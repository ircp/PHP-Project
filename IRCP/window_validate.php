<?		
	try {
		include ('window_parameter.php');
		
		if ($objectid == 1) {
			if ($menuid == 0) {
				include ('login_validate.php');
			}
			else {
				if ($menuurl == '') {
					echo("");
				}
				else {
					$menuurl = $menuurl."_validate.php";
					include_once($menuurl);
				}				
			}
		}
		if ($objectid == 2) {
			if ($menuid == 0) {
				unset ($RESULT);
				
				$RESULT = array();
				
				$row_array['ERROR_CODE'] = 0;
				$row_array['ERROR_MESSAGE'] =  'No Error';

				array_push($RESULT, $row_array);
				echo(json_encode($RESULT));
			}
		}	
		if ($objectid == 3) {
			if ($menuid == 0) {
				include ('changepassword_validate.php');
			}
			else {
				if ($menuurl == '') {
					echo("");
				}
				else {
					$menuurl = $menuurl."_validate.php";
					include_once($menuurl);
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
					$menuurl = $menuurl."sub_validate.php";
					include_once($menuurl);
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
					$menuurl = $menuurl."subset_validate.php";
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