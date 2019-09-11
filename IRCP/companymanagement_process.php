<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$companyname = trim($explode[0]);
		$abbreviation = trim($explode[1]);		
		$description = trim($explode[2]);		
		$companytype = trim($explode[3]);	
		$validindcode = trim($explode[4]);	
		
		if ($mode == 'A') {
			$sql = "	INSERT INTO COMPANY (CREATED_DATE, LAST_MODIFIED, USER_ID, COMPANY_NAME, ABBREVIATION, DESCRIPTION, VALID_IND_CODE, COMPANY_TYPE)
						VALUES (NOW() , NOW() , ?, ?, ?, ?, ?, ?)";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $companyname, $abbreviation, $description, $validindcode, $companytype));							
		}
		else if ($mode == 'C') {
			$sql = "	UPDATE COMPANY
						SET	LAST_MODIFIED = NOW(), USER_ID = ?, COMPANY_NAME = ?, ABBREVIATION = ?, DESCRIPTION = ?, VALID_IND_CODE = ?, COMPANY_TYPE = ?
						WHERE COMPANY_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $companyname, $abbreviation, $description, $validindcode, $companytype ,$key));	
		}
		else if ($mode == 'D') {
			$sql = "	DELETE FROM COMPANY
						WHERE COMPANY_ID = ?";

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