<?
	require ('connection.php');
	
	try {
		
		$explode = explode("|", $key);
		$departmentid = $explode[0];
		
		$employeeid  = $parameter;
		
		
		if ($mode == 'A') {
			$sql = " SELECT MAX(ORDERING) + 1 MAX_ORDER FROM DEPARTMENT_EXECUTIVE
						WHERE DEPARTMENT_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($departmentid));	

			$ordering = 1;
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$ordering = $arr['MAX_ORDER'];
			}
			
			if ($ordering == 0 || $ordering == '') {
				$ordering = 1;
			}
			
			$sql = "	INSERT INTO DEPARTMENT_EXECUTIVE (DEPARTMENT_ID, EMPLOYEE_ID, CREATED_DATE, LAST_MODIFIED, USER_ID, ORDERING, GENERAL_1, LEAVE_APPROVED) VALUES (?, ?, NOW(), NOW(), ?, ?, 0, 1)";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($departmentid, $employeeid, $userid, $ordering));				
		}
		else if ($mode == 'D') {
			$sql = " SELECT ORDERING FROM DEPARTMENT_EXECUTIVE
						WHERE DEPARTMENT_ID = ? AND EMPLOYEE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($departmentid, $employeeid));	

			$ordering = 1;
			
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$ordering = $arr['ORDERING'];
			}
			
				
			$sql = "	UPDATE DEPARTMENT_EXECUTIVE
						SET ORDERING = ORDERING - 1
						WHERE DEPARTMENT_ID = ?  AND ORDERING > ?";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($departmentid,$ordering));
			
			$sql = "	DELETE FROM DEPARTMENT_EXECUTIVE
						WHERE DEPARTMENT_ID = ? AND EMPLOYEE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($departmentid, $employeeid));
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