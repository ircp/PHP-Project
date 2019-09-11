<?
	require ('connection.php');
	
	try {
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