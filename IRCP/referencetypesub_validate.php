<?
	require ('connection.php');
	
	try {		

		$explode = explode("|", $key);
		$referencetypeid = $explode[0];
		$referencecode = $explode[1];

		$explode = explode("|", $parameter);
		
		$referencecode = trim($explode[0]);
		$label = trim($explode[1]);
		$abbreviation = trim($explode[2]);	
		$ordering = trim($explode[3]);		
		$validindcode = trim($explode[4]);
		$description = trim($explode[5]);	
		
		if ($mode == 'D') {
			$error_code = 0;
			include_once ('window_errormessage.php');
			exit();
		}
		
		if ($referencecode != '') {
			$countn = 0;
			
			if ($mode == 'A') {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM REFERENCE_CODE
							WHERE REFERENCE_CODE = ? AND REFERENCE_TYPE_ID = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($referencecode, $referencetypeid));	

				if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$countn = $arr['COUNTN'];
				}					
	
				if ($countn > 0) {
					$error_code = 1011;
					include_once ('window_errormessage.php');
					exit();
				}		
			}
			else {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM REFERENCE_CODE
							WHERE REFERENCE_TYPE_ID = ? AND REFERENCE_CODE = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($referencetypeid, $referencecode));	

				if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$countn = $arr['COUNTN'];
				}					
	
				if ($countn > 1) {
					$error_code = 1011;
					include_once ('window_errormessage.php');
					exit();
				}				
			}
		}
		
		if ($label != '') {
			$countn = 0;
			
			if ($mode == 'A') {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM REFERENCE_CODE
							WHERE LABEL = ? AND REFERENCE_TYPE_ID = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($label, $referencetypeid));	

				if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$countn = $arr['COUNTN'];
				}					
	
				if ($countn > 0) {
					$error_code = 1012;
					include_once ('window_errormessage.php');
					exit();
				}		
			}
			else {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM REFERENCE_CODE
							WHERE LABEL = ? AND REFERENCE_TYPE_ID = ? AND REFERENCE_CODE <> ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($label, $referencetypeid, $referencecode));	

				if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$countn = $arr['COUNTN'];
				}					
	
				if ($countn > 0) {
					$error_code = 1012;
					include_once ('window_errormessage.php');
					exit();
				}				
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