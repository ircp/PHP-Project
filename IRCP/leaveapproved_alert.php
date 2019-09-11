<?
	require_once ('PHPMailer.php');
	require_once ('SMTP.php');
	require_once ('Exception.php');
	
	$mail = new PHPMailer\PHPMailer\PHPMailer();
		
	try {
		require ('connection.php');
		
		$sql = "	SELECT ELH.APPROVED_ID, COUNT(*) COUNTN, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, EMPLOYEE.EMAIL
					FROM EMPLOYEE_LEAVE_HISTORY ELH
					LEFT JOIN EMPLOYEE EMPLOYEE ON EMPLOYEE.EMPLOYEE_ID = ELH.APPROVED_ID 
					WHERE SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
					AND (ELH.APPROVED_ID IS NOT NULL OR ELH.APPROVED_ID <> '')
					AND (ELH.APPROVED_DATE IS NULL OR ELH.APPROVED_DATE = '')
					GROUP BY ELH.APPROVED_ID
					ORDER BY EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$receiver = 'คุณ'.$arr['FIRST_NAME']." ".$arr['LAST_NAME'];
			$receiver_mail = $arr['EMAIL'];
			$number_waiting = $arr['COUNTN'];
			
			$mail_title = "แจ้งใบลารอการอนุมัติบนระบบ";
			$mail_description = "เรียน ".$receiver." <br><br><br> ในระบบใบลามีใบลารอท่านอนุมัติจำนวน ".$number_waiting." รายการ <br><br><br>จึงเรียนมาเพื่อทราบ";
			
			$mail_footer = "<br><br><br><a  href='http://192.168.1.203/IRCP/'>http://192.168.1.203/IRCP/</a>";
			$mail->CharSet = "utf-8";	
			$mail->isSMTP();
			$mail->Host='ircpmail.ircp.co.th';
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = 'hr_leave';                 // SMTP username
			$mail->Password = 'HR4Leave@min';                           // SMTP password
			$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = 587;                                    // TCP port to connect to
			
			$mail->setFrom('hr_leave@ircp.co.th', 'HR Leave');
			
			$mail->clearAddresses();
			$mail->clearAttachments();		
			
			$mail->addAddress($receiver_mail, $receiver); 
			
			$mail->isHTML(true);
			
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
				$mail_status = $mail->ErrorInfo;				
			} else {
				$mail_status = 'COMPLETE';
			}			
		}
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;				
?>