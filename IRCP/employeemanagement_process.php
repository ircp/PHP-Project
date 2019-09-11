<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $parameter);
		
		$employeecode = trim($explode[0]);
		$firstname = trim($explode[1]);		
		$lastname = trim($explode[2]);		
		$employeestatus = trim($explode[3]);	
		$title = trim($explode[4]);	
		$firstname_en = trim($explode[5]);
		$lastname_en = trim($explode[6]);
		$nickname = trim($explode[7]);
		$departmentid = trim($explode[8]);
		$positionid = trim($explode[9]);
		$startdate = trim($explode[10]);
		$enddate = trim($explode[11]);
		$email = trim($explode[12]);
		$cumulativevacation = trim($explode[13]);
		$emp_file = trim($explode[14]);
		$groupid = trim($explode[15]);
		$workingtime = trim($explode[16]);
		$cardcodeprefix = trim($explode[17]);
		$cardcodesuffix = trim($explode[18]);
		
		$title_en = '';
		
		$startdate_u = ((int)substr($startdate, 6,4) - 543)."-".substr($startdate, 3, 2)."-".substr($startdate, 0,2)." 00:00:00";
		
		if ($employeestatus == 1) {
			$enddate_u = '9999-12-31 23:59:59';
		}
		else {
			$enddate_u = ((int)substr($enddate, 6,4) - 543)."-".substr($enddate, 3, 2)."-".substr($enddate, 0,2)." 00:00:00";
		}
		
		if ($cumulativevacation == '') {
			$cumulativevacation = 0;
		}
		
		$sql = "	SELECT DESCRIPTION
					FROM REFERENCE_CODE
					WHERE REFERENCE_TYPE_ID = 6 AND LABEL = ?";
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($title));

		if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$title_en = $arr['DESCRIPTION'];
		}
		$sex = '';
		
		if ($title == 'นาย') {
			$sex = 'ชาย';
		}
		else {
			$sex = 'หญิง';
		}
		
		if ($mode == 'A') {
			$sql = "	INSERT INTO SEQUENCE_EMPLOYEE (EMPLOYEE_CODE) VALUES (?)";
			
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($employeecode));
			
			$sql = "	SELECT LAST_INSERT_ID() EMPLOYEE_ID";
			
			$stmt = $conn->prepare($sql);
			$stmt->execute();
			
			$employeeid = 0;
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$employeeid = $arr['EMPLOYEE_ID'];
			}

			$sql = "	INSERT INTO EMPLOYEE 
						(EMPLOYEE_ID, CREATED_DATE, LAST_MODIFIED, USER_ID, EFFECTIVE_START_DATE, EFFECTIVE_END_DATE, EMPLOYEE_CODE, TITLE, FIRST_NAME, LAST_NAME, NICK_NAME, TITLE_EN, FIRST_NAME_EN, LAST_NAME_EN, START_DATE, END_DATE, EMPLOYEE_STATUS, POSITION_ID, SEX, IMAGE_EMPLOYEE, EMAIL, CUMULATIVE_VACATION, DEPARTMENT_ID, WORKING_TIME, CARD_CODE_PREFIX, CARD_CODE_SUFFIX)
						VALUES 
						(?, NOW(), NOW(), ?, NOW(), '9999-12-31 23:59:59', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
						";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($employeeid, $userid, $employeecode, $title, $firstname, $lastname, $nickname, $title_en, $firstname_en, $lastname_en, $startdate_u, $enddate_u, $employeestatus, $positionid, $sex, $emp_file, $email, $cumulativevacation, $departmentid, $workingtime, $cardcodeprefix, $cardcodesuffix));
		
			$username = str_replace('@ircp.co.th', '', $email);
			
			$sql = "	INSERT INTO USER (CREATED_DATE, LAST_MODIFIED, USER_NAME, PASSWORD, FIRST_NAME, LAST_NAME, TELEPHONE, EMAIL, VALID_IND_CODE, GROUP_ID, EMPLOYEE_ID, COMPANY_ID)
						VALUES (NOW(), NOW(), ?, '', ?, ?, '', ?, 1, ?, ?, 1)";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($username, $firstname, $lastname, $email, $groupid, $employeeid));
				
		}
		else if ($mode == 'C') {
			$sql = "	UPDATE EMPLOYEE
						SET GENERAL_1 = 1, EFFECTIVE_END_DATE = NOW()
						WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
						AND EMPLOYEE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($key));	
			
			$sql = "	INSERT INTO EMPLOYEE 
						(EMPLOYEE_ID, CREATED_DATE, LAST_MODIFIED, USER_ID, EFFECTIVE_START_DATE, EFFECTIVE_END_DATE, EMPLOYEE_CODE, TITLE, FIRST_NAME, LAST_NAME, NICK_NAME, TITLE_EN, FIRST_NAME_EN, LAST_NAME_EN, START_DATE, END_DATE, EMPLOYEE_STATUS, POSITION_ID, SEX, IMAGE_EMPLOYEE, EMAIL, CUMULATIVE_VACATION, DEPARTMENT_ID, WORKING_TIME, CARD_CODE_PREFIX, CARD_CODE_SUFFIX)
						SELECT EMPLOYEE_ID, CREATED_DATE, NOW(), ?, EFFECTIVE_END_DATE, '9999-12-31 23:59:59', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
						FROM EMPLOYEE
						WHERE EMPLOYEE_ID = ? AND GENERAL_1 = 1";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $employeecode, $title, $firstname, $lastname, $nickname, $title_en, $firstname_en, $lastname_en, $startdate_u, $enddate_u, $employeestatus, $positionid, $sex, $emp_file, $email, $cumulativevacation, $departmentid, $workingtime, $cardcodeprefix, $cardcodesuffix, $key));	
			
			if ($employeestatus == 2) {
				$validindcode = 0;
			}
			else {
				$validindcode = 1;
			}
			
			if ($employeeid ==1) {
				$groupid =1 ;
			}
			
			$sql = "	UPDATE USER 
						SET LAST_MODIFIED = NOW() , FIRST_NAME = ?, LAST_NAME = ?, EMAIL = ?, VALID_IND_CODE =? , GROUP_ID = ?
						WHERE EMPLOYEE_ID = ?";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($firstname, $lastname, $email, $validindcode, $groupid, $key));
			
			$sql = "	UPDATE EMPLOYEE
						SET GENERAL_1 = NULL
						WHERE EMPLOYEE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($key));	
		}
		else if ($mode == 'D') {
			$sql = "	UPDATE EMPLOYEE
						SET LAST_MODIFIED = ?, USER_ID = ?, EFFECTIVE_END_DATE = NOW()
						WHERE SYSDATE() BETWEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
						AND EMPLOYEE_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($key));	

			$sql = "	UPDATE USER
						SET VALID_IND_CODE = 0
						WHERE EMPLOYEE_ID = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($key));							
		}
		
		$conn->commit();	
	}	
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;	
?>