<?	
	require ('connection.php');
	
	try {
		include ('window_parameter.php');
		
		$sql = "	SELECT FIELD_NAME, VALIDATE, TYPE, RELATIVE
					FROM WINDOW_FIELD
					WHERE OBJECT_ID = ? AND MENU_ID = ?
					ORDER BY ORDERING";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($objectid, $menuid));
		
		$i = 0;
		
		unset ($RESULT);
		
		$RESULT = array();
		
		$i = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i + 1;
			$row_array['FIELD_NAME'] = $arr['FIELD_NAME'];
			$row_array['VALIDATE'] =  $arr['VALIDATE'];
			$row_array['TYPE'] =  $arr['TYPE'];
			$row_array['RELATIVE'] =  $arr['RELATIVE'];
			
			array_push($RESULT, $row_array);
		}
		
		echo(json_encode($RESULT));
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	
	$conn = null;
?>