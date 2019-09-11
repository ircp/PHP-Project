<?
	echo("<div class ='row'>");
	echo("<div class='card text-white bg-info mb-12'>");

	echo("<div class = 'card-body'>");
	echo("<div class='form-group'>");
	echo("<div class='d-flex justify-content-center'>");
	echo("<div class='feature-item'>");
	echo("<div class='input-group'>");
	echo("<button type='button' class='btny-info btnx-xl js-scroll-trigger'>".$_SESSION[session_id()]['MENU'][$menuid]['MENU_NAME']."</button> ");
	echo("</div>");
	echo("</div>");
	echo("</div>");
	echo("</div>");
/*
	echo("<br>");
	echo("<div class='form-group' >");
	echo("<div class='col-md-12 col-xs-12 col-sm-12'>");	
	echo("<div class='input-group mb-3'>");
	echo("<input type='text' class='form-control' placeholder='' id='searchparameter' maxlength='50' size='50'>");
	echo("<div class='input-group-append'>");
    echo("<button class='btn btn-outline-info' type='button' onclick=\"windowOpen('2', '".$menuid."', '', '');return false;\"><i class='fas fa-search'></i></button>");
	echo("</div>");
	echo("</div>");
	echo("</div>");
	echo("</div>");	
*/
	$currentyear = (int)date("Y") + 543;
	echo("<br>");
	echo("<div class='form-group'>");
	echo("<div class='col-md-12 col-xs-12 col-sm-12'>");	
	echo("<div class='input-group mb-4'>");
	echo("<select class='selectpicker form-control' id='yearselected' >");
	for ($x = 0; $x < 3; $x++) {
		echo("<option value='".$currentyear."'>ปี ".$currentyear."</option>");
		$currentyear = $currentyear - 1;
	}
	echo("</select>");
	echo("</div>");
	echo("</div>");
	echo("</div>");	
	echo("<br>");
	echo("<div class='form-group'>");
	echo("<div class='col-md-12 col-xs-12 col-sm-12'>");	
	echo("<div class='input-group mb-4'>");
	echo("<select class='selectpicker form-control' id='monthselected' >");
	echo("<option value='1'>มกราคม</option>");
	echo("<option value='2'>กุมภาพันธ์</option>");
	echo("<option value='3'>มีนาคม</option>");
	echo("<option value='4'>เมษายน</option>");
	echo("<option value='5'>พฤษภาคม</option>");
	echo("<option value='6'>มิถุนายน</option>");
	echo("<option value='7'>กรกฎาคม</option>");
	echo("<option value='8'>สิงหาคม</option>");
	echo("<option value='9'>กันยายน</option>");
	echo("<option value='10'>ตุลาคม</option>");
	echo("<option value='11'>พฤศจิกายน</option>");
	echo("<option value='12'>ธันวาคม</option>");
	echo("</select>");
	echo("</div>");
	echo("</div>");
	echo("</div>");	
	echo("<br>");
	echo("<div class='form-group'>");
	echo("<div class='col-md-12 col-xs-12 col-sm-12'>");	
	echo("<div class='input-group mb-3'>");
	echo("<select class='selectpicker form-control' id='employeeidselected' >");
	require ('connection.php');
	
	$sql = "	SELECT DEPARTMENT_ID, EMPLOYEE.EMPLOYEE_ID
				FROM EMPLOYEE
				LEFT JOIN USER ON USER.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID
				WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
				AND USER.USER_ID = ?";
	$stmt = $conn->prepare($sql);
	$stmt->execute(array($userid));	
	
	$depart = 0;
	$supervisor = 0;
	if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$depart = $arr['DEPARTMENT_ID'];
		$supervisor = $arr['EMPLOYEE_ID'];
	}
	
	if ($depart == 10 || $depart == 12 || $depart == 1) {
		$sql = "	SELECT EMPLOYEE_ID, FIRST_NAME, LAST_NAME 
					FROM EMPLOYEE
					WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
					AND EMPLOYEE_ID <> 1 AND WORKING_TIME = 1
					ORDER BY FIRST_NAME, LAST_NAME";
		$stmt = $conn->prepare($sql);
		$stmt->execute();
	}
	else {
		
		$sql = "	SELECT EMPLOYEE.EMPLOYEE_ID, FIRST_NAME, LAST_NAME 
					FROM EMPLOYEE
					LEFT JOIN DEPARTMENT_EXECUTIVE DE ON DE.DEPARTMENT_ID = EMPLOYEE.DEPARTMENT_ID 
					WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
					AND EMPLOYEE.EMPLOYEE_ID <> 1 AND EMPLOYEE.EMPLOYEE_STATUS = 1 AND  DE.EMPLOYEE_ID = ? AND EMPLOYEE.WORKING_TIME = 1
					ORDER BY FIRST_NAME, LAST_NAME";
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($supervisor));	
	}

	while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
		echo("<option value='".$arr['EMPLOYEE_ID']."'>".$arr['FIRST_NAME']." ".$arr['LAST_NAME']."</option>");
	}
	
	echo("</select>");
	
	echo("</div>");
	echo("</div>");
	echo("</div>");
	echo("<br>");
	echo("<div class='form-group'>");
	echo("<div class='d-flex justify-content-center'>");	
	echo("<div class='feature-item'>");
	echo("<div class='input-group'>");
	if ($_SESSION[session_id()]['MENU'][$menuid]['UPDATE'] == 1) {
		echo("<a href='' class='btnx-info btnx-xl js-scroll-trigger' style='text-decoration: none' onclick = \"generateReport('1', '".$menuid."', 'A','');return false;\"><i class='fas fa-print'></i> จัดพิมพ์รายงาน</a>");
	}
	echo("</div>");
	echo("</div>");
	echo("</div>");
	echo("</div>");
	
	echo("</div>");
	echo("</div>");
	echo("</div>");	
?>