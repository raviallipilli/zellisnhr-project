<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

function send_email($to = null, $from = null, $fromName = null, $subject = null, $message = null, $debug = false)
{
	if(ENABLE_EMAIL)
	{
		$mail = new PHPMailer;

		if($debug)
		{
			//Enable SMTP debugging
			// 0 = off (for production use)
			// 1 = client messages
			// 2 = client and server messages
			$mail->SMTPDebug = 2;
			//Ask for HTML-friendly debug output
			$mail->Debugoutput = 'html';
		}
		else
		{
			$mail->SMTPDebug = 0;
		}

		$mail->IsSMTP();                // send via SMTP
		$mail->Host     = SMTP_HOST;    // SMTP servers
		$mail->SMTPAuth = true;         // turn on SMTP authentication
		$mail->Username = SMTP_USERNAME; // SMTP username
		$mail->Password = SMTP_PASSWORD; // SMTP password

		//office 365
		$mail->Port       = 587;
		$mail->SMTPSecure = 'tls';

		$mail->From     = $from;
		$mail->FromName = $fromName;
		$mail->AddAddress($to);
		$mail->IsHTML(true);            // send as HTML

		$mail->Subject  =  $subject;
		$mail->Body     =  $message;

		if(!$mail->Send())
		{
			return false;
		}
		return true;
	}
	return false;
}