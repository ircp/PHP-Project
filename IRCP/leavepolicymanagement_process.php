<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$leavename = trim($explode[0]);
		$allowance = trim($explode[1]);		
		$ordering = trim($explode[2]);	
		$validindcode = trim($explode[3]);	

		if ($ordering == '') {
			$sql = "	SELECT MAX(OREDERING) + 1 MAX_ORDER  FROM LEAVE_POLICY";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute();	

			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$ordering = $arr['MAX_ORDER'];
			}

			if ($ordering == '') {
				$ordering = 1;
			}
		}
		
		if ($allowance == '') {
			$allowance = 0;
		}
		
		if ($mode == 'A') {
			
			$sql = "	INSERT INTO LEAVE_POLICY (CREATED_DATE, LAST_MODIFIED, USER_ID, LEAVE_NAME, ALLOWANCE, VALID_IND_CODE, ORDERING)
						VALUES (NOW(), NOW(), ?, ?, ?, ?, ?)";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $leavename, $allowance, $validindcode, $ordering));							
		}
		else if ($mode == 'C') {
			$sql = "	UPDATE LEAVE_POLICY
						SET	LAST_MODIFIED = NOW(), USER_ID = ?, LEAVE_NAME = ?, ALLOWANCE = ?, VALID_IND_CODE =? , ORDERING =?
						WHERE LEAVE_TYPE_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $leavename, $allowance, $validindcode, $ordering ,$key));	
		}
		else if ($mode == 'D') {
			$sql = "	DELETE FROM LEAVE_POLICY
						WHERE LEAVE_TYPE_ID = ?";

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