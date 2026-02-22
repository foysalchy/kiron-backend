<?php

$apiToken  = 'iF9aNbBfaKiT3WtsdZSkUiAEoMEnt2Q6aCFlYWUv';
$zoneId    = 'ac7c72e0460207f900a635fb32ae43cf';
$subdomain = $sub_domain; // assume this variable already exists
$ipAddress = '134.209.65.214';
$domain    = 'doob.com.bd';

$apiEndpoint = "https://api.cloudflare.com/client/v4/zones/$zoneId/dns_records";

$dnsRecord = [
    'type'    => 'A',
    'name'    => "$subdomain.$domain",
    'content' => $ipAddress,
    'ttl'     => 3600,
    'proxied' => true,
];

$ch = curl_init($apiEndpoint);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dnsRecord));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiToken,
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {
    $result = json_decode($response, true);
    print_r($result); // Cloudflare API response
}

curl_close($ch);

header('Content-Type: application/json');

if (!isset($_GET['domain']) || empty($_GET['domain'])) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Domain is required'
    ]);
    exit;
}

$domain = $_GET['domain'];
$targetIp = '134.209.65.214';

try {
    // DNS A record lookup
    $records = dns_get_record($domain, DNS_A);

    $aRecords = [];
    foreach ($records as $record) {
        if (isset($record['ip'])) {
            $aRecords[] = $record['ip'];
        }
    }

    $isValuePresent = in_array($targetIp, $aRecords);

    http_response_code(200);
    echo json_encode([
        'isValuePresent' => $isValuePresent
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal Server Error'
    ]);
}