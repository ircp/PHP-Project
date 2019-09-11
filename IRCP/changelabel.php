<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$source = $explode[0];
		$target = $explode[1];
		$value = $explode[2];
		
		$result = '';
		
		if ($menuid == 5 && $source == 'objecttype' && $target == 'object_display') {			
			$sql = "	SELECT LABEL, REFERENCE_CODE 
						FROM REFERENCE_CODE
						WHERE REFERENCE_TYPE_ID = 9 AND VALID_IND_CODE = 1 AND REFERENCE_CODE = ?
						ORDER BY ORDERING, LABEL";
			
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($value));	

			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$result = "ชื่อ".$arr['LABEL'];
			}
		}
		
		if ($menuid == 3 && $source == 'objecttype' && $target == 'parentmenu_label') {
			$result = 'Parent';
		}
		
		if ($menuid == 3 && $source == 'objecttype' && $target == 'menu_name') {
			$sql = "	SELECT LABEL, REFERENCE_CODE 
						FROM REFERENCE_CODE
						WHERE REFERENCE_TYPE_ID = 9 AND VALID_IND_CODE = 1 AND REFERENCE_CODE = ?
						ORDER BY ORDERING, LABEL";
			
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($value));	

			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$result = "ชื่อ".$arr['LABEL'];
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