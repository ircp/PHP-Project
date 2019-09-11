<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$employeecode = trim($explode[0]);
		$firstname = trim($explode[1]);		
		$lastname = trim($explode[2]);		
		$employeestatus = trim($explode[3]);	
		$title = trim($explode[4]);	
		$firstname_en = trim($explode[5]);
		$lastname_en = trim($explode[6]);
		$nickname = trim($explode[7]);
		$departmentid = trim($explode[8]);
		$positionid = trim($explode[9]);
		$startdate = trim($explode[10]);
		$enddate = trim($explode[11]);
		$email = trim($explode[12]);
		$cumulativevacation = trim($explode[13]);
		$emp_file = trim($explode[14]);
		$groupid = trim($explode[15]);
		
		if ($mode == 'D') {
			$error_code = 0;
			include ('window_errormessage.php');
			exit();
		}
		
		if ($email != '') {
			if ($mode == 'A') {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM EMPLOYEE
							WHERE EMAIL = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($email));						
			}
			else {
				$sql = "	SELECT COUNT(*) COUNTN
							FROM EMPLOYEE
							WHERE EMAIL = ? AND EMPLOYEE_ID <> ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($email, $key));						
			}
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$countn = $arr['COUNTN'];
			}
			
			if ($countn > 0) {
				$error_code = 1015;
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