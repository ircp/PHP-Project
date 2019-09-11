<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$departmentname = trim($explode[0]);
		$abbreviation = trim($explode[1]);		
		$description = trim($explode[2]);		
		$validindcode = trim($explode[3]);	
		
		if ($mode == 'D') {
			$error_code = 0;
			include ('window_errormessage.php');	
			exit();
		}
		
		if ($departmentname != '') {
			$countn = 0;
			
			if ($mode == 'A') {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM DEPARTMENT
							WHERE DEPARTMENT_NAME = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($departmentname));	
			}
			else {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM DEPARTMENT
							WHERE DEPARTMENT_NAME = ? AND DEPARTMENT_ID <> ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($departmentname, $key));					
			}
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$countn = $arr['COUNTN'];
			}
			
			if ($countn > 0) {
				$error_code = 1014;
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