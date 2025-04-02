<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$data= json_decode($raw, true);

if(empty($data['email'])){
  echo json_encode(['success'=>false, 'message'=>'E-mail gelmedi']);
  exit;
}

$email = trim($data['email']);

// Rastgele 6 haneli kod
$code = rand(100000,999999);

// Basitçe session’da saklıyoruz
$_SESSION['email_verify'][$email] = $code;

// Burada PHPMailer vb. ile mail gönderilebilir
// DEMO: mail atıldığını varsayıyoruz
$mailSent = true; 
/*
Gerçek senaryoda:
use PHPMailer\PHPMailer\PHPMailer;
...
$mail->addAddress($email);
$mail->Subject = 'Doğrulama Kodunuz';
$mail->Body = 'Merhaba, Kodunuz: ' . $code;
$mailSent = $mail->send();
*/

if($mailSent){
  echo json_encode(['success'=>true, 'code'=>$code]);
} else {
  echo json_encode(['success'=>false, 'message'=>'Mail gönderilemedi']);
}
