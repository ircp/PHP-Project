<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$departmentname = trim($explode[0]);
		$abbreviation = trim($explode[1]);		
		$description = trim($explode[2]);		
		$validindcode = trim($explode[3]);	
		
		
		if ($mode == 'A') {
			
			$sql = "	INSERT INTO DEPARTMENT (CREATED_DATE, LAST_MODIFIED, DEPARTMENT_NAME, ABBREVIATION, DESCRIPTION, VALID_IND_CODE, USER_ID)
						VALUES (NOW() , NOW() , ?, ?, ?, ?, ?)";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($departmentname, $abbreviation, $description,  $validindcode, $userid));							
		}
		else if ($mode == 'C') {
			$sql = "	UPDATE DEPARTMENT
						SET	LAST_MODIFIED = NOW(), DEPARTMENT_NAME = ?, ABBREVIATION = ?, DESCRIPTION = ?, VALID_IND_CODE = ?, USER_ID = ?
						WHERE DEPARTMENT_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($departmentname, $abbreviation, $description,  $validindcode, $userid,$key));	
		}
		else if ($mode == 'D') {
			$sql = "	DELETE FROM DEPARTMENT
						WHERE DEPARTMENT_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($key));

			$sql = "	UPDATE EMPLOYEE
						SET DEPARTMENT_ID = 0
						WHERE DEPARTMENT_ID = ?";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($key));
						
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