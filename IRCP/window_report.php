<?	
	require ('connection.php');
	
	try {
		include ('window_parameter.php');
		
		if ($objectid == 1) {
			if ($menuid == 0) {
				
			}
			else {
				if ($menuurl == '') {
					echo("");
				}
				else {
					$menuurl = $menuurl."_report.php";
					include_once($menuurl);
				}					
			}
		}			
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	
	$conn = null;
?>