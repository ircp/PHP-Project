<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$groupname = trim($explode[0]);
		$groupabbreviation = trim($explode[1]);		
		$groupdescription = trim($explode[2]);		
		$validindcode = trim($explode[3]);	
		
		if ($mode == 'D') {
			$error_code = 0;
			include ('window_errormessage.php');	
			exit();
		}
		
		if ($groupname != '') {
			$countn = 0;
			
			if ($mode == 'A') {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM GROUP_USER
							WHERE GROUP_NAME = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($groupname));	
			}
			else {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM GROUP_USER
							WHERE GROUP_NAME = ? AND GROUP_ID <> ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($groupname, $key));					
			}
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$countn = $arr['COUNTN'];
			}
			
			if ($countn > 0) {
				$error_code = 1008;
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