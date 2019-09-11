<?
	require ('connection.php');
	
	try {

		$explode = explode("|", $key);
		$employeeid = $explode[0];
		$workingday = $explode[1];
		
		$explode = explode("|", $parameter);
		$description = trim($explode[0]);	
		
		if ($mode == 'C') {
			$workingstatus = 3;
		}
		else {
			$workingstatus = 5;
		}
		
		$sql = "	SELECT EMPLOYEE_ID FROM USER WHERE USER_ID = ?";
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($userid));	

		$approveid = 1;
		
		if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$approveid = $arr['EMPLOYEE_ID'];
		}
		
		$sql = "	UPDATE WORKING_DAY
					SET EFFECTIVE_END_DATE = NOW(), GENERAL_1 = 1
					WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
					AND EMPLOYEE_ID = ? AND WORKING_DAY = ?";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($employeeid, $workingday));

		$sql = "	INSERT INTO WORKING_DAY 
					(EMPLOYEE_ID, WORKING_DAY, EFFECTIVE_START_DATE, EFFECTIVE_END_DATE, CREATED_DATE, LAST_MODIFIED, USER_ID, WORKING_STATUS, WORKING_IN, WORKING_OUT, DESCRIPTION, APPROVED_ID, APPROVED_DATE, COMMENT, IN_OUT_ID, LEAVE_ID, INTERVAL_IN, INTERVAL_OUT)
					SELECT 
					EMPLOYEE_ID, WORKING_DAY, EFFECTIVE_END_DATE, '9999-12-31 23:59:59', CREATED_DATE, NOW() , ?, ?, WORKING_IN, WORKING_OUT, ?, ?, NOW(), COMMENT, IN_OUT_ID, LEAVE_ID, INTERVAL_IN, INTERVAL_OUT
					FROM WORKING_DAY
					WHERE EMPLOYEE_ID = ? AND WORKING_DAY = ? AND GENERAL_1 = 1";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($userid, $workingstatus , $description ,$approveid ,$employeeid, $workingday));					

		$sql = "	UPDATE WORKING_DAY
					SET GENERAL_1 = NULL
					WHERE EMPLOYEE_ID = ? AND WORKING_DAY = ?";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($employeeid, $workingday));	
		
		$conn->commit();	
	}	
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;	
?>