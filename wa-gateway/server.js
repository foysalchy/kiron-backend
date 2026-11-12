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
    syncFullHistory: false,
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

  // Listen to incoming messages
  sock.ev.on("messages.upsert", async ({ messages, type }) => {
    if (type !== "notify") return;

    for (const msg of messages) {
      if (!msg.message) continue;
      const remoteJid = msg.key.remoteJid;
      if (remoteJid.endsWith("@g.us")) continue; // Ignore groups for now

      const isFromMe = msg.key.fromMe;
      const senderPhone = remoteJid.split("@")[0];
      const text =
        msg.message.conversation ||
        msg.message.extendedTextMessage?.text ||
        msg.message.imageMessage?.caption ||
        "";

      const payload = {
        sessionId,
        messageId: msg.key.id,
        fromMe: isFromMe,
        senderPhone,
        remoteJid,
        text,
        timestamp: msg.messageTimestamp,
        pushName: msg.pushName || senderPhone,
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
    const webhookUrl = process.env.LARAVEL_WEBHOOK_URL || "http://127.0.0.1:8000/api/v1/webhook/whatsapp-qr";
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

    const req = client.request(options, (res) => {});
    req.on("error", (e) => {
      // Laravel might be running on a different port or offline
    });
    req.write(postData);
    req.end();
  } catch (err) {}
}

// REST API Endpoints for Laravel
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

  const session = activeSessions.get(sessionId);
  if (!session || session.status !== "connected" || !session.sock) {
    return res.status(400).json({ error: "WhatsApp session is not connected." });
  }

  try {
    const jid = phone.includes("@") ? phone : `${phone.replace(/\D/g, "")}@s.whatsapp.net`;

    if (fileUrl) {
      await session.sock.sendMessage(jid, {
        image: { url: fileUrl },
        caption: text || "",
      });
    } else {
      await session.sock.sendMessage(jid, { text: text || "" });
    }

    res.json({ success: true });
  } catch (err) {
    console.error("Failed to send WhatsApp message:", err);
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

