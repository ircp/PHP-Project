<?
	require ('connection.php');
	
	try {
		$sql = "	SELECT COUNT(*) COUNTN FROM TASK_SCHEDULE
					WHERE TASK_STATUS = 2";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
		
		$countn = 0;
		if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$countn = $arr['COUNTN'];
		}
		
		if ($countn > 0) {
			exit();
		}
		
		$sql = "	SELECT COUNT(*) COUNTN FROM TASK_SCHEDULE
					WHERE TASK_STATUS IN (1)";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
		
		$countn = 0;
		if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$countn = $arr['COUNTN'];
		}
		
		if ($countn == 0) {
			exit();
		}		
		
		$sql = "	SELECT TS.TASK_ID, SC.SCHEDULE_ID, SC.SCHEDULE_NAME, SC.SCHEDULE_DESCRIPTION, SC.SCHEDULE_URL, SC.SCHEDULE_TYPE, SC.VALID_IND_CODE, SC.MAIL
					FROM TASK_SCHEDULE TS
					LEFT JOIN SCHEDULE SC ON TS.SCHEDULE_ID = SC.SCHEDULE_ID AND SC.VALID_IND_CODE = 1
					WHERE TS.TASK_STATUS = 1
					ORDER BY TS.TASK_ID";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute();
		
		UNSET ($TASK_LIST);
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$taskid = $arr['TASK_ID'];
			$scheduleid = $arr['SCHEDULE_ID'];
			$schedulename = $arr['SCHEDULE_NAME'];
			$description = $arr['SCHEDULE_DESCRIPTION'];
			$scheduletype = $arr['SCHEDULE_TYPE'];
			$schedulemail = $arr['MAIL'];
			$url = $arr['SCHEDULE_URL'];
			
			$TASK_LIST[$taskid]['SCHEDULE_ID'] = $scheduleid;
			$TASK_LIST[$taskid]['SCHEDULE_NAME'] = $schedulename;
			$TASK_LIST[$taskid]['DESCRIPTION'] = $description;
			$TASK_LIST[$taskid]['SCHEDULE_TYPE'] = $scheduletype;
			$TASK_LIST[$taskid]['URL'] = $url;
			$TASK_LIST[$taskid]['MAIL'] = $schedulemail;
		}
		
		error_reporting(E_ALL);
		ini_set('display_errors', TRUE);
		ini_set('display_startup_errors', TRUE);
		date_default_timezone_set('Asia/Bangkok');
		define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');			

		require_once ('PHPMailer.php');
		require_once ('SMTP.php');
		require_once ('Exception.php');
		
		$mail = new PHPMailer\PHPMailer\PHPMailer();
		
		foreach ($TASK_LIST as $taskid => $result) {
			require ('connection.php');
			
			$sql = "	UPDATE TASK_SCHEDULE
						SET TASK_STATUS = 2
						WHERE TASK_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($taskid));
			$conn->commit();
			
			$file_name = '';		
			$scheduleid = $result['SCHEDULE_ID'];
			$scheduletype = $result['SCHEDULE_TYPE'];
			$scheduleurl = $result['URL'];
			$schedulemail = $result['MAIL'];
			$schedulename = $result['SCHEDULE_NAME'];
			
			if ($scheduleurl != '') {
				$url = $scheduleurl.".php";
				include ($url);
			}
			
			require ('connection.php');
			
			$sql = "	UPDATE TASK_SCHEDULE
						SET TASK_STATUS = 3, FILENAME = ?, LAST_MODIFIED = NOW()
						WHERE TASK_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($file_name, $taskid));	
			
			$conn->commit();
			
			if ($schedulemail == 1) {
				$sql = " SELECT EMAIL, FIRST_NAME, LAST_NAME
							FROM SCHEDULE_MAIL SCHEDULE_MAIL
							LEFT JOIN EMPLOYEE EMPLOYEE ON EMPLOYEE.EMPLOYEE_ID = SCHEDULE_MAIL.EMPLOYEE_ID AND 
							SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
							WHERE SCHEDULE_MAIL.SCHEDULE_ID = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($scheduleid));
				
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
				
				while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$receiver_mail = $arr['EMAIL'];
					$receiver = $arr['FIRST_NAME']." ".$arr['LAST_NAME'];
					$mail->addAddress($receiver_mail, $receiver);     // Add a recipient
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

				require ('connection.php');

				$sql = "	UPDATE TASK_SCHEDULE
							SET MAIL_STATUS = ?, ERROR_MESSAGE =?
							WHERE TASK_ID = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($mail_status, $error_message ,$taskid));	
				
				$conn->commit();	
			
			}
		}
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: TASK ' .$e->getMessage();
	}
	$conn = null;
?>