<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$groupname = trim($explode[0]);
		$groupabbreviation = trim($explode[1]);		
		$groupdescription = trim($explode[2]);		
		$validindcode = trim($explode[3]);	
		$companyid = trim($explode[4]);
		
		if ($mode == 'A') {
			$sql = "	INSERT INTO GROUP_USER (CREATED_DATE, LAST_MODIFIED, USER_ID, GROUP_NAME, GROUP_ABBREVIATION, GROUP_DESCRIPTION, VALID_IND_CODE, COMPANY_ID)
						VALUES (NOW() , NOW() , ?, ?, ?, ?, ?, ?)";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $groupname, $groupabbreviation, $groupdescription, $validindcode, $companyid));							
		}
		else if ($mode == 'C') {
			$sql = "	UPDATE GROUP_USER
						SET	LAST_MODIFIED = NOW(), USER_ID = ?, GROUP_NAME = ?, GROUP_ABBREVIATION = ?, GROUP_DESCRIPTION = ?, VALID_IND_CODE = ?, COMPANY_ID = ?
						WHERE GROUP_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $groupname, $groupabbreviation, $groupdescription, $validindcode, $companyid, $key));	
		}
		else if ($mode == 'D') {
			$sql = "	DELETE FROM GROUP_USER
						WHERE GROUP_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($key));	
			
			$sql = "	DELETE FROM GROUP_MENU
						WHERE GROUP_ID = ?";
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