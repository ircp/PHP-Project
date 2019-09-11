<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		$menuname = trim($explode[0]);
		$menuurl = trim($explode[1]);		
		$menuimage = trim($explode[2]);		
		$menudescription = trim($explode[3]);		
		$objecttype =  trim($explode[4]);
		
		if ($mode == 'D') {
			$error_code = 0;
			include_once ('window_errormessage.php');
			exit();
		}
		
		if ($menuurl != '') {
			$countn = 0;
			
			if ($mode == 'A') {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM MENU
							WHERE MENU_URL = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($menuurl));	
			}
			else {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM MENU
							WHERE MENU_URL = ? AND MENU_ID <> ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($menuurl, $key));					
			}
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$countn = $arr['COUNTN'];
			}
			
			if ($countn > 0) {
				$error_code = 1004;
				include_once ('window_errormessage.php');
				exit();
			}
			
			$countn = 0;
			if ($objecttype == 2 || $objecttype == 3) {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM GROUP_MENU 
							WHERE MENU_ID = ? AND MENU_LEVEL = 1";
				$stmt = $conn->prepare($sql);
				$stmt->execute(array($key));	

				if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$countn = $arr['COUNTN'];
				}

				if ($countn > 0) {
					$error_code = 1013;
					include_once ('window_errormessage.php');
					exit();
				}				
			}
		}
		
		if ($menuname != '') {
			$countn = 0;
			if ($mode == 'A') {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM MENU
							WHERE MENU_NAME = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($menuname));	
			}
			else {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM MENU
							WHERE MENU_NAME = ? AND MENU_ID <> ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($menuname, $key));					
			}
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$countn = $arr['COUNTN'];
			}
			
			if ($countn > 0) {
				$error_code = 1005;
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