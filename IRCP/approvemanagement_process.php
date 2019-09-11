<?
	require ('connection.php');
	
	try {
		$leaveid = $key;
		
		$sql = "	SELECT ELH.SUMMARY_LEAVE, ELH.EMPLOYEE_ID, ELH.APPROVED_ID, ELH.LEAVE_START_DATE, ELH.LEAVE_END_DATE, LP.LEAVE_NAME
					FROM EMPLOYEE_LEAVE_HISTORY ELH
					LEFT JOIN LEAVE_POLICY LP ON LP.LEAVE_TYPE_ID = ELH.LEAVE_TYPE_ID
					WHERE  SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
					AND ELH.LEAVE_ID = ?";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($leaveid));	
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$summary_leave = $arr['SUMMARY_LEAVE'];
			$employeeid = $arr['EMPLOYEE_ID'];
			$approvedid = $arr['APPROVED_ID'];
			$leavename = $arr['LEAVE_NAME'];
			$leavestart = substr($arr['LEAVE_START_DATE'],8,2)."/".substr($arr['LEAVE_START_DATE'],5,2)."/".((int)substr($arr['LEAVE_START_DATE'],0,4) + 543)." ".substr($arr['LEAVE_START_DATE'],11,5);
			$leaveend = substr($arr['LEAVE_END_DATE'],8,2)."/".substr($arr['LEAVE_END_DATE'],5,2)."/".((int)substr($arr['LEAVE_END_DATE'],0,4) + 543)." ".substr($arr['LEAVE_END_DATE'],11,5);	
		}
		
		$sql = "	SELECT FIRST_NAME, LAST_NAME, EMAIL, DEPARTMENT_ID
					FROM EMPLOYEE
					WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
					AND EMPLOYEE_ID = ?";
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($employeeid));
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$firstname = $arr['FIRST_NAME'];
			$lastname = $arr['LAST_NAME'];
			$receiver_mail = $arr['EMAIL'];
			$receiver = $arr['FIRST_NAME']." ".$arr['LAST_NAME'];
			$departmentid = $arr['DEPARTMENT_ID'];
		}
		
		$sql = "	SELECT FIRST_NAME, LAST_NAME, EMAIL, DEPARTMENT_ID
					FROM EMPLOYEE
					WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
					AND EMPLOYEE_ID = ?";
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($approvedid));
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$approvename = $arr['FIRST_NAME']." ".$arr['LAST_NAME'];
			$approvename_email = $arr['EMAIL'];
		}
		
		$sql = "	SELECT COUNT(*) COUNTN
					FROM EMPLOYEE_LEAVE_HISTORY
					WHERE LEAVE_ID = ?
					AND APPROVED_ID IS NOT NULL";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($leaveid));	

		if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$approvedcount = $arr['COUNTN'];
		}
		
		if ($mode == 'A') {
					
		}
		else if ($mode == 'C') {
			$sql = "	UPDATE EMPLOYEE_LEAVE_HISTORY
						SET APPROVED_DATE = NOW(), LAST_MODIFIED = NOW(), USER_ID = ?
						WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
						AND LEAVE_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $leaveid));	
			
			if ($summary_leave <= 3) {
				if ($approvedcount == 1) {
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
					
					$conn->commit();
					
					require ('PHPMailer.php');
					require("SMTP.php");
					require("Exception.php");					
					
					$mail = new PHPMailer\PHPMailer\PHPMailer();	

					$mail_title = 'แจ้งใบลารอการอนุมัติของ คุณ'.$firstname." ".$lastname;
					$mail_description = "เรียน คุณ".$firstname." ".$lastname."<br><br><br>ใบลาของท่านได้รับการอนุมัติโดย คุณ".$approvename." เรียบร้อยแล้ว <br><br><br>ประเภทใบลา: ".$leavename." <br><br> ตั้งแต่วันที่ ".$leavestart." ถึง ".$leaveend." จำนวน ".$summary_leave." วัน<br><br><br>จึงเรียนมาเพื่อทราบ";
					
					include('sendmail.php');						
					
					$mail = '';
					
					$mail = new PHPMailer\PHPMailer\PHPMailer();
					
					$mail_title = 'แจ้งใบลารอการอนุมัติของ คุณ'.$firstname." ".$lastname;
					$mail_description = "เรียน คุณ".$supervisor_name."<br><br><br>แจ้งใบลารอการอนุมัติของคุณ ".$firstname." ".$lastname." <br><br><br>ประเภทใบลา: ".$leavename." <br><br> ตั้งแต่วันที่ ".$leavestart." ถึง ".$leaveend." จำนวน ".$summary_leave." วัน<br><br><br>จึงเรียนมาเพื่อทราบ";
					
					
//					$supervisor_email = 'varinw@ircp.co.th';
					
					$receiver_mail =  $supervisor_email;
					$receiver =  $supervisor_name;
					
					include('sendmail.php');	
				}
				else {
					$sql = "	UPDATE EMPLOYEE_LEAVE_HISTORY
								SET GENERAL_1 = 1, EFFECTIVE_END_DATE = NOW()
								WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
								AND LEAVE_ID = ?";

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($leaveid));		
					
					$sql = "	INSERT INTO EMPLOYEE_LEAVE_HISTORY 
								(LEAVE_ID, EFFECTIVE_START_DATE, EFFECTIVE_END_DATE, CREATED_DATE, LAST_MODIFIED, USER_ID, EMPLOYEE_ID,  LEAVE_START_DATE, LEAVE_END_DATE, LEAVE_TYPE_ID, LEAVE_STATUS, LEAVE_DESCRIPTION, SUMMARY_LEAVE)
								SELECT LEAVE_ID, EFFECTIVE_END_DATE, '9999-12-31 23:59:59', CREATED_DATE, NOW(), USER_ID, EMPLOYEE_ID,  LEAVE_START_DATE, LEAVE_END_DATE, LEAVE_TYPE_ID, 3, LEAVE_DESCRIPTION, SUMMARY_LEAVE
								FROM EMPLOYEE_LEAVE_HISTORY
								WHERE GENERAL_1 = 1 AND LEAVE_ID = ?";

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($leaveid));		

					$sql = "	UPDATE EMPLOYEE_LEAVE_HISTORY
								SET GENERAL_1 = NULL
								WHERE LEAVE_ID = ?";

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($leaveid));	
					
					$conn->commit();	

					require ('PHPMailer.php');
					require("SMTP.php");
					require("Exception.php");					
					
					$mail = new PHPMailer\PHPMailer\PHPMailer();	

					$mail_title = 'แจ้งใบลารอการอนุมัติของ คุณ'.$firstname." ".$lastname;
					$mail_description = "เรียน คุณ".$firstname." ".$lastname."<br><br><br>ใบลาของท่านได้รับการอนุมัติโดย คุณ".$approvename." เรียบร้อยแล้ว <br><br><br>ประเภทใบลา: ".$leavename." <br><br> ตั้งแต่วันที่ ".$leavestart." ถึง ".$leaveend." จำนวน ".$summary_leave." วัน<br><br><br>จึงเรียนมาเพื่อทราบ";
					$mail_footer = '<br><br><br><a>http://192.168.1.201:8080/IRCP/</a>';
					
					include('sendmail.php');						
				}
			}
			else {
				if ($approvedcount == 1) {
					$sql = "	SELECT COUNT(*) COUNTN
								FROM DEPARTMENT_EXECUTIVE
								WHERE EMPLOYEE_ID = ?";
					$stmt = $conn->prepare($sql);
					$stmt->execute(array($employeeid));
					
					$exec_level = 0;
					
					while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$exec_level = $arr['COUNTN'];
					}
					
					if ($exec_level > 0) {
						$sql = "	SELECT DE.EMPLOYEE_ID, EM.EMAIL, EM.FIRST_NAME, EM.LAST_NAME
									FROM DEPARTMENT_EXECUTIVE DE
									LEFT JOIN EMPLOYEE EM ON EM.EMPLOYEE_ID = DE.EMPLOYEE_ID
									WHERE DE.DEPARTMENT_ID = ? AND DE.ORDERING > 1 AND EM.EMPLOYEE_ID <> ?
									ORDER BY DE.ORDERING";
					}
					else {						
						$sql = "	SELECT DE.EMPLOYEE_ID, EM.EMAIL, EM.FIRST_NAME, EM.LAST_NAME
									FROM DEPARTMENT_EXECUTIVE DE
									LEFT JOIN EMPLOYEE EM ON EM.EMPLOYEE_ID = DE.EMPLOYEE_ID
									WHERE DE.DEPARTMENT_ID = ? AND DE.ORDERING > 1 AND EM.EMPLOYEE_ID <> ? AND DE.LEAVE_APPROVED = 1
									ORDER BY DE.ORDERING";
					}
					
					$stmt = $conn->prepare($sql);
					$stmt->execute(array($departmentid, $employeeid));
					
					$n = 0;
					$found = 0;
					$supervisorid = 0;
					$supervisor_name = '';
					$supervisor_email = '';

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
					
					$conn->commit();
					
					require ('PHPMailer.php');
					require("SMTP.php");
					require("Exception.php");					
					
					$mail = new PHPMailer\PHPMailer\PHPMailer();	

					$mail_title = 'แจ้งใบลารอการอนุมัติของ คุณ'.$firstname." ".$lastname;
					$mail_description = "เรียน คุณ".$firstname." ".$lastname."<br><br><br>ใบลาของท่านได้รับการอนุมัติโดย คุณ".$approvename." เรียบร้อยแล้ว <br><br><br>ประเภทใบลา: ".$leavename." <br><br> ตั้งแต่วันที่ ".$leavestart." ถึง ".$leaveend." จำนวน ".$summary_leave." วัน<br><br><br>จึงเรียนมาเพื่อทราบ";
					$mail_footer = '<br><br><br><a>http://192.168.1.201:8080/IRCP/</a>';
					
					include('sendmail.php');						
					
					$mail_title = 'แจ้งใบลารอการอนุมัติของ คุณ'.$firstname." ".$lastname;
					$mail_description = "เรียน คุณ".$supervisor_name."<br><br><br>แจ้งใบลารอการอนุมัติของคุณ ".$firstname." ".$lastname." <br><br><br>ประเภทใบลา: ".$leavename." <br><br> ตั้งแต่วันที่ ".$leavestart." ถึง ".$leaveend." จำนวน ".$summary_leave." วัน<br><br><br>จึงเรียนมาเพื่อทราบ";
					$mail_footer = '<br><br><br><a>http://192.168.1.201:8080/IRCP/</a>';
					
					
//					$supervisor_email = 'varinw@ircp.co.th';
					
					$receiver_mail =  $supervisor_email;
					$receiver =  $supervisor_name;
					
					include('sendmail.php');						
					
				}
				else if ($approvedcount == 2) {
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
					
					if ($supervisorid == $approvedid) {
						$sql = "	UPDATE EMPLOYEE_LEAVE_HISTORY
									SET GENERAL_1 = 1, EFFECTIVE_END_DATE = NOW()
									WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
									AND LEAVE_ID = ?";

						$stmt = $conn->prepare($sql);
						$stmt->execute(array($leaveid));		
						
						$sql = "	INSERT INTO EMPLOYEE_LEAVE_HISTORY 
									(LEAVE_ID, EFFECTIVE_START_DATE, EFFECTIVE_END_DATE, CREATED_DATE, LAST_MODIFIED, USER_ID, EMPLOYEE_ID,  LEAVE_START_DATE, LEAVE_END_DATE, LEAVE_TYPE_ID, LEAVE_STATUS, LEAVE_DESCRIPTION, SUMMARY_LEAVE)
									SELECT LEAVE_ID, EFFECTIVE_END_DATE, '9999-12-31 23:59:59', CREATED_DATE, NOW(), USER_ID, EMPLOYEE_ID,  LEAVE_START_DATE, LEAVE_END_DATE, LEAVE_TYPE_ID, 3, LEAVE_DESCRIPTION, SUMMARY_LEAVE
									FROM EMPLOYEE_LEAVE_HISTORY
									WHERE GENERAL_1 = 1 AND LEAVE_ID = ?";

						$stmt = $conn->prepare($sql);
						$stmt->execute(array($leaveid));		

						$sql = "	UPDATE EMPLOYEE_LEAVE_HISTORY
									SET GENERAL_1 = NULL
									WHERE LEAVE_ID = ?";

						$stmt = $conn->prepare($sql);
						$stmt->execute(array($leaveid));	
						
						$conn->commit();	

						require ('PHPMailer.php');
						require("SMTP.php");
						require("Exception.php");					
						
						$mail = new PHPMailer\PHPMailer\PHPMailer();	

						$mail_title = 'แจ้งใบลารอการอนุมัติของ คุณ'.$firstname." ".$lastname;
						$mail_description = "เรียน คุณ".$firstname." ".$lastname."<br><br><br>ใบลาของท่านได้รับการอนุมัติโดย คุณ".$approvename." เรียบร้อยแล้ว <br><br><br>ประเภทใบลา: ".$leavename." <br><br> ตั้งแต่วันที่ ".$leavestart." ถึง ".$leaveend." จำนวน ".$summary_leave." วัน<br><br><br>จึงเรียนมาเพื่อทราบ";
						$mail_footer = '<br><br><br><a>http://192.168.1.201:8080/IRCP/</a>';
						
						include('sendmail.php');							
					}
					else {
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
						
						$conn->commit();
						
						require ('PHPMailer.php');
						require("SMTP.php");
						require("Exception.php");					
						
						$mail = new PHPMailer\PHPMailer\PHPMailer();	

						$mail_title = 'แจ้งใบลารอการอนุมัติของ คุณ'.$firstname." ".$lastname;
						$mail_description = "เรียน คุณ".$firstname." ".$lastname."<br><br><br>ใบลาของท่านได้รับการอนุมัติโดย คุณ".$approvename." เรียบร้อยแล้ว <br><br><br>ประเภทใบลา: ".$leavename." <br><br> ตั้งแต่วันที่ ".$leavestart." ถึง ".$leaveend." จำนวน ".$summary_leave." วัน<br><br><br>จึงเรียนมาเพื่อทราบ";
						$mail_footer = '<br><br><br><a>http://192.168.1.201:8080/IRCP/</a>';
						
						include('sendmail.php');						
						
						$mail_title = 'แจ้งใบลารอการอนุมัติของ คุณ'.$firstname." ".$lastname;
						$mail_description = "เรียน คุณ".$supervisor_name."<br><br><br>แจ้งใบลารอการอนุมัติของคุณ ".$firstname." ".$lastname." <br><br><br>ประเภทใบลา: ".$leavename." <br><br> ตั้งแต่วันที่ ".$leavestart." ถึง ".$leaveend." จำนวน ".$summary_leave." วัน<br><br><br>จึงเรียนมาเพื่อทราบ";
						$mail_footer = '<br><br><br><a>http://192.168.1.201:8080/IRCP/</a>';
						
						
//						$supervisor_email = 'varinw@ircp.co.th';
						
						$receiver_mail =  $supervisor_email;
						$receiver =  $supervisor_name;
						
						include('sendmail.php');	
					}
				}
				else {
					$sql = "	UPDATE EMPLOYEE_LEAVE_HISTORY
								SET GENERAL_1 = 1, EFFECTIVE_END_DATE = NOW()
								WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
								AND LEAVE_ID = ?";

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($leaveid));		
					
					$sql = "	INSERT INTO EMPLOYEE_LEAVE_HISTORY 
								(LEAVE_ID, EFFECTIVE_START_DATE, EFFECTIVE_END_DATE, CREATED_DATE, LAST_MODIFIED, USER_ID, EMPLOYEE_ID,  LEAVE_START_DATE, LEAVE_END_DATE, LEAVE_TYPE_ID, LEAVE_STATUS, LEAVE_DESCRIPTION, SUMMARY_LEAVE)
								SELECT LEAVE_ID, EFFECTIVE_END_DATE, '9999-12-31 23:59:59', CREATED_DATE, NOW(), USER_ID, EMPLOYEE_ID,  LEAVE_START_DATE, LEAVE_END_DATE, LEAVE_TYPE_ID, 3, LEAVE_DESCRIPTION, SUMMARY_LEAVE
								FROM EMPLOYEE_LEAVE_HISTORY
								WHERE GENERAL_1 = 1 AND LEAVE_ID = ?";

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($leaveid));		

					$sql = "	UPDATE EMPLOYEE_LEAVE_HISTORY
								SET GENERAL_1 = NULL
								WHERE LEAVE_ID = ?";

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($leaveid));	
					
					$conn->commit();	

					require ('PHPMailer.php');
					require("SMTP.php");
					require("Exception.php");					
					
					$mail = new PHPMailer\PHPMailer\PHPMailer();	

					$mail_title = 'แจ้งใบลารอการอนุมัติของ คุณ'.$firstname." ".$lastname;
					$mail_description = "เรียน คุณ".$firstname." ".$lastname."<br><br><br>ใบลาของท่านได้รับการอนุมัติโดย คุณ".$approvename." เรียบร้อยแล้ว <br><br><br>ประเภทใบลา: ".$leavename." <br><br> ตั้งแต่วันที่ ".$leavestart." ถึง ".$leaveend." จำนวน ".$summary_leave." วัน<br><br><br>จึงเรียนมาเพื่อทราบ";
					$mail_footer = '<br><br><br><a>http://192.168.1.201:8080/IRCP/</a>';
					
					include('sendmail.php');						
				}
			}
		}
		else if ($mode == 'D') {
			$leaveid = $key;
			
			$sql = "	UPDATE EMPLOYEE_LEAVE_HISTORY
						SET APPROVED_DATE = NOW() , LAST_MODIFIED = NOW(), USER_ID = ?, LEAVE_STATUS = 5, EFFECTIVE_END_DATE = NOW()
						WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
						AND LEAVE_ID = ?";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $leaveid));		

			$conn->commit();
			
			require ('PHPMailer.php');
			require("SMTP.php");
			require("Exception.php");					
			
			$mail = new PHPMailer\PHPMailer\PHPMailer();	

			$mail_title = 'แจ้งใบลารอการอนุมัติของ คุณ'.$firstname." ".$lastname;
			$mail_description = "เรียน คุณ".$firstname." ".$lastname."<br><br><br>ใบลาของท่านได้รับการปฏิเสธโดย คุณ".$approvename." <br><br><br>ประเภทใบลา: ".$leavename." <br><br> ตั้งแต่วันที่ ".$leavestart." ถึง ".$leaveend." จำนวน ".$summary_leave." วัน<br><br><br>จึงเรียนมาเพื่อทราบ";
			$mail_footer = '<br><br><br><a>http://192.168.1.201:8080/IRCP/</a>';
			
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