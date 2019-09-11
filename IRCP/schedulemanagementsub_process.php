<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$employeeid = trim($explode[0]);
		
		$explode = explode("|", $key);
		
		$scheduleid = trim($explode[0]);
		
		if ($mode == 'A') {
			$sql = "	INSERT INTO SCHEDULE_MAIL (SCHEDULE_ID, EMPLOYEE_ID)
						VALUES (?, ?)";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($scheduleid, $employeeid));							
		}
		else if ($mode == 'D') {
			$sql = "	DELETE FROM SCHEDULE_MAIL
						WHERE SCHEDULE_ID = ? AND EMPLOYEE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($scheduleid, $employeeid));	
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