<?
	require ('connection.php');
	
	try {
		$newindex = $_POST['newindex'] + 1;
		$oldindex = $_POST['oldindex'] + 1;
		
		$sql = "	UPDATE DEPARTMENT_EXECUTIVE
					SET GENERAL_1 = 1
					WHERE DEPARTMENT_ID = ? AND ORDERING = ?";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key, $oldindex));		

		$sql = "	UPDATE DEPARTMENT_EXECUTIVE
					SET ORDERING = ORDERING + 1
					WHERE DEPARTMENT_ID = ? AND ORDERING <> ? AND ORDERING >= ?";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key, $oldindex, $newindex));

		$sql = "	UPDATE DEPARTMENT_EXECUTIVE
					SET ORDERING = ?
					WHERE DEPARTMENT_ID = ? AND GENERAL_1 = 1";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($newindex, $key));		

		$sql = "	UPDATE DEPARTMENT_EXECUTIVE
					SET GENERAL_1 = 0
					WHERE DEPARTMENT_ID = ?";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));			
		
		$conn->commit();
	}	
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	
	$conn = null;	
?>