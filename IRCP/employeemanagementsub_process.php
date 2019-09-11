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
		$leavestatus = $explode[6];
		$startdate = $explode[7];
		$enddate = $explode[8];
		
		$countn = count($explode) - 2;	
		
		echo($countn);
		
		$exploden = explode("|", $key);
//		$employeeid = trim($explode[0]);
		$leaveid = trim($exploden[1]);
	
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

			$sql = "	INSERT INTO EMPLOYEE_LEAVE_HISTORY 
						(LEAVE_ID, EFFECTIVE_START_DATE, EFFECTIVE_END_DATE, CREATED_DATE, LAST_MODIFIED, USER_ID, EMPLOYEE_ID,  LEAVE_START_DATE, LEAVE_END_DATE, LEAVE_TYPE_ID, LEAVE_STATUS, LEAVE_DESCRIPTION, SUMMARY_LEAVE)
						VALUES 
						(?, NOW(), '9999-12-31 23:59:59', NOW(), NOW(), ?, ?, ?, ?, ?, 1, ?, ?)";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leaveid, $userid, $employeeid ,$startdate, $enddate, $leavetypeid , $description, $summaryleave));

			$sql = "	UPDATE EMPLOYEE_LEAVE_HISTORY
						SET GENERAL_1 = 1, EFFECTIVE_END_DATE = NOW()
						WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
						AND LEAVE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leaveid));		
			
			if ($leavestatus == 3) {
				$sql = "	INSERT INTO EMPLOYEE_LEAVE_HISTORY 
							(LEAVE_ID, EFFECTIVE_START_DATE, EFFECTIVE_END_DATE, CREATED_DATE, LAST_MODIFIED, USER_ID, EMPLOYEE_ID,  LEAVE_START_DATE, LEAVE_END_DATE, LEAVE_TYPE_ID, LEAVE_STATUS, LEAVE_DESCRIPTION, SUMMARY_LEAVE)
							SELECT LEAVE_ID, EFFECTIVE_END_DATE, '9999-12-31 23:59:59', CREATED_DATE, NOW(), USER_ID, EMPLOYEE_ID,  LEAVE_START_DATE, LEAVE_END_DATE, LEAVE_TYPE_ID, 3, LEAVE_DESCRIPTION, SUMMARY_LEAVE
							FROM EMPLOYEE_LEAVE_HISTORY
							WHERE GENERAL_1 = 1 AND LEAVE_ID = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($leaveid));		
			}
			else if ($leavestatus == 5) {
				$sql = "	INSERT INTO EMPLOYEE_LEAVE_HISTORY 
							(LEAVE_ID, EFFECTIVE_START_DATE, EFFECTIVE_END_DATE, CREATED_DATE, LAST_MODIFIED, USER_ID, EMPLOYEE_ID,  LEAVE_START_DATE, LEAVE_END_DATE, LEAVE_TYPE_ID, LEAVE_STATUS, LEAVE_DESCRIPTION, SUMMARY_LEAVE)
							SELECT LEAVE_ID, EFFECTIVE_END_DATE, NOW(), CREATED_DATE, NOW(), USER_ID, EMPLOYEE_ID,  LEAVE_START_DATE, LEAVE_END_DATE, LEAVE_TYPE_ID, 5, LEAVE_DESCRIPTION, SUMMARY_LEAVE
							FROM EMPLOYEE_LEAVE_HISTORY
							WHERE GENERAL_1 = 1 AND LEAVE_ID = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($leaveid));						
			}

			$sql = "	UPDATE EMPLOYEE_LEAVE_HISTORY
						SET GENERAL_1 = NULL
						WHERE LEAVE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leaveid));	
			
			for ($x = 9; $x <= $countn; $x++) {
				$cdate = $explode[$x]." 00:00:00";
				$sum_leave = 1;
				
				if ($x == 9 && $x == $countn) {
					$sum_leave = $summaryleave;
				}
				else if ($x == 9 && $x != $countn) {
					$hour = (int) substr($startdate, 11, 2);
					
					if ($hour > 8) {
						$sum_leave = 17 - $hour;
						
						if ($hour <= 12) {
							$sum_leave = $sum_leave - 1;
						}
						$sum_leave = $sum_leave/8;
					}
				}
				else if ($x == $countn) {
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
		}
		else if ($mode == 'C') {
			$sql = "	DELETE FROM EMPLOYEE_LEAVE_DETAIL
						WHERE LEAVE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leaveid));
				
			$sql = "	UPDATE EMPLOYEE_LEAVE_HISTORY
						SET GENERAL_1 = 1, EFFECTIVE_END_DATE = NOW()
						WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
						AND LEAVE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leaveid));		
			
			$sql = "	INSERT INTO EMPLOYEE_LEAVE_HISTORY 
						(LEAVE_ID, EFFECTIVE_START_DATE, EFFECTIVE_END_DATE, CREATED_DATE, LAST_MODIFIED, USER_ID, EMPLOYEE_ID,  LEAVE_START_DATE, LEAVE_END_DATE, LEAVE_TYPE_ID, LEAVE_STATUS, LEAVE_DESCRIPTION, SUMMARY_LEAVE, APPROVED_ID)
						SELECT LEAVE_ID, EFFECTIVE_END_DATE, '9999-12-31 23:59:59', CREATED_DATE, NOW(), ? , EMPLOYEE_ID,  ?, ?, ?, ?, ?, ?, APPROVED_ID
						FROM EMPLOYEE_LEAVE_HISTORY
						WHERE GENERAL_1 = 1 AND LEAVE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $startdate, $enddate, $leavetypeid, $leavestatus, $description, $summaryleave, $leaveid));	
			
			if ($leavestatus == 5) {
				$sql = "	UPDATE EMPLOYEE_LEAVE_HISTORY
							SET EFFECTIVE_END_DATE = NOW()
							WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
							AND LEAVE_ID = ?";
							
				$stmt = $conn->prepare($sql);
				$stmt->execute(array($leaveid));								
			}
		
			$sql = "	UPDATE EMPLOYEE_LEAVE_HISTORY
						SET GENERAL_1 = NULL
						WHERE LEAVE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leaveid));			

			for ($x = 9; $x <= $countn; $x++) {
				$cdate = $explode[$x]." 00:00:00";
				$sum_leave = 1;
				
				if ($x == 9 && $x == $countn) {
					$sum_leave = $summaryleave;
				}
				else if ($x == 9 && $x != $countn) {
					$hour = (int) substr($startdate, 11, 2);
					
					if ($hour > 8) {
						$sum_leave = 17 - $hour;
						
						if ($hour <= 12) {
							$sum_leave = $sum_leave - 1;
						}
						$sum_leave = $sum_leave/8;
					}
				}
				else if ($x == $countn) {
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

		}
		else if ($mode == 'D') {
			$sql = "	DELETE FROM EMPLOYEE_LEAVE_HISTORY
						WHERE LEAVE_ID = ?";


			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leaveid));		

			$sql = "	DELETE FROM EMPLOYEE_LEAVE_DETAIL
						WHERE LEAVE_ID = ?";


			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leaveid));					
		}
		
		$conn->commit();	
	}	
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;	
?>