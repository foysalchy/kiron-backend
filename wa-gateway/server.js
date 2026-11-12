import express from "express";
import cors from "cors";
import makeWASocket, {
  DisconnectReason,
  useMultiFileAuthState,
  fetchLatestBaileysVersion,
} from "@whiskeysockets/baileys";
import pino from "pino";
import QRCode from "qrcode";
import fs from "fs";
import path from "path";
import http from "http";
import dotenv from "dotenv";

// Load environment from ../.env if exists, or local .env
dotenv.config({ path: path.resolve("../.env") });
dotenv.config();

const app = express();
app.use(cors());
app.use(express.json());

const PORT = process.env.WA_PORT || 3001;
const SESSIONS_DIR = path.resolve("./sessions");

if (!fs.existsSync(SESSIONS_DIR)) {
  fs.mkdirSync(SESSIONS_DIR, { recursive: true });
}

// Active sockets map: channelId -> { sock, qrCode, status, profile }
const activeSessions = new Map();

async function initWASession(sessionId) {
  if (activeSessions.has(sessionId)) {
    const existing = activeSessions.get(sessionId);
    if (existing.status === "connected" || existing.qrCode) {
      return existing;
    }
  }

  const sessionPath = path.join(SESSIONS_DIR, `session_${sessionId}`);
  const { state, saveCreds } = await useMultiFileAuthState(sessionPath);
  const { version } = await fetchLatestBaileysVersion();

  const sessionData = {
    sock: null,
    qrCode: null,
    status: "connecting",
    phone: null,
    name: null,
  };
  activeSessions.set(sessionId, sessionData);

  const sock = makeWASocket({
    version,
    logger: pino({ level: "silent" }),
    printQRInTerminal: false,
    auth: state,
    browser: ["Kiron Omni Channel", "Chrome", "1.0.0"],
    syncFullHistory: true,
  });

  sessionData.sock = sock;

  sock.ev.on("creds.update", saveCreds);

  sock.ev.on("connection.update", async (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      try {
        const qrDataUrl = await QRCode.toDataURL(qr);
        sessionData.qrCode = qrDataUrl;
        sessionData.status = "waiting_for_scan";
      } catch (err) {
        console.error("QR Code generation error:", err);
      }
    }

    if (connection === "close") {
      const statusCode = lastDisconnect?.error?.output?.statusCode;
      const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

      sessionData.qrCode = null;
      sessionData.status = "disconnected";

      if (shouldReconnect) {
        setTimeout(() => initWASession(sessionId), 3000);
      } else {
        // Logged out: remove session folder
        try {
          fs.rmSync(sessionPath, { recursive: true, force: true });
        } catch (e) {}
        activeSessions.delete(sessionId);
      }
    } else if (connection === "open") {
      sessionData.status = "connected";
      sessionData.qrCode = null;
      sessionData.phone = sock.user?.id?.split(":")[0] || sock.user?.id?.split("@")[0];
      sessionData.name = sock.user?.name || `WhatsApp (${sessionData.phone})`;

      // Notify Laravel webhook if needed
      notifyLaravelWebhook({
        event: "channel.connected",
        sessionId,
        phone: sessionData.phone,
        name: sessionData.name,
      });
    }
  });

  // Listen to incoming messages and chat sync
  sock.ev.on("messaging-history.set", async ({ chats, contacts, messages, isLatest }) => {
    try {
      for (const msg of messages || []) {
        if (!msg.message) continue;
        const remoteJid = msg.key.remoteJid;
        if (!remoteJid || remoteJid.endsWith("@g.us")) continue;

        const isFromMe = msg.key.fromMe;
        const senderPhone = remoteJid.split("@")[0];
        const text =
          msg.message.conversation ||
          msg.message.extendedTextMessage?.text ||
          msg.message.imageMessage?.caption ||
          "";

        notifyLaravelWebhook({
          event: "message.received",
          sessionId,
          messageId: msg.key.id,
          fromMe: isFromMe,
          senderPhone,
          remoteJid,
          text,
          timestamp: msg.messageTimestamp,
          pushName: msg.pushName || senderPhone,
        });
      }
    } catch (e) {
      console.error("History sync error:", e);
    }
  });

  // Listen to incoming messages
  sock.ev.on("messages.upsert", async ({ messages, type }) => {
    console.log(`[WA Gateway] messages.upsert received ${messages?.length} messages (type: ${type})`);

    for (const msg of messages || []) {
      if (!msg.message) continue;
      const remoteJid = msg.key.remoteJid;
      if (!remoteJid || remoteJid.endsWith("@g.us")) continue; // Ignore groups for now

      const isFromMe = msg.key.fromMe;
      const senderPhone = remoteJid.split("@")[0];
      const text =
        msg.message.conversation ||
        msg.message.extendedTextMessage?.text ||
        msg.message.imageMessage?.caption ||
        "";

      console.log(`[WA Gateway] New Message from: ${senderPhone}, fromMe: ${isFromMe}, text: ${text}`);

      // Try to fetch profile picture if available
      let profilePicUrl = null;
      try {
        profilePicUrl = await sock.profilePictureUrl(remoteJid, "image");
      } catch (e) {
        // Ignored if customer privacy restricts profile picture
      }

      const payload = {
        sessionId,
        messageId: msg.key.id,
        fromMe: isFromMe,
        senderPhone,
        remoteJid,
        text,
        timestamp: msg.messageTimestamp,
        pushName: msg.pushName || (isFromMe ? "You" : senderPhone),
        profilePicUrl,
      };

      notifyLaravelWebhook({
        event: "message.received",
        ...payload,
      });
    }
  });

  return sessionData;
}

