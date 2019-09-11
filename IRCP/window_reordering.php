<?
	try {
		include ('window_parameter.php');
		
		if ($objectid == 0) {
			
		}
		else if ($objectid == 1) {

		}
		else if ($objectid == 3) {

		}
		else if ($objectid == 4) {
			if ($menuid == 0) {
				
			}
			else {
				if ($menuurl == '') {
					echo("");
				}
				else {
					$menuurl = $menuurl."sub_reordering.php";
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
?>