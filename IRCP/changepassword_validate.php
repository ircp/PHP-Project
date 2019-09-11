<?	
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		$lastpassword = $explode[0];
		$newpassword = $explode[1];			
			
		$pass = sha1($lastpassword);
		
		$countn = 0;
		
		$sql = "	SELECT COUNT(*) COUNTN
					FROM USER
					WHERE USER_ID = ? AND PASSWORD = ?";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($userid, $pass));

		
		if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$countn = $arr['COUNTN'];
		}

		if ($countn == 0) {
			$error_code = 1016;
			include_once ('window_errormessage.php');
			exit();
		}
		
		$error_code = 0;
		include_once ('window_errormessage.php');
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	
	$conn = null;
?>