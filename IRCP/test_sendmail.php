<?
		require_once ('PHPMailer.php');
		require_once ('SMTP.php');
		require_once ('Exception.php');
		
		$mail = new PHPMailer\PHPMailer\PHPMailer();
		
			$mail->CharSet = "utf-8";	
			$mail->isSMTP();
			$mail->Host='ircpmail.ircp.co.th';
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = 'hr_leave';                 // SMTP username
			$mail->Password = 'HR4Leave@min';                           // SMTP password
			$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = 587;                                    // TCP port to connect to
			
			$mail->SMTPDebug = 3;
			
$mail->Debugoutput = function($str, $level) {
    file_put_contents('D:\smtp.log', gmdate('Y-m-d H:i:s'). "\t$level\t$str\n", FILE_APPEND | LOCK_EX);
};

			$mail->setFrom('hr_leave@ircp.co.th', 'HR Leave');
			$mail->addAddress('varinw@ircp.co.th', 'rynko');   
			
			$mail->isHTML(true);                                  // Set email format to HTML

			
			$mail->Subject = 'รายงานสรุปการลาของพนักงาน ประจำสัปดาห์';
			$mail->Body    ="เรียนทุกท่าน <br><br> HR Leave ขอจัดส่งรายงานสรุปการลาของพนักงาน ประจำสัปดาห์  ตามเอกสารแนบ<br><br>จึงเรียนมาเพื่อทราบ  <br><br>ขอบคุณค่ะ";
			$mail->AltBody = 'This is a plain-text message body';

			$mail->smtpConnect([
				'ssl' => [
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				]
			]);

			if(!$mail->send()) {
				require ('connection.php');

				$sql = "	UPDATE TASK_SCHEDULE
							SET MAIL_STATUS = ?
							WHERE TASK_ID = 7";
				
				$mail_status =  $mail->ErrorInfo;
				$stmt = $conn->prepare($sql);
				$stmt->execute(array($mail_status));	
				
				$conn->commit();	
	//			echo 'Message could not be sent.';
	//			echo 'Mailer Error: ' . $mail->ErrorInfo;
			} else {
	//			echo 'Message has been sent';
			}	
?>