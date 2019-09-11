<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$source = $explode[0];
		$target = $explode[1];
		$value = $explode[2];
		
		$result = '';
		
		if ($menuid == 4 && $source == 'companyid' && $target == 'groupid') {
			$companyid = $value;

			$sql = "	SELECT GROUP_ID, GROUP_NAME 
						FROM GROUP_USER 
						WHERE VALID_IND_CODE = 1 AND COMPANY_ID = ?
						ORDER BY GROUP_NAME";
			
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($companyid));	

			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$result = $result."<option value = '".$arr['GROUP_ID']."' >".$arr['GROUP_NAME']."</option>";
			}			
		}
		
		if ($menuid ==3) {
			$explode = explode("|", $key);
			$groupid = $explode[0];
			$menu = $explode[1];
			
			if ($source == 'objecttype' && $target == 'parentmenuid') {
				if ($value == 1) {
					$result = "<option value = '' ></option>";
				}
				else if ($value == 2) {
					$sql = "	SELECT MENU_ID, MENU_NAME 
								FROM MENU
								WHERE MENU_ID IN (SELECT MENU_ID FROM GROUP_MENU WHERE GROUP_ID = ? AND MENU_URL <> '' AND MENU_LEVEL IN (1,2)) 
								ORDER BY MENU_NAME";
					
					$stmt = $conn->prepare($sql);
					$stmt->execute(array($groupid));	

					while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$result = $result."<option value = '".$arr['MENU_ID']."' >".$arr['MENU_NAME']."</option>";
					}					
				}
				else if ($value == 3) {
					$sql = "	SELECT MENU_ID, MENU_NAME 
								FROM MENU
								WHERE MENU_ID IN (SELECT MENU_ID FROM GROUP_MENU WHERE GROUP_ID = ? AND MENU_URL <> '') 
								ORDER BY MENU_NAME";
					
					$stmt = $conn->prepare($sql);
					$stmt->execute(array($groupid, $menu));	

					while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$result = $result."<option value = '".$arr['MENU_ID']."' >".$arr['MENU_NAME']."</option>";
					}										
				}
			}
			if ($source == 'menulevel' && $target == 'parentmenuid') {
				if ($value == 1) {
					$result = "<option value = '' ></option>";
				}
				else if ($value == 2) {
					$sql = "	SELECT MENU_ID, MENU_NAME 
								FROM MENU
								WHERE MENU_ID IN (SELECT MENU_ID FROM GROUP_MENU WHERE GROUP_ID = ? AND MENU_LEVEL = 1)
								ORDER BY MENU_NAME";
					
					$stmt = $conn->prepare($sql);
					$stmt->execute(array($groupid));	

					while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$result = $result."<option value = '".$arr['MENU_ID']."' >".$arr['MENU_NAME']."</option>";
					}					
				}				
			}
			if ($source == 'objecttype' && $target == 'menuid') {
				if ($value == 1) {
					$sql = "	SELECT MENU_ID, MENU_NAME 
								FROM MENU
								WHERE (MENU_ID NOT IN (SELECT MENU_ID FROM GROUP_MENU WHERE GROUP_ID = ? ) OR MENU_ID = ?)AND OBJECT_TYPE = 1 
								ORDER BY MENU_NAME";
					
					$stmt = $conn->prepare($sql);
					$stmt->execute(array($groupid, $menu));	

					while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
						if ($arr['MENU_ID'] == $menu) {
							$result = $result."<option value = '".$arr['MENU_ID']."' selected>".$arr['MENU_NAME']."</option>";
						}
						else {
							$result = $result."<option value = '".$arr['MENU_ID']."' >".$arr['MENU_NAME']."</option>";
						}
					}	
				}
				else if ($value == 2) {
					$sql = "	SELECT MENU_ID, MENU_NAME 
								FROM MENU
								WHERE (MENU_ID NOT IN (SELECT MENU_ID FROM GROUP_MENU WHERE GROUP_ID = ? AND MENU_LEVEL = 3) OR MENU_ID = ?) AND OBJECT_TYPE = 2
								ORDER BY MENU_NAME";
					
					$stmt = $conn->prepare($sql);
					$stmt->execute(array($groupid, $menu));	

					while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
						if ($arr['MENU_ID'] == $menu) {
							$result = $result."<option value = '".$arr['MENU_ID']."' selected>".$arr['MENU_NAME']."</option>";
						}
						else {
							$result = $result."<option value = '".$arr['MENU_ID']."' >".$arr['MENU_NAME']."</option>";
						}
					}					
				}				
			}
			if ($source == 'menulevel' && $target == 'menuid') {
				$sql = "	SELECT MENU_ID, MENU_NAME 
							FROM MENU
							WHERE (MENU_ID NOT IN (SELECT MENU_ID FROM GROUP_MENU WHERE GROUP_ID = ? ) OR MENU_ID = ?)AND OBJECT_TYPE = 1 
							ORDER BY MENU_NAME";
				
				$stmt = $conn->prepare($sql);
				$stmt->execute(array($groupid, $menu));	

				while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
						if ($arr['MENU_ID'] == $menu) {
							$result = $result."<option value = '".$arr['MENU_ID']."' selected>".$arr['MENU_NAME']."</option>";
						}
						else {
							$result = $result."<option value = '".$arr['MENU_ID']."' >".$arr['MENU_NAME']."</option>";
						}
				}					
			}
		}
		
		echo($result);
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;
?>