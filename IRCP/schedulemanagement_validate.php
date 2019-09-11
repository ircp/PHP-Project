<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$schedulename = trim($explode[0]);
		$scheduleurl = trim($explode[1]);	
		$scheduletype = trim($explode[2]);	
		$schedulemail = trim($explode[3]);	
		$validindcode = trim($explode[4]);	
		$description = trim($explode[5]);	
//		$period = trim($explode[6]);	
		
		if ($mode == 'D') {
			$error_code = 0;
			include ('window_errormessage.php');	
			exit();
		}
		
		if ($schedulename != '') {
			$countn = 0;
			
			if ($mode == 'A') {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM SCHEDULE
							WHERE SCHEDULE_NAME = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($schedulename));	
			}
			else {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM SCHEDULE
							WHERE SCHEDULE_NAME = ? AND SCHEDULE_ID <> ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($schedulename, $key));					
			}
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$countn = $arr['COUNTN'];
			}
			
			if ($countn > 0) {
				$error_code = 1019;
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