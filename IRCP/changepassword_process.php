<?	
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		$lastpassword = $explode[0];
		$newpassword = $explode[1];			
			
		$pass = sha1($newpassword);		
		
		$sql = "	UPDATE USER
					SET PASSWORD = ?, LAST_MODIFIED = NOW()
					WHERE USER_ID = ?";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($pass, $userid));	
		
		$conn->commit();		
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	
	$conn = null;
?>