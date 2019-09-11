<?
	require ('connection.php');
	
	try {
		if (session_id() == '') {
			session_start();
		}	
		
		$groupid = $_SESSION[session_id()]['GROUP'];

		$found = 0;		
		unset($MENU);
		$MENU = array();
		
		$sql = "	SELECT GM.PERMISSION, GM.FIRST_MENU, GM.MENU_LEVEL, GM.MENU_ORDER, GM.PARENT_MENU_ID,
					ME.MENU_NAME, ME.MENU_URL, ME.MENU_ID, ME.MENU_IMAGE
					FROM GROUP_MENU GM
					LEFT JOIN MENU ME ON ME.MENU_ID = GM.MENU_ID
					WHERE GM.GROUP_ID = ? AND VALID_IND_CODE =1 AND GM.MENU_LEVEL <= 2 AND ME.OBJECT_TYPE = 1
					ORDER BY GM.MENU_LEVEL, GM.MENU_ORDER, ME.MENU_NAME";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($groupid));			
		
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$menuid = $result['MENU_ID'];
			$permission = $result['PERMISSION'];
			
			$MENU[$menuid]['FIRST_MENU'] = $result['FIRST_MENU'];
			$MENU[$menuid]['MENU_NAME'] = $result['MENU_NAME'];
			$MENU[$menuid]['MENU_URL'] = $result['MENU_URL'];
			$MENU[$menuid]['PARENT_MENU_ID'] =  $result['PARENT_MENU_ID'];
			$MENU[$menuid]['MENU_LEVEL'] = $result['MENU_LEVEL'];
			$MENU[$menuid]['MENU_ORDER'] = $result['MENU_ORDER'];
			$MENU[$menuid]['MENU_IMAGE'] = $result['MENU_IMAGE'];

			$viewflag = 0;
			$updateflag = 0;
			$deleteflag = 0;
			$addflag = 0;

			if ($permission >= (pow(2,4))) {
				$viewflag = 1;
				$permission = $permission - (pow(2,4));
			}
			if ($permission >= (pow(2,3))) {
				$updateflag = 1;
				$permission = $permission - (pow(2,3));
			}
			if ($permission >= (pow(2,2))) {
				$deleteflag = 1;
				$permission = $permission - (pow(2,2));
			}
			if ($permission >= (pow(2,1))) {
				$addflag = 1;
				$permission = $permission - (pow(2,1));
			}
			
			$MENU[$menuid]['VIEW'] = $viewflag;
			$MENU[$menuid]['UPDATE'] = $updateflag;
			$MENU[$menuid]['DELETE'] = $deleteflag;
			$MENU[$menuid]['ADD'] = $addflag;
			$MENU[$menuid]['CHILD'] = 0;				
			
			if ($found == 0) {
				$_SESSION[session_id()]['CURRENT_MENU'] = $menuid;
				$_SESSION[session_id()]['FIRST_MENU'] = $menuid;
				
				if ($result['PARENT_MENU_ID'] != 0) {
					$_SESSION[session_id()]['FIRST_MENU'] = $result['PARENT_MENU_ID'];
				}					
			}			
			if ($result['FIRST_MENU'] == 1) {
				$found = 1;
				$_SESSION[session_id()]['CURRENT_MENU'] = $menuid;
				$_SESSION[session_id()]['FIRST_MENU'] = $menuid;
				
				if ($result['PARENT_MENU_ID'] != 0) {
					$_SESSION[session_id()]['FIRST_MENU'] = $result['PARENT_MENU_ID'];
				}
			}
			if ($result['PARENT_MENU_ID'] == 0) {
				$PARENT[$menuid]['MENU_NAME'] = $result['MENU_NAME'];
				$PARENT[$menuid]['CHILD_COUNT'] = 0;
			}
			else {
				$PARENT[$result['PARENT_MENU_ID']]['CHILD_COUNT'] = $PARENT[$result['PARENT_MENU_ID']]['CHILD_COUNT'] + 1;
				$CHILD[$result['PARENT_MENU_ID']][$menuid]['MENU_NAME'] = $result['MENU_NAME'];
			}
		}
		$_SESSION[session_id()]['MENU'] = $MENU;
		
		$sql = "	SELECT COUNT(*) COUNTN FROM EMPLOYEE_LEAVE_HISTORY ELH
					LEFT JOIN USER USER ON USER.EMPLOYEE_ID = ELH.APPROVED_ID
					WHERE SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
					AND USER.USER_ID = ? AND ELH.APPROVED_DATE IS NULL";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($userid));			
		
		$approved_count = 0;
		if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$approved_count = $arr['COUNTN'];
		}
		
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;
?>