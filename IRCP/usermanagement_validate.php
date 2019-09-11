<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$username = trim($explode[0]);
		$firstname = trim($explode[1]);		
		$lastname = trim($explode[2]);		
		$telephone = trim($explode[3]);	
		$email = trim($explode[4]);	
		$groupid = trim($explode[5]);	
		$validindcode = trim($explode[6]);	
		
		if ($mode == 'D') {
			$error_code = 0;
			include ('window_errormessage.php');	
			exit();
		}
		
		if ($username != '') {
			$countn = 0;
			
			if ($mode == 'A') {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM USER
							WHERE USER_NAME = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($username));	
			}
			else {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM USER
							WHERE USER_NAME = ? AND USER_ID <> ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($username, $key));					
			}
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$countn = $arr['COUNTN'];
			}
			
			if ($countn > 0) {
				$error_code = 1006;
				include ('window_errormessage.php');	
				exit();
			}
		}
		
		if ($firstname != '') {
			$countn = 0;
			if ($mode == 'A') {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM USER
							WHERE FIRST_NAME = ? AND LAST_NAME = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($firstname, $lastname));	
			}
			else {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM USER
							WHERE FIRST_NAME = ? AND LAST_NAME = ? AND USER_ID <> ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($firstname, $lastname, $key));					
			}
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$countn = $arr['COUNTN'];
			}
			
			if ($countn > 0) {
				$error_code = 1007;
				include ('window_errormessage.php');	
				exit();
			}
		}
		$error_code = 0;
		include ('window_errormessage.php');	
	}	
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;	
?>