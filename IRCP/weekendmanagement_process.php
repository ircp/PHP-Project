<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$weekenddate = trim($explode[0]);
		$description = trim($explode[1]);		
		
		$weekend = ((int)substr($weekenddate,6,4) - 543)."-".substr($weekenddate,3,2)."-".substr($weekenddate,0,2)." 00:00:00";
		
		if ($mode == 'A') {
			$sql = "	INSERT INTO WEEKEND (CREATED_DATE, LAST_MODIFIED, USER_ID, WEEKEND_NAME, WEEKEND_DATE)
						VALUES (NOW(), NOW(), ?, ?, ?)";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $description, $weekend));							
					
		}
		else if ($mode == 'C') {
			$sql = "	UPDATE WEEKEND
						SET	LAST_MODIFIED = NOW(), USER_ID = ?, WEEKEND_NAME = ?, WEEKEND_DATE = ?
						WHERE WEEKEND_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $description, $weekend, $key));	
		}
		else if ($mode == 'D') {
			$sql = "	DELETE FROM WEEKEND
						WHERE WEEKEND_ID = ?";

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