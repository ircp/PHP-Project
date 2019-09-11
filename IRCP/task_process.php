<?
	try {
		require_once ('connection.php');
		
		$sql = "	SELECT TASK_SCHEDULE.TASK_ID ,SCHEDULE.SCHEDULE_ID, SCHEDULE.SCHEDULE_TYPE, SCHEDULE.MAIL, SCHEDULE.URL, SCHEDULE.SCHEDULE_NAME
					FROM TASK_SCHEDULE TASK_SCHEDULE
					LEFT JOIN SCHEDULE SCHEDULE ON TASK_SCHEDULE.SCHEDULE_ID = SCHEDULE.SCHEDULE_ID
					WHERE TASK_SCHEDULE.TASK_STATUS = 1 
					ORDER BY TASK_ID";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute();	
		
		UNSET ($TASK_LIST);
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$TASK_LIST[$arr['TASK_ID']]['URL'] = $arr['URL'];
			$TASK_LIST[$arr['TASK_ID']]['MAIL'] = $arr['MAIL'];
			$TASK_LIST[$arr['TASK_ID']]['SCHEDULE_TYPE'] = $arr['SCHEDULE_TYPE'];
			$TASK_LIST[$arr['TASK_ID']]['SCHEDULE_NAME'] = $arr['SCHEDULE_NAME'];
			$TASK_LIST[$arr['TASK_ID']]['SCHEDULE_ID'] = $arr['SCHEDULE_ID'];
		}
		
		$conn = null;

		error_reporting(E_ALL);
		ini_set('display_errors', TRUE);
		ini_set('display_startup_errors', TRUE);
		date_default_timezone_set('Asia/Bangkok');
		define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');			

		require_once ('PHPMailer.php');
		require_once ('SMTP.php');
		require_once ('Exception.php');
		
		$mail = new PHPMailer\PHPMailer\PHPMailer();

		
		if (isset($TASK_LIST)) {
			foreach ($TASK_LIST as $taskid => $task_result) {
				$url = $task_result['URL'];
				$scheduletype = $task_result['SCHEDULE_TYPE'];
				$schedulemail = $task_result['MAIL'];
				$schedulename = $task_result['SCHEDULE_NAME'];
				$scheduleid = $task_result['SCHEDULE_ID'];

				$filename =  '';
				$filenamesummary = '';
		
				require ('connection.php');
				
				$sql = "	UPDATE TASK_SCHEDULE
							SET TASK_STATUS = 3
							WHERE TASK_ID = ?";
							
				$stmt = $conn->prepare($sql);
				$stmt->execute(array($taskid));								
				
				if ($url != '') {
					$url = $url.".php";
					include ($url);	
				}
		
				if ($schedulemail == 1) {
					$sql = " SELECT EMAIL, FIRST_NAME, LAST_NAME
								FROM SCHEDULE_MAIL SCHEDULE_MAIL
								LEFT JOIN EMPLOYEE EMPLOYEE ON EMPLOYEE.EMPLOYEE_ID = SCHEDULE_MAIL.EMPLOYEE_ID AND 
								SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
								WHERE SCHEDULE_MAIL.SCHEDULE_ID = ?";

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($scheduleid));
					
					UNSET ($MAIL_LIST);
					$i = 0;
					while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$i = $i + 1;
						
						$MAIL_LIST[$i]['MAIL'] = $arr['EMAIL'];
						$MAIL_LIST[$i]['RECEIVER'] = $arr['FIRST_NAME']." ".$arr['LAST_NAME'];
					}					
				}
				
				$conn->commit();
				$conn = null;
				
				if ($schedulemail == 1 && (isset($MAIL_LIST))) {
					
					$mail_title = $schedulename;
					$mail_description = "เรียนทุกท่าน <br><br><br><br> ขอจัดส่ง ".$schedulename." ตามเอกสารแนบ <br><br><br><br>จึงเรียนมาเพื่อทราบ";
					
					$mail_footer = "";
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
					
					foreach ($MAIL_LIST as $mailid => $mail_result) {
						$receiver_mail = $mail_result['MAIL'];
						$receiver = $mail_result['RECEIVER'];
						
						$mail->addAddress($receiver_mail, $receiver);  
					}
					
					$mail->isHTML(true);                                  // Set email format to HTML
					if ($filename != '') {
						$mail->addAttachment($filename);
					}
					if ($filenamesummary != '') {
						$mail->addAttachment($filenamesummary);
					}

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
						$error_message = $mail->ErrorInfo0;	
						$mail_status = 0;
					} else {
						$error_message = '';
						$mail_status = 3;
					}					
				}				
			}
		}

	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: TASK ' .$e->getMessage();
		
	}
	
?>