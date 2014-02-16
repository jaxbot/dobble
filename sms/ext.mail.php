<?php
//Megan Mailer
//This sends the emails via the txt@dobble.me service

require("3rdparty/class.phpmailer.php");

$MAIL_USERNAME = "txt@dobble.me";
$MAIL_PASSWORD = "";

$SENDTO = $argv[1];
$MESSAGE = trim(join(" ", array_slice($argv, 2)));

$mail = new PHPMailer(true);
$mail->IsSMTP();
try {
$to = $SENDTO;

$mail->Host = "smtp.gmail.com";
$mail->Port = 465;
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Username = $GLOBALS['MAIL_USERNAME'];
$mail->Password = $GLOBALS['MAIL_PASSWORD'];
$mail->SetFrom("txt@dobble.me", "Dobble.me");
$mail->Body = $MESSAGE;
$mail->AltBody = $MESSAGE;

$mail->AddAddress($to);

$mail->Send();
} catch (phpmailerException $e) {
	echo $e->errorMessage();
}

?>
