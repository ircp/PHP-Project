<?
	require ('connection.php');
	
	include ('window_parameter.php') ;
	
	try {
		$sql = "	SELECT COUNT(*) COUNTN FROM EMPLOYEE_LEAVE_HISTORY ELH
					LEFT JOIN USER USER ON USER.EMPLOYEE_ID = ELH.APPROVED_ID
					WHERE SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
					AND USER.USER_ID = ? AND ELH.APPROVED_DATE IS NULL";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($userid));			
		
		$approved_count = 0;
		if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$approved_count = $arr['COUNTN'];
		}

		echo($approved_count);
	}	
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;	
?>