async function notifyLaravelWebhook(data) {
  try {
    const rawUrl =
      process.env.LARAVEL_WEBHOOK_URL ||
      (process.env.APP_URL
        ? `${process.env.APP_URL}/api/v1/webhook/whatsapp-qr`
        : "http://127.0.0.1:8000/api/v1/webhook/whatsapp-qr");

    const webhookUrl = rawUrl.replace(/\/+api\/v1/, "/api/v1");
    console.log(`[WA Gateway] Sending webhook to: ${webhookUrl} (event: ${data.event})`);

    const postData = JSON.stringify(data);
    const parsed = new URL(webhookUrl);

    const isHttps = parsed.protocol === "https:";
    const client = isHttps ? (await import("https")).default : http;

    const options = {
      hostname: parsed.hostname,
      port: parsed.port || (isHttps ? 443 : 80),
      path: parsed.pathname + parsed.search,
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Content-Length": Buffer.byteLength(postData),
      },
    };

    const req = client.request(options, (res) => {
      console.log(`[WA Gateway] Webhook response status: ${res.statusCode}`);
    });

    req.on("error", (e) => {
      console.error(`[WA Gateway] Webhook request error: ${e.message}`);
    });

    req.write(postData);
    req.end();
  } catch (err) {
    console.error(`[WA Gateway] notifyLaravelWebhook caught: ${err.message}`);
  }
}

// REST API Endpoints for Laravel
app.get("/sync-chats/:sessionId", async (req, res) => {
  const { sessionId } = req.params;
  const session = activeSessions.get(sessionId);

  if (!session || session.status !== "connected" || !session.sock) {
    return res.status(400).json({ error: "WhatsApp session is not connected." });
  }

  try {
    console.log(`[WA Gateway] Manual sync triggered for ${sessionId}`);
    // Fetch last 50 messages from socket store or trigger history
    res.json({ success: true, message: "Sync command received" });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.get("/status/:sessionId", async (req, res) => {
  const { sessionId } = req.params;
  const session = activeSessions.get(sessionId);

  if (!session) {
    return res.json({ status: "not_initialized" });
  }

  res.json({
    status: session.status,
    phone: session.phone,
    name: session.name,
  });
});

app.get("/qr/:sessionId", async (req, res) => {
  const { sessionId } = req.params;
  let session = activeSessions.get(sessionId);

  if (!session || session.status === "disconnected") {
    session = await initWASession(sessionId);
  }

  res.json({
    status: session.status,
    qr: session.qrCode,
    phone: session.phone,
    name: session.name,
  });
});

app.post("/send/:sessionId", async (req, res) => {
  const { sessionId } = req.params;
  const { phone, text, fileUrl } = req.body;

  console.log(`[WA Gateway] /send request for sessionId: ${sessionId}, to phone: ${phone}, text: ${text}`);

  const session = activeSessions.get(sessionId);
  if (!session || session.status !== "connected" || !session.sock) {
    console.error(`[WA Gateway] /send failed: session not connected (status: ${session?.status})`);
    return res.status(400).json({ error: "WhatsApp session is not connected." });
  }

  try {
    let cleanPhone = phone.replace(/\D/g, "");
    if (!phone.includes("@")) {
      cleanPhone = `${cleanPhone}@s.whatsapp.net`;
    } else {
      cleanPhone = phone;
    }

    console.log(`[WA Gateway] Sending to JID: ${cleanPhone}`);

    let sentMsg;
    if (fileUrl) {
      sentMsg = await session.sock.sendMessage(cleanPhone, {
        image: { url: fileUrl },
        caption: text || "",
      });
    } else {
      sentMsg = await session.sock.sendMessage(cleanPhone, { text: text || "" });
    }

    console.log(`[WA Gateway] Message sent successfully (id: ${sentMsg?.key?.id})`);
    res.json({ success: true, messageId: sentMsg?.key?.id });
  } catch (err) {
    console.error("[WA Gateway] Failed to send WhatsApp message:", err);
    res.status(500).json({ error: err.message });
  }
});

app.post("/logout/:sessionId", async (req, res) => {
  const { sessionId } = req.params;
  const session = activeSessions.get(sessionId);

  if (session && session.sock) {
    try {
      await session.sock.logout();
    } catch (e) {}
  }

  const sessionPath = path.join(SESSIONS_DIR, `session_${sessionId}`);
  try {
    fs.rmSync(sessionPath, { recursive: true, force: true });
  } catch (e) {}
  activeSessions.delete(sessionId);

  res.json({ success: true, message: "Logged out successfully" });
});

// Auto-restore saved sessions on gateway start
fs.readdir(SESSIONS_DIR, (err, files) => {
  if (!err && files) {
    for (const f of files) {
      if (f.startsWith("session_")) {
        const sId = f.replace("session_", "");
        initWASession(sId);
      }
    }
  }
});

app.listen(PORT, () => {
  console.log(`WhatsApp Gateway service running on port ${PORT}`);
});

