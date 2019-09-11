<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$description = trim($explode[0]);
		
		$explode = explode("|", $key);
		$employeeid = $explode[0];
		$workingdate = $explode[1];
		
		if ($mode == 'C') {

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
			
			$sql = "	UPDATE WORKING_DAY
						SET GENERAL_1 = 1, EFFECTIVE_END_DATE = NOW()
						WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
						AND EMPLOYEE_ID = ? AND WORKING_DAY = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($employeeid, $workingdate));	

			$sql = "	INSERT INTO WORKING_DAY (EMPLOYEE_ID, WORKING_DAY, EFFECTIVE_START_DATE, EFFECTIVE_END_DATE, CREATED_DATE, LAST_MODIFIED, USER_ID, WORKING_STATUS, WORKING_IN, WORKING_OUT, DESCRIPTION, APPROVED_ID, APPROVED_DATE,COMMENT, IN_OUT_ID, LEAVE_ID, INTERVAL_IN, INTERVAL_OUT, GENERAL_1)
						SELECT EMPLOYEE_ID, WORKING_DAY, NOW(), '9999-12-31 23:59:59', CREATED_DATE, NOW(), ?, 2, WORKING_IN, WORKING_OUT, ?, ?, APPROVED_DATE, COMMENT, IN_OUT_ID, LEAVE_ID, INTERVAL_IN, INTERVAL_OUT, GENERAL_1
						FROM WORKING_DAY
						WHERE GENERAL_1 = 1 AND EMPLOYEE_ID = ? AND WORKING_DAY = ?";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $description, $supervisorid, $employeeid, $workingdate));
			
			
			$sql = "	UPDATE WORKING_DAY
						SET GENERAL_1 = NULL
						WHERE EMPLOYEE_ID = ? AND WORKING_DAY = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($employeeid, $workingdate));			
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