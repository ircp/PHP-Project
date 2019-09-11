<?
	require ('connection.php');
	
	try {
		if ($menuid == 10 || $menuid == 11) {
			$mail_footer = "<br><br><br><a  href='http://192.168.1.203/IRCP/'>http://192.168.1.203/IRCP/</a>";
			$mail->CharSet = "utf-8";	
			$mail->isSMTP();
			$mail->Host='ircpmail.ircp.co.th';
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = 'hr_leave';                 // SMTP username
			$mail->Password = 'HR4Leave@min';                           // SMTP password
			$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = 587;                                    // TCP port to connect to
			
			$mail->clearAddresses();
			
			$mail->setFrom('hr_leave@ircp.co.th', 'HR Leave');
			$mail->addAddress($receiver_mail, $receiver);     // Add a recipient
			$mail->isHTML(true);                                  // Set email format to HTML
			
			$mail->Subject = $mail_title;
			$mail->Body    = $mail_description." ".$mail_footer;
			$mail->AltBody = 'This is a plain-text message body';

			$mail->smtpConnect([
				'ssl' => [
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				]
			]);

			if(!$mail->send()) {
				echo 'Message could not be sent.';
				echo 'Mailer Error: ' . $mail->ErrorInfo;
			} else {
				echo 'Message has been sent';
			}			
		}
	}	
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
?>