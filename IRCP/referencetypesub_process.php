<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$referencecode = trim($explode[0]);
		$label = trim($explode[1]);
		$abbreviation = trim($explode[2]);	
		$ordering = trim($explode[3]);		
		$validindcode = trim($explode[4]);
		$description = trim($explode[5]);	
		
		$explode = explode("|", $key);
		
		$referencetypeid = $explode[0];
		
		
		if ($ordering == '') {
			$sql = "	SELECT MAX(OREDERING) + 1 MAX_ORDER  FROM REFERENCE_CODE
						WHERE REFERENCE_TYPE_ID = ?";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($referencetypeid));	

			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$ordering = $arr['MAX_ORDER'];
			}

			if ($ordering == '') {
				$ordering = 1;
			}
		}
		
		
		if ($mode == 'A') {
			$sql = "	INSERT INTO REFERENCE_CODE (REFERENCE_TYPE_ID, REFERENCE_CODE, CREATED_DATE, LAST_MODIFIED, USER_ID, LABEL, ABBREVIATION, DESCRIPTION, ORDERING, VALID_IND_CODE)
						VALUES (?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?)";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($referencetypeid, $referencecode, $userid, $label, $abbreviation, $description, $ordering, $validindcode));							
		}
		else if ($mode == 'C') {
			$sql = "	UPDATE REFERENCE_CODE
						SET	LAST_MODIFIED = NOW(), USER_ID = ?, LABEL = ?, ABBREVIATION = ?, DESCRIPTION = ?, ORDERING = ?, VALID_IND_CODE = ?
						WHERE REFERENCE_TYPE_ID = ? AND REFERENCE_CODE = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $label, $abbreviation, $description, $ordering, $validindcode, $referencetypeid, $referencecode));	
		}
		else if ($mode == 'D') {
			$sql = "	DELETE FROM REFERENCE_CODE
						WHERE REFERENCE_TYPE_ID = ? AND REFERENCE_CODE = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($referencetypeid, $referencecode));	
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