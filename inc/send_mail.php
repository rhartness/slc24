<?php
require_once $_SITE["root"]."inc/phpmailer/class.phpmailer.php";

function send_mail($subject, $content, $recipient, $replyto="admin@solidcoin24.com", $replytoname="solidcoin24")
{
	$body = $content;
	
	$mail = new PHPMailer();
	
	$mail->IsSMTP();
	
	$mail->SMTPAuth   = true;                   // enable SMTP authentication
	$mail->Host       = "mail.solidcoin24.com"; // sets the SMTP server
	$mail->Port       = 25;                     // set the SMTP port for the GMAIL server
	$mail->Username   = "noreply@solidcoin24.com";             // SMTP account username
	$mail->Password   = "PASSWORD"; 
	
	$mail->From       = "admin@solidcoin24.com";
	$mail->FromName   = "solidcoin24";
	$mail->Subject    = $subject;
	$mail->MsgHTML($body);
	
	$mail->AddReplyTo($replyto, $replytoname);
	$mail->AddAddress($recipient);
	
	if(!$mail->Send())
		echo "Error: ".$mail->ErrorInfo;
	else
		return true;
}

?>