<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		$employeeid = trim($explode[0]);
		$leavetypeid = trim($explode[1]);
		$startdateu = trim($explode[2]);
		$enddateu = trim($explode[3]);
		$summaryleave = trim($explode[4]);
		$description = trim($explode[5]);
		$startdate = trim($explode[6]);
		$enddate = trim($explode[7]);
		
		$countn = count($explode) - 2;
		
		if ($mode == 'A') {
			$sql = "	INSERT INTO SEQUENCE_LEAVE_HISTORY (EMPLOYEE_ID) VALUES (?)";
			
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($employeeid));
			
			$sql = "	SELECT LAST_INSERT_ID() LEAVE_ID";
			
			$stmt = $conn->prepare($sql);
			$stmt->execute();
			
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$leaveid = $arr['LEAVE_ID'];
			}
			
			$sql = "	SELECT LEAVE_NAME FROM LEAVE_POLICY
						WHERE LEAVE_TYPE_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leavetypeid));

			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$leavename = $arr['LEAVE_NAME'];
			}
			
			
			$sql = "	INSERT INTO EMPLOYEE_LEAVE_HISTORY 
						(LEAVE_ID, EFFECTIVE_START_DATE, EFFECTIVE_END_DATE, CREATED_DATE, LAST_MODIFIED, USER_ID, EMPLOYEE_ID,  LEAVE_START_DATE, LEAVE_END_DATE, LEAVE_TYPE_ID, LEAVE_STATUS, LEAVE_DESCRIPTION, SUMMARY_LEAVE)
						VALUES 
						(?, NOW(), '9999-12-31 23:59:59', NOW(), NOW(), ?, ?, ?, ?, ?, 1, ?, ?)";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leaveid, $userid, $employeeid ,$startdate, $enddate, $leavetypeid , $description, $summaryleave));
			
			$sql = "	SELECT FIRST_NAME, LAST_NAME, EMAIL
						FROM EMPLOYEE
						WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
						AND EMPLOYEE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($employeeid));

			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$firstname = $arr['FIRST_NAME'];
				$lastname = $arr['LAST_NAME'];
				$receiver_mail = $arr['EMAIL'];
				$receiver = $arr['FIRST_NAME']." ".$arr['LAST_NAME'];
			}
			
			$sql = " SELECT DEPARTMENT_ID FROM EMPLOYEE
						WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
						AND EMPLOYEE_ID = ?";

		
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($employeeid));

			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$departmentid = $arr['DEPARTMENT_ID'];
			}

			
			$sql = "	SELECT DE.EMPLOYEE_ID, EM.EMAIL, EM.FIRST_NAME, EM.LAST_NAME
						FROM DEPARTMENT_EXECUTIVE DE
						LEFT JOIN EMPLOYEE EM ON EM.EMPLOYEE_ID = DE.EMPLOYEE_ID
						WHERE DE.DEPARTMENT_ID = ?  AND EM.EMPLOYEE_ID <> ?
						ORDER BY DE.ORDERING";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($departmentid, $employeeid));
			
			$n = 0;
			$found = 0;
			$supervisorid = 0;

			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$n = $n + 1;
				
				if ($found == 0) {
					if ($n == 1 && $employeeid != $arr['EMPLOYEE_ID']) {
						$supervisorid = $arr['EMPLOYEE_ID'];
						$supervisor_name = $arr['FIRST_NAME']." ".$arr['LAST_NAME'];
						$supervisor_email = $arr['EMAIL'];
						$found = 1;
					}
					else {
						if ($employeeid != $arr['EMPLOYEE_ID']) {
							$supervisorid = $arr['EMPLOYEE_ID'];
							$supervisor_name = $arr['FIRST_NAME']." ".$arr['LAST_NAME'];
							$supervisor_email = $arr['EMAIL'];
							$found = 1;							
						}
					}
				}
			}
			
			if ($supervisorid == 0) {
				$sql = "	SELECT DE.EMPLOYEE_ID, EM.EMAIL, EM.FIRST_NAME, EM.LAST_NAME
							FROM DEPARTMENT_EXECUTIVE DE
							LEFT JOIN EMPLOYEE EM ON EM.EMPLOYEE_ID = DE.EMPLOYEE_ID
							WHERE DE.DEPARTMENT_ID = 12 AND DE.ORDERING = 1
							ORDER BY DE.ORDERING";

				$stmt = $conn->prepare($sql);
				$stmt->execute();
				
				$n = 0;
				$found = 0;
				$supervisorid = 0;

				if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$supervisorid = $arr['EMPLOYEE_ID'];
					$supervisor_name = $arr['FIRST_NAME']." ".$arr['LAST_NAME'];
					$supervisor_email = $arr['EMAIL'];
				}
			}
			
			$sql = "	UPDATE EMPLOYEE_LEAVE_HISTORY
						SET GENERAL_1 = 1, EFFECTIVE_END_DATE = NOW()
						WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
						AND LEAVE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leaveid));		
			
			$sql = "	INSERT INTO EMPLOYEE_LEAVE_HISTORY 
						(LEAVE_ID, EFFECTIVE_START_DATE, EFFECTIVE_END_DATE, CREATED_DATE, LAST_MODIFIED, USER_ID, EMPLOYEE_ID,  LEAVE_START_DATE, LEAVE_END_DATE, LEAVE_TYPE_ID, LEAVE_STATUS, LEAVE_DESCRIPTION, SUMMARY_LEAVE, APPROVED_ID)
						SELECT LEAVE_ID, EFFECTIVE_END_DATE, '9999-12-31 23:59:59', CREATED_DATE, NOW(), USER_ID, EMPLOYEE_ID,  LEAVE_START_DATE, LEAVE_END_DATE, LEAVE_TYPE_ID, 2, LEAVE_DESCRIPTION, SUMMARY_LEAVE, ?
						FROM EMPLOYEE_LEAVE_HISTORY
						WHERE GENERAL_1 = 1 AND LEAVE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($supervisorid, $leaveid));		

			$sql = "	UPDATE EMPLOYEE_LEAVE_HISTORY
						SET GENERAL_1 = NULL
						WHERE LEAVE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leaveid));				
			
			
			
			for ($x = 8; $x <= $countn; $x++) {
				$cdate = $explode[$x]." 00:00:00";
				$sum_leave = 1;
				
				if ($x == 8 && $x == $countn) {
					$sum_leave = $summaryleave;
				}
				else if ($x == 8 && $x != $countn) {
					$hour = (int) substr($startdate, 11, 2);
					
					if ($hour > 8) {
						$sum_leave = 17 - $hour;
						
						if ($hour <= 12) {
							$sum_leave = $sum_leave - 1;
						}
						$sum_leave = $sum_leave/8;
					}
				}
				else if ($x == $countn && $x != 8) {
					$hour = (int) substr($enddate, 11, 2);
					
					if ($hour <= 12) {
						$sum_leave = 4 - (12 - $hour);
					}
					else {
						$sum_leave = ($hour - 8);
						
						if ($hour >=12 ) {
							$sum_leave = $sum_leave - 1;
						}						
					}
					
					$sum_leave = $sum_leave/8;
				}
				
				$sql = "	INSERT INTO EMPLOYEE_LEAVE_DETAIL (LEAVE_ID, LEAVE_DATE, CREATED_DATE, LAST_MODIFIED, USER_ID, SUMMARY_LEAVE)
							VALUES  (?, ?, NOW(), NOW(), ?, ?)";
							
				$stmt = $conn->prepare($sql);
				$stmt->execute(array($leaveid, $cdate, $userid, $sum_leave));
			}	
			
			$conn->commit();

			require ('PHPMailer.php');
			require("SMTP.php");
			require("Exception.php");

			$mail = new PHPMailer\PHPMailer\PHPMailer();			
			
			$mail_title = 'แจ้งใบลารอการอนุมัติของ คุณ'.$firstname." ".$lastname;
			$mail_description = "เรียน คุณ".$firstname." ".$lastname."<br><br>ใบลาของท่านได้รับการบันทึกเข้าระบบเรียบร้อยแล้ว <br><br>ประเภทใบลา: ".$leavename." <br><br> ตั้งแต่วันที่ ".$startdateu." ถึง ".$enddateu." จำนวน ".$summaryleave." วัน<br><br>จึงเรียนมาเพื่อทราบ";
			
			include('sendmail.php');
			
			$mail = '';
			$mail = new PHPMailer\PHPMailer\PHPMailer();			
			
			$mail_title = 'แจ้งใบลารอการอนุมัติของ คุณ'.$firstname." ".$lastname;
			$mail_description = "เรียน คุณ".$supervisor_name."<br><br>แจ้งใบลารอการอนุมัติของคุณ ".$firstname." ".$lastname." <br><br>ประเภทใบลา: ".$leavename." <br><br> ตั้งแต่วันที่ ".$startdateu." ถึง ".$enddateu." จำนวน ".$summaryleave." วัน<br><br>จึงเรียนมาเพื่อทราบ";

			$receiver_mail =  $supervisor_email;
			$receiver =  $supervisor_name;
			
			include('sendmail.php');
		}
	}	
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	
	$conn = null;	
?>