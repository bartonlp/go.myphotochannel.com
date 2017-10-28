<?php
// Example of sending email with attachment using PHPMailer
// This now uses PHPMailer 6.0 via /var/www/bartonphillips.org/vendor
// See: https://github.com/PHPMailer/PHPMailer for documentation

// Added namespace info
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get the autoloader
require_once(getenv("SITELOADNAME"));

$agent = $_SERVER['HTTP_USER_AGENT'];

// Instantiate PHPMailer
$mail = new PHPMailer();

$mail->IsSendmail(); // telling the class to use SendMail transport

$body = <<<EOF
  <h1>This is a test of PHPMailer.</h1>
  <p>User Agent: $agent</p>
  <div align="center">
    <img src="images/myphotochannel.png">
  </div>
EOF;

$mail->AddReplyTo("bartonphillips@gmail.com","Barton Phillips");
$mail->SetFrom('info@myphotochannel.com');
$address = "bartonphillips@gmail.com";
$mail->AddAddress($address, "Barton Phillips");
$mail->Subject = "PHPMailer Test Subject via Sendmail (BLP)";
$mail->MsgHTML($body, dirname(__FILE__));
//$mail->msgHTML(file_get_contents('content.html'), dirname(__FILE__));
$mail->AddAttachment("images/myphotochannel.png");
if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}
