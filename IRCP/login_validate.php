<?	
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		$username = $explode[0];
		$password = $explode[1];			
		
		$userid = 0;
		$groupid = 0;
		$companyid = 0;
		
		$sql = "	SELECT USER_ID, GROUP_ID, COMPANY_ID
					FROM USER
					WHERE USER_NAME = ?";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($username));

		if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$userid = $arr['USER_ID'];
			$groupid = $arr['GROUP_ID'];
			$companyid = $arr['COMPANY_ID'];
		}		
		
		
		
		if ($companyid != 1 || $username == 'ADMIN') {	
			$sql = "	SELECT USER_ID, GROUP_ID 
						FROM USER
						WHERE USER_NAME = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($username));

			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$userid = $arr['USER_ID'];
				$groupid = $arr['GROUP_ID'];
			}
			
			if ($userid == 0) {
				$error_code = 1001;
				include ('window_errormessage.php');
				exit();
			}
			
			$userid = 0;
			$groupid = 0;
			
			$pass = sha1($password);
			
			$sql = "	SELECT USER_ID, GROUP_ID, FIRST_NAME, LAST_NAME
						FROM USER
						WHERE USER_NAME = ? AND PASSWORD = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($username, $pass));

			$userid = 0;
			$groupid = 0;
			
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$userid = $arr['USER_ID'];
				$groupid = $arr['GROUP_ID'];
				$name = $arr['FIRST_NAME']." ".$arr['LAST_NAME'];
			}

			if ($userid == 0) {
				$error_code = 1002;
				include ('window_errormessage.php');
				exit();
			}
		}
		else {
			$adserver = "ldap://192.168.1.9";			
			$ldap = ldap_connect($adserver);			
			
			$user = strtolower($username)."@ircp";
			$pass = $password;
			
			$b = ldap_bind($ldap, $user, $pass);
			
			if (!$b) {
				$error_code = 1002;
				include ('window_errormessage.php');
				exit();
			}
			else {
				
				$user = strtoupper($username);

				$sql = "	SELECT USER_ID, GROUP_ID, FIRST_NAME, LAST_NAME
							FROM USER
							WHERE USER_NAME = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($username));

				$userid = 0;
				$groupid = 0;
				
				if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$userid = $arr['USER_ID'];
					$groupid = $arr['GROUP_ID'];
					$name = $arr['FIRST_NAME']." ".$arr['LAST_NAME'];
				}	
			}
		}
		
		if ($userid != 0) {
			$sessionid = '';
			$hdiff = 0;
			$mdiff = 0;
			
			$sql = "	SELECT SESSION_ID, HOUR(TIMEDIFF(NOW(), LAST_LOGIN)) HDIFF,  MINUTE(TIMEDIFF(NOW(), LAST_LOGIN)) MDIFF
						FROM USER_LOGIN
						WHERE USER_ID = ? AND CURRENT_STATUS = 1";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid));
			
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$sessionid = $arr['SESSION_ID'];
				$hdiff = $arr['HDIFF'];
				$mdiff = $arr['MDIFF'];
			}
			
			if ($hdiff == 0 && $mdiff == 0) {
				
			}
			else {
				if ($hdiff >= 1) {
					$sql = "	UPDATE USER_LOGIN
								SET CURRENT_STATUS =  0
								WHERE USER_ID = ? AND CURRENT_STATUS = 1";

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($userid));	
					
					$conn->commit();
				}
				else {
					$error_code = 1003;
					include ('window_errormessage.php');
					exit();
				}
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