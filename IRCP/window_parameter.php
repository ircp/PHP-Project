<?
	$menuid = '';
	$objectid = '';
	$mode = '';
	$key = '';
	$parameter = '';
	
	if (isset($_POST['menuid'])) {
		$menuid = $_POST['menuid'];
	}
	if (isset($_POST['objectid'])) {
		$objectid = $_POST['objectid'];
	}
	if (isset($_POST['mode'])) {
		$mode = $_POST['mode'];
	}
	if (isset($_POST['key'])) {
		$key = $_POST['key'];
	}
	if (isset($_POST['parameter'])) {
		$parameter = $_POST['parameter'];
	}
	
	if (session_id() == '') {
		session_start();
	}
	
	if ($menuid != 0) {
		$_SESSION[session_id()]['CURRENT_MENU'] = $menuid;
		$menuurl = $_SESSION[session_id()]['MENU'][$menuid]['MENU_URL']; 
	}
	
	if (!empty($_SESSION[session_id()]['USER'])) {
		$userid = $_SESSION[session_id()]['USER'];
	}
	else {
		$userid = '';
	}
	if (!empty($_SESSION[session_id()]['GROUP'])) {
		$group = $_SESSION[session_id()]['GROUP'];
	}
	else {
		$group = '';
	}
?>