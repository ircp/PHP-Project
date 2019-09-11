<?	
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		$username = $explode[0];
		$password = $explode[1];			
		
		$pass = sha1($password);

		$userid = 0;
		$groupid = 0;
		$companyid = 0;
		
		$sql = "	SELECT USER_ID, GROUP_ID, COMPANY_ID, FIRST_NAME, LAST_NAME
					FROM USER
					WHERE USER_NAME = ?";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($username));

		if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$userid = $arr['USER_ID'];
			$groupid = $arr['GROUP_ID'];
			$companyid = $arr['COMPANY_ID'];
			$name = $arr['FIRST_NAME']." ".$arr['LAST_NAME'];
		}	
		
		
		echo($userid);

		$sql = "	UPDATE USER_LOGIN
					SET CURRENT_STATUS =  0
					WHERE USER_ID = ? AND CURRENT_STATUS = 1";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($userid));		
		
		if (session_id() == '') {
			session_start();
		}
			
		$sql = "	INSERT INTO USER_LOGIN
					VALUES (?, NOW(), ?, 1)";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($userid, session_id()));
		
		$_SESSION[session_id()]['USER'] = $userid;
		$_SESSION[session_id()]['GROUP'] = $groupid;
		$_SESSION[session_id()]['USER_NAME'] = $name;
		
		$conn->commit();		
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	
	$conn = null;
?>