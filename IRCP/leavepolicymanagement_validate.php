<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$leavename = trim($explode[0]);
		$allowance = trim($explode[1]);		
		$ordering = trim($explode[2]);	
		$validindcode = trim($explode[3]);	
		
		if ($mode == 'D') {
			$error_code = 0;
			include ('window_errormessage.php');	
			exit();
		}
		
		if ($leavename != '') {
			$countn = 0;
			
			if ($mode == 'A') {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM LEAVE_POLICY
							WHERE LEAVE_NAME = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($leavename));	
			}
			else {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM LEAVE_POLICY
							WHERE LEAVE_NAME = ? AND LEAVE_TYPE_ID <> ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($leavename, $key));					
			}
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$countn = $arr['COUNTN'];
			}
			
			if ($countn > 0) {
				$error_code = 1018;
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