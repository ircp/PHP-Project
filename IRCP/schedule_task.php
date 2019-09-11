<?
	require ('connection.php');
	
	try {
		$scheduletype = $argv[1];
		
		$sql = "	INSERT INTO TASK_SCHEDULE (CREATED_DATE, LAST_MODIFIED, USER_ID, SCHEDULE_ID, TASK_STATUS)
					SELECT NOW(), NOW(), 0, SCHEDULE_ID, 1
					FROM SCHEDULE
					WHERE SCHEDULE_TYPE = ? AND VALID_IND_CODE = 1";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($scheduletype));
		
		$conn->commit();
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;
?>