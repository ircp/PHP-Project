<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$weekenddate = trim($explode[0]);	
		$description = trim($explode[1]);		
		
		if ($mode == 'D') {
			$error_code = 0;
			include ('window_errormessage.php');	
			exit();
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