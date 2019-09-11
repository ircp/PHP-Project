<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$label = trim($explode[0]);
		$abbreviation = trim($explode[1]);		
		$description = trim($explode[2]);		
		
		if ($mode == 'D') {
			$error_code = 0;
			include_once ('window_errormessage.php');
			exit();
		}
		
		if ($label != '') {
			$countn = 0;
			
			if ($mode == 'A') {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM REFERENCE_TYPE
							WHERE LABEL = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($label));	
			}
			else {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM REFERENCE_TYPE
							WHERE LABEL = ? AND REFERENCE_TYPE_ID <> ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($label, $key));					
			}
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$countn = $arr['COUNTN'];
			}
			
			if ($countn > 0) {
				$error_code = 1010;
				include_once ('window_errormessage.php');
				exit();
			}
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