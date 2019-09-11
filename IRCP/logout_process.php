<?	
	require ('connection.php');
	
	try {		
		$sql = "	UPDATE USER_LOGIN
					SET CURRENT_STATUS =  0
					WHERE USER_ID = ? AND CURRENT_STATUS = 1";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($userid));		
		
		UNSET ($_SESSION[session_id()]['USER']);
		UNSET ($_SESSION[session_id()]['GROUP']);
		UNSET ($_SESSION[session_id()]['USER_NAME']);
		
		$conn->commit();		
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	
	$conn = null;
?>