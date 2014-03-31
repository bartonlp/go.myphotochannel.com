<?php
require 'PHPMailer/PHPMailerAutoload.php';
$mail = new PHPMailer();
$mail->IsSendmail(); // telling the class to use SendMail transport
$body = "<p>This is a test <img src='images/myphotochannel.png'/></p>";
//$body = eregi_replace("[\]",'',$body);
$mail->AddReplyTo("bartonphillips@gmail.com","Barton Phillips");
$mail->SetFrom('info@myphotochannel.com');
//$mail->AddReplyTo("name@yourdomain.com","First Last");
$address = "bartonphillips@gmail.com";
$mail->AddAddress($address, "Barton Phillips");
$mail->Subject    = "PHPMailer Test Subject via Sendmail, basic";
$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
$mail->MsgHTML($body);
//$mail->AddEmbeddedImage("rocks.png", "my-attach", "rocks.png");
//$mail->Body = 'Your <b>HTML</b> with an embedded Image: <img src="cid:my-attach"> Here is an image!';

$mail->AddAttachment("images/myphotochannel.png");      // attachment
//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment
if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}
?>