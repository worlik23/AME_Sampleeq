<?php
require './vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$mail = new PHPMailer(true);
$sender = 'ENGEEQ@inventec.com';
$senderName = 'SAMPLEEQ & ENGEEQ';

$msg = '<html lang="en" style="font-family:\'Tahoma\';">';
$msg .= '<body style="display:grid;grid-template-rows:120px fit-content 80px;grid-template-columns:1fr;margin:auto;padding:0;background:white;color:black;">';
$msg .= '<header style="grid-area:1/1/2/2;width:100%;height:120px;margin:0;padding:0;background:#e5001f;display:flex;flex-direction:row;justify-content:space-between;align-items:center;"><div><img src="cid:logoTxt" alt="S&E logo text" style="height:50px;"></div>';
$msg .= '<div><img src="cid:logoImg" alt="Sampleeq & Engeeq logo" style="height:120px;width:120px;"></div></header>';
$msg .= '<main style="grid-area:2/1/3/2;width:100%;><h1 style="color:#e5001f;font-size:18pt;font-weight:bold;">'.$subject.'</h1>';
$msg .= '<h2 style="margin: 0 1rem;color:#e5001f;font-size:16pt;font-weight:bold;">Editor : '.$_SESSION['online']['username'].'</h2>';
$msg .=	$message;
$msg .= '<footer style="grid-area:3/1/4/2;display:flex;flex-direction:row;align-items:center;justify-content:space-evenly;width:100%;height:50px;';
$msg .= 'font-size:12pt;background:#e5001f;color:white;">';
$msg .= ($details) ? $details : '';
$msg .=	'</footer></main></body></html>';

$nomsg = $message;

try {
    $mail->isSMTP();
    $mail->Host = '10.14.20.130';
    $mail->SMTPAuth = false; 
    $mail->SMTPSecure = false; 
    $mail->Port = 25; 
    $mail->SMTPAutoTLS = false;
    $mail->CharSet = 'UTF-8';
    $mail->ContentType = 'text/html; charset=UTF-8';
    $mail->addEmbeddedImage('./icon/logoTxt.png', 'logoTxt');
    $mail->addEmbeddedImage('./favicons/SEWebApp_192x192.png', 'logoImg');
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );    
    $mail->setFrom($sender, $senderName);
    foreach ($receivers as $to) {
        $mail->addAddress($to);
    }
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $msg;
    $mail->AltBody = $nomsg;
    $mail->send();
    echo 'Mail Ok';
} catch (Exception $e) {
    echo "Email could not be sent. Error: {$mail->ErrorInfo}";
}