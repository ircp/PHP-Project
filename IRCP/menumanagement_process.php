<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$menuname = trim($explode[0]);
		$menuurl = trim($explode[1]);
		$menuimage = trim($explode[2]);
		$menudescription = trim($explode[3]);
		$objecttype =  trim($explode[4]);
		
		
		if ($mode == 'A') {
			$sql = "	INSERT INTO MENU (CREATED_DATE, LAST_MODIFIED, USER_ID, MENU_NAME, MENU_DESCRIPTION, MENU_URL, MENU_IMAGE, OBJECT_TYPE)
						VALUES (NOW() , NOW() , ?, ?, ?, ?, ?, ?)";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $menuname, $menudescription, $menuurl, $menuimage, $objecttype));							
		}
		else if ($mode == 'C') {
			$sql = "	UPDATE MENU
						SET	MENU_NAME = ?, MENU_DESCRIPTION = ?, MENU_URL = ? , MENU_IMAGE = ?, LAST_MODIFIED = NOW(), USER_ID = ?, OBJECT_TYPE = ?
						WHERE MENU_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($menuname, $menudescription, $menuurl, $menuimage, $userid, $objecttype ,$key));	
		}
		else if ($mode == 'D') {
			$sql = "	DELETE FROM MENU
						WHERE MENU_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($key));	

			$sql = "	DELETE FROM GROUP_MENU
						WHERE MENU_ID = ?";
						
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