<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$label = trim($explode[0]);
		$abbreviation = trim($explode[1]);		
		$description = trim($explode[2]);		
		
		if ($mode == 'A') {
			$sql = "	INSERT INTO REFERENCE_TYPE (CREATED_DATE, LAST_MODIFIED, USER_ID, LABEL, ABBREVIATION, DESCRIPTION)
						VALUES (NOW() , NOW() , ?, ?, ?, ?)";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $label, $abbreviation, $description));	
		}
		else if ($mode == 'C') {
			$sql = "	UPDATE REFERENCE_TYPE
						SET	LAST_MODIFIED = NOW(), USER_ID = ?, LABEL = ?, ABBREVIATION = ?, DESCRIPTION = ?
						WHERE REFERENCE_TYPE_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $label, $abbreviation, $description, $key));	
		}
		else if ($mode == 'D') {
			$sql = "	DELETE FROM REFERENCE_TYPE
						WHERE REFERENCE_TYPE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($key));	
			
			$sql = "	DELETE FROM REFERENCE_CODE
						WHERE REFERENCE_TYPE_ID = ?";
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