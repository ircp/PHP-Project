<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$username = trim($explode[0]);
		$firstname = trim($explode[1]);		
		$lastname = trim($explode[2]);		
		$telephone = trim($explode[3]);	
		$email = trim($explode[4]);	
		$groupid = trim($explode[5]);	
		$validindcode = trim($explode[6]);	
		$companyid = trim($explode[7]);
		
		if ($mode == 'A') {
			$password = sha1('IRCP2018');
			
			$sql = "	INSERT INTO USER (CREATED_DATE, LAST_MODIFIED, USER_NAME, PASSWORD, FIRST_NAME, LAST_NAME, TELEPHONE, EMAIL, VALID_IND_CODE, GROUP_ID, COMPANY_ID)
						VALUES (NOW() , NOW() , ?, ?, ?, ?, ?, ?, ?, ?, ?)";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($username, $password, $firstname, $lastname, $telephone, $email, $validindcode, $groupid, $companyid));							
		}
		else if ($mode == 'C') {
			$sql = "	UPDATE USER
						SET	LAST_MODIFIED = NOW(), USER_NAME = ?, FIRST_NAME = ?, LAST_NAME = ?, TELEPHONE = ?, EMAIL = ?, VALID_IND_CODE = ?, GROUP_ID = ?, COMPANY_ID = ?
						WHERE USER_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($username, $firstname, $lastname, $telephone, $email, $validindcode, $groupid, $companyid ,$key));	
		}
		else if ($mode == 'D') {
			$sql = "	DELETE FROM USER
						WHERE USER_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($key));	
		}
		else if ($mode == 'R') {
			if ($key == 1) {
				$generated_string = 'MYIRCP2018';
			}
			else {
				$generated_string = '';
				$domain = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
				$len = strlen($domain);
				
				for ($i = 0; $i < 5; $i++) {
					$index = rand(0, $len - 1);
					$generated_string = $generated_string . $domain[$index]; 
				}
				$random_number = rand();
				
				if ($random_number < 10) {
					$random_number = $random_number + 5;
				}
				else {
					$random_number = substr($random_number, 0, 2);
				}
				
				$generated_string = $generated_string."".$random_number;
			}
			$password = sha1($generated_string);
			$sql = "	UPDATE USER
						SET PASSWORD = ?, LAST_MODIFIED = NOW()
						WHERE USER_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($password, $key));	

			echo($generated_string);				
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