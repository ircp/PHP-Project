<?
	require_once ('PHPMailer.php');
	require_once ('SMTP.php');
	require_once ('Exception.php');
	
	
		
	try {
		
		$mail = new PHPMailer\PHPMailer\PHPMailer();
						
		$currentyear = date('Y');
		$currentdate = date("d");
		$currentmonth = date("m");
		
		$endyear = 0;
		$currentday = $currentyear."-".$currentmonth."-".$currentdate;
		
		if (($currentmonth == 1 || $currentmonth == '01') && $currentdate <= 7) {
			$currentyear = $currentyear -1 ;
		}
		
		$date = date_create($currentday);
		date_sub($date,date_interval_create_from_date_string("7 day"));
		$sdate =  date_format($date,"Y-m-d");
		$startdate =  $sdate." 00:00:00";

		require ('connection.php');
		
		$sql = "	SELECT WD.EMPLOYEE_ID, EM.FIRST_NAME, EM.LAST_NAME, EM.EMAIL, COUNT(*) COUNTN
						FROM WORKING_DAY WD
						LEFT JOIN EMPLOYEE EM ON SYSDATE() BETWEEN EM.EFFECTIVE_START_DATE AND EM.EFFECTIVE_END_DATE AND EM.EMPLOYEE_ID = WD.EMPLOYEE_ID
						WHERE SYSDATE() BETWEEN WD.EFFECTIVE_START_DATE AND WD.EFFECTIVE_END_DATE
						AND WD.WORKING_STATUS = 1
						AND WD.WORKING_DAY <= ? 
						AND EM.WORKING_TIME = 1
						GROUP BY WD.APPROVED_ID, EM.FIRST_NAME, EM.LAST_NAME, EM.EMAIL
						ORDER BY EM.FIRST_NAME, EM.LAST_NAME";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($startdate));
		
		unset($RESULT_EM);
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$RESULT_EM[$arr['EMPLOYEE_ID']]['RECEIVER'] =  'คุณ'.$arr['FIRST_NAME']." ".$arr['LAST_NAME'];
			$RESULT_EM[$arr['EMPLOYEE_ID']]['RECEIVER_MAIL'] =  $arr['EMAIL'];
			$RESULT_EM[$arr['EMPLOYEE_ID']]['NUMBER_WAITING'] =  $arr['COUNTN']; 
		}
		
		if (isset($RESULT_EM)) {

			foreach ($RESULT_EM as $employeeid => $task_result) {
				
				
				$receiver =$task_result['RECEIVER'];
				$receiver_m = $task_result['RECEIVER_MAIL'];
				$number_waiting = $task_result['NUMBER_WAITING']; 
				
				$mail_title = "แจ้งเวลาการทำงานยังไม่ส่งอนุมัติบนระบบ";
				$mail_description = "เรียน ".$receiver." <br><br><br> ในระบบเวลาการทำงานของท่านที่ยังไม่ส่งอนุมัติจำนวน ".$number_waiting." รายการ <br><br><br>จึงเรียนมาเพื่อทราบ";
				
				$mail_footer = "<br><br><br><a  href='http://192.168.1.203/IRCP/'>http://192.168.1.203/IRCP/</a>";
				$mail->CharSet = "utf-8";	
				$mail->isSMTP();
				$mail->Host='ircpmail.ircp.co.th';
				$mail->SMTPAuth = true;                               // Enable SMTP authentication
				$mail->Username = 'hr_leave';                 // SMTP username
				$mail->Password = 'HR4Leave@min';                           // SMTP password
				$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
				$mail->Port = 25;                                    // TCP port to connect to
				
				
				$mail->setFrom('hr_leave@ircp.co.th', 'HR Leave');

				$mail->clearAddresses();
				$mail->clearAttachments();		
				
				$receiver_mail = 'varinw@ircp.co.th';
				$mail->addAddress($receiver_mail, $receiver); 
				
				$mail->isHTML(true);
				
				$mail->Subject = $mail_title;
				$mail->Body    = $mail_description." ".$mail_footer;
				$mail->AltBody = 'This is a plain-text message body';

			$mail->SMTPKeepAlive = true;
			$mail->SMTPOptions = array(
					'ssl' => array(
						'verify_peer' => false,
						'verify_peer_name' => false,
						'allow_self_signed' => true
					)
				);
	/*
				$mail->smtpConnect([
					'ssl' => [
						'verify_peer' => false,
						'verify_peer_name' => false,
						'allow_self_signed' => true
					]
				]);
*/
				if(!$mail->send()) {
					$mail_status = $mail->ErrorInfo;				
				} else {
					$mail_status = 'COMPLETE';
				}	
/*
				$mail_status = '';
				
				while ($mail_status != 'COMPLETE') {
					if(!$mail->send()) {
						$mail_status = $mail->ErrorInfo;				
					} else {
						$mail_status = 'COMPLETE';
					}					
				}
*/				
				$sql = "	INSERT INTO TEST_MAIL (EMAIL_ADDRESS, RECEIVER, LAST_MODIFIED, RESULT)
								VALUES (?, ?, NOW(), ?)";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($receiver_m, $receiver, $mail_status));				
			}
			
			$conn->commit();
		}
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;				
?>