<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$menulevel = trim($explode[0]);
		$parentmenuid = trim($explode[1]);		
		$menu = trim($explode[2]);		
		$menuorder = trim($explode[3]);	
		$validindcode = trim($explode[4]);	
		$firstmenu = trim($explode[5]);
		$add = trim($explode[6]);
		$update = trim($explode[7]) * 2;
		$delete = trim($explode[8]) * 3;
		$view = trim($explode[9]) * 4;
		$objecttype = trim($explode[10]);
		
		$explode = explode("|", $key);
		
		$groupid = trim($explode[0]);
		
		if ($parentmenuid == '') {
			$parentmenuid = 0;
		}
		
		if ($menuorder == '') {
			$sql = "	SELECT MAX(MENU_ORDER) + 1 MAX_ORDER  FROM GROUP_MENU
						WHERE PARENT_MENU_ID = ? AND GROUP_ID = ?";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($parentmenuid, $groupid));	

			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$menuorder = $arr['MAX_ORDER'];
			}

			if ($menuorder == '') {
				$menuorder = 1;
			}
		}
		
		if ($objecttype == 1) {
			
		}
		else if ($objecttype == 2) {
			$menulevel = 3;
		}
		else if ($objecttype == 3) {
			$menulevel = 4;
		}		
		
		$permission = 0;
		if ($add > 0) {
			$permission = $permission + pow(2,$add);
		}
		if ($update > 0) {
			$permission = $permission + pow(2,$update);
		}
		if ($delete > 0) {
			$permission = $permission + pow(2,$delete);
		}
		if ($view > 0) {
			$permission = $permission + pow(2,$view);
		}
		
		if ($mode == 'A') {
			$sql = "	INSERT INTO GROUP_MENU (GROUP_ID, MENU_ID, CREATED_DATE, LAST_MODIFIED, USER_ID, PERMISSION, FIRST_MENU, MENU_LEVEL, PARENT_MENU_ID, VALID_IND_CODE, MENU_ORDER)
						VALUES (?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?)";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($groupid, $menu, $userid, $permission, $firstmenu, $menulevel, $parentmenuid, $validindcode, $menuorder));							
		}
		else if ($mode == 'C') {
			$sql = "	UPDATE GROUP_MENU
						SET	LAST_MODIFIED = NOW(), USER_ID = ?, PERMISSION = ?, FIRST_MENU = ?, MENU_LEVEL = ?, PARENT_MENU_ID = ?, VALID_IND_CODE = ?, MENU_ORDER = ?
						WHERE GROUP_ID = ? AND MENU_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $permission, $firstmenu, $menulevel, $parentmenuid, $validindcode, $menuorder,$groupid, $menu));	
		}
		else if ($mode == 'D') {
			$sql = "	DELETE FROM GROUP_MENU
						WHERE GROUP_ID = ? AND MENU_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($groupid, $menu));	
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