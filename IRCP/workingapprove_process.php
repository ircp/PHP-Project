<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $key);
		$employeeid = $explode[0];
		$workingdate = $explode[1];
		
		if ($mode == 'C') {
			$workstatus = 3;
		}
		else {
			$workstatus = 4;
		}
		
		$sql = "	UPDATE WORKING_DAY
					SET GENERAL_1 = 1, EFFECTIVE_END_DATE = NOW()
					WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
					AND EMPLOYEE_ID = ? AND WORKING_DAY = ?";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($employeeid, $workingdate));	

		$sql = "	INSERT INTO WORKING_DAY (EMPLOYEE_ID, WORKING_DAY, EFFECTIVE_START_DATE, EFFECTIVE_END_DATE, CREATED_DATE, LAST_MODIFIED, USER_ID, WORKING_STATUS, WORKING_IN, WORKING_OUT, DESCRIPTION, APPROVED_ID, APPROVED_DATE,COMMENT, IN_OUT_ID, LEAVE_ID, INTERVAL_IN, INTERVAL_OUT, GENERAL_1)
					SELECT EMPLOYEE_ID, WORKING_DAY, NOW(), '9999-12-31 23:59:59', CREATED_DATE, NOW(), ?, ?, WORKING_IN, WORKING_OUT, DESCRIPTION, APPROVED_ID , NOW() ,COMMENT, IN_OUT_ID, LEAVE_ID, INTERVAL_IN, INTERVAL_OUT, GENERAL_1
					FROM WORKING_DAY
					WHERE GENERAL_1 = 1 AND EMPLOYEE_ID = ? AND WORKING_DAY = ?";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($userid, $workstatus, $employeeid, $workingdate));
		
		
		$sql = "	UPDATE WORKING_DAY
					SET GENERAL_1 = NULL
					WHERE EMPLOYEE_ID = ? AND WORKING_DAY = ?";
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($employeeid, $workingdate));	
		
		$conn->commit();
	}	
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	
	$conn = null;	
?>