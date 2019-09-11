<?	
	require ('connection.php');
	
	try {
		include ('window_parameter.php');
		
		$sql = "	SELECT REFERENCE_CODE, LABEL
					FROM REFERENCE_CODE
					WHERE REFERENCE_TYPE_ID = 1 AND REFERENCE_CODE = ?
					ORDER BY ORDERING";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($error_code));
		
		unset ($RESULT);
		
		$RESULT = array();
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$row_array['ERROR_CODE'] = $arr['REFERENCE_CODE'];
			$row_array['ERROR_MESSAGE'] =  $arr['LABEL'];
			
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