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
		$period = 0;
		
		if ($mode == 'A') {
			$sql = "	INSERT INTO SCHEDULE (CREATED_DATE, LAST_MODIFIED, USER_ID, SCHEDULE_NAME, DESCRIPTION, URL, SCHEDULE_TYPE ,VALID_IND_CODE, MAIL, PERIOD)
						VALUES (NOW(), NOW(), ?, ?, ?, ?, ? ,?, ?, ?)";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $schedulename, $description, $scheduleurl, $scheduletype, $validindcode, $schedulemail, $period));							
		}
		else if ($mode == 'C') {
			$sql = "	UPDATE SCHEDULE
						SET	LAST_MODIFIED =  NOW() , USER_ID =? , SCHEDULE_NAME =? , DESCRIPTION =? , URL =? , SCHEDULE_TYPE =?  ,VALID_IND_CODE =? , MAIL = ?, PERIOD = ?
						WHERE SCHEDULE_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $schedulename, $description, $scheduleurl, $scheduletype, $validindcode, $schedulemail, $period, $key));	
		}
		else if ($mode == 'D') {
			$sql = "	DELETE FROM SCHEDULE
						WHERE SCHEDULE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($key));	
			
			$sql = "	DELETE FROM SCHEDULE_MAIL
						WHERE SCHEDULE_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($key));	
		}
		
		$conn->commit();
	}	
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	
	$conn = null;	
?>