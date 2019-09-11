<?
	require ('connection.php');
	
	try {
		
		if ($key == 1) {
			$sql = "	SELECT WEEKEND_ID FROM WEEKEND
						WHERE WEEKEND_DATE = ?";


			$stmt = $conn->prepare($sql);
			$stmt->execute(array($parameter));
			
			$weekendid = 0;
			
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$weekendid = $arr['WEEKEND_ID'];
			}
			
			if ($weekendid != 0) {
				$error_code = 1015;
				include ('window_errormessage.php');	
				
				exit();
			}
		}
		if ($key == 2) {
			
		}
		
		$error_code = 0;
		include ('window_errormessage.php');	
	}	
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;	
?>