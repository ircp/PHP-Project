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
					$menuurl = $menuurl.".php";
					include_once($menuurl);
				}					
			}
		}
		else if ($objectid == 2) {
			if ($menuid == 0) {
				
			}
			else {
				if ($menuurl == '') {
					echo("");
				}
				else {
					$menuurl = $menuurl."_list.php";
					include_once($menuurl);
				}		
			}
		}
		else if ($objectid == 3) {
			if ($menuid == 0) {
				include ('changepassword_get.php');
			}
			else {
				if ($menuurl == '') {
					echo("");
				}
				else {
					$menuurl = $menuurl."_get.php";
					include_once($menuurl);
				}					
			}
		}
		else if ($objectid == 4) {
			if ($menuid == 0) {
				
			}
			else {
				if ($menuurl == '') {
					echo("");
				}
				else {
					$menuurl = $menuurl."_sub.php";
					include_once($menuurl);
				}					
			}
		}
		else if ($objectid == 5) {
			if ($menuid == 0) {
				
			}
			else {
				if ($menuurl == '') {
					echo("");
				}
				else {
					$menuurl = $menuurl."sub_get.php";
					include_once($menuurl);
				}					
			}
		}
		else if ($objectid == 6) {
			include_once('showdropdown.php');
		}	
		else if ($objectid == 7) {
			include_once('changelabel.php');
		}
		else if ($objectid == 8) {
			if ($menuid == 0) {
				
			}
			else {
				if ($menuurl == '') {
					echo("");
				}
				else {
					$menuurl = $menuurl."subset_get.php";
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