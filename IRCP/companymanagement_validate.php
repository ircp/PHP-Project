<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$companyname = trim($explode[0]);
		$abbreviation = trim($explode[1]);		
		$description = trim($explode[2]);		
		$companytype = trim($explode[3]);	
		$validindcode = trim($explode[4]);	
		
		if ($mode == 'D') {
			$error_code = 0;
			include ('window_errormessage.php');	
			exit();
		}
		
		if ($companyname != '') {
			$countn = 0;
			
			if ($mode == 'A') {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM COMPANY
							WHERE COMPANY_NAME = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($companyname));	
			}
			else {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM COMPANY
							WHERE COMPANY_NAME = ? AND COMPANY_ID <> ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($companyname, $key));					
			}
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$countn = $arr['COUNTN'];
			}
			
			if ($countn > 0) {
				$error_code = 1013;
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