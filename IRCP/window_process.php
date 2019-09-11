<?		
	try {
		include ('window_parameter.php');
		
		if ($objectid == 1) {
			if ($menuid == 0) {
				include ('login_process.php');
			}
			else {
				if ($menuurl == '') {
					echo("");
				}
				else {
					$menuurl = $menuurl."_process.php";
					include($menuurl);
				}				
			}
		}
		if ($objectid == 2) {
			if ($menuid == 0) {
				include ('logout_process.php');
			}
			else {
				if ($menuurl == '') {
					echo("");
				}
				else {
					$menuurl = $menuurl."_process.php";
					include($menuurl);
				}					
			}			
		}
		if ($objectid == 3) {
			if ($menuid == 0) {
				
			}
			else {
				if ($menuurl == '') {
					echo("");
				}
				else {
					$menuurl = $menuurl."_process.php";
					include($menuurl);
				}					
			}
		}
		if ($objectid == 4) {
			if ($menuid == 0) {
				
			}
			else {
				if ($menuurl == '') {
					echo("");
				}
				else {
					$menuurl = $menuurl."sub_process.php";
					include($menuurl);
				}					
			}
		}
		if ($objectid == 6) {
			if ($menuid == 0) {
				
			}
			else {
				if ($menuurl == '') {
					echo("");
				}
				else {
					$menuurl = $menuurl."subset_process.php";
					include($menuurl);
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