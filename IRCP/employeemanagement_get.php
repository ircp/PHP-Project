<?
	require ('connection.php');
	
	try {
		$sql = "	SELECT EMPLOYEE.EMPLOYEE_CODE, EMPLOYEE.TITLE, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, EMPLOYEE.EMPLOYEE_STATUS, EMPLOYEE.WORKING_TIME,
					EMPLOYEE.DEPARTMENT_ID, EMPLOYEE.POSITION_ID, EMPLOYEE.EMAIL, EMPLOYEE.SEX, EMPLOYEE.EMPLOYEE_ID,
					EMPLOYEE.NICK_NAME, EMPLOYEE.TITLE_EN, EMPLOYEE.FIRST_NAME_EN, EMPLOYEE.LAST_NAME_EN, EMPLOYEE.START_DATE, EMPLOYEE.END_DATE, EMPLOYEE.CUMULATIVE_VACATION, EMPLOYEE.IMAGE_EMPLOYEE,
					EMPLOYEE.CARD_CODE_PREFIX, EMPLOYEE.CARD_CODE_SUFFIX,
					RC1.LABEL EMPLOYEESTATUS, DEPARTMENT.DEPARTMENT_NAME, RC2.LABEL POSITION, USER.GROUP_ID
					FROM EMPLOYEE EMPLOYEE
					LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 5 AND RC1.REFERENCE_CODE = EMPLOYEE.EMPLOYEE_STATUS
					LEFT JOIN REFERENCE_CODE RC2 ON RC2.REFERENCE_TYPE_ID = 4 AND RC2.REFERENCE_CODE = EMPLOYEE.POSITION_ID
					LEFT JOIN DEPARTMENT DEPARTMENT ON DEPARTMENT.DEPARTMENT_ID = EMPLOYEE.DEPARTMENT_ID
					LEFT JOIN USER USER ON USER.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID
					WHERE SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE 
					AND EMPLOYEE.EMPLOYEE_ID = ?
					ORDER BY DEPARTMENT.DEPARTMENT_NAME, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));

		$employeecode = '';
		$title = '';
		$firstname = '';
		$lastname = '';
		$employeestatus = '';
		$departmentid = '';
		$positionid = '';
		$email = '';
		$sex = '';
		$employeeid = '';
		$nickname = '';
		$title_en = '';
		$firstnameen  = '';
		$lastnameen = '';
		$startdate = '';
		$endate = '';
		$cumulativevacation  = '';
		$imageemployee = '';
		$groupid = '';
		$workingtime = '';
		$cardcodeprefix = '';
		$cardcodesuffix = '';
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$employeecode = $arr['EMPLOYEE_CODE'];
			$title = $arr['TITLE'];
			$firstname = $arr['FIRST_NAME'];
			$lastname = $arr['LAST_NAME'];
			$employeestatus = $arr['EMPLOYEE_STATUS'];
			$departmentid = $arr['DEPARTMENT_ID'];
			$positionid = $arr['POSITION_ID'];
			$email = $arr['EMAIL'];
			$sex = $arr['SEX'];
			$employeeid = $arr['EMPLOYEE_ID'];
			$nickname = $arr['NICK_NAME'];
			$title_en = $arr['TITLE_EN'];
			$firstnameen  = $arr['FIRST_NAME_EN'];
			$lastnameen = $arr['LAST_NAME_EN'];
			$startdate = substr($arr['START_DATE'],8,2)."/".substr($arr['START_DATE'],5,2)."/".((int)substr($arr['START_DATE'],0,4) + 543);
			$endate =substr($arr['END_DATE'],8,2)."/".substr($arr['END_DATE'],5,2)."/".((int)substr($arr['END_DATE'],0,4) + 543);
			$cumulativevacation  = $arr['CUMULATIVE_VACATION'];
			$imageemployee  = $arr['IMAGE_EMPLOYEE'];
			$groupid  = $arr['GROUP_ID'];
			$workingtime = $arr['WORKING_TIME'];
			$cardcodeprefix = $arr['CARD_CODE_PREFIX'];
			$cardcodesuffix = $arr['CARD_CODE_SUFFIX'];
		}
		
		$disable = '';

		if ($mode == 'D') {
			$disable = 'disabled';
		}		
		
		if ($employeestatus == 1) {
			$endate = '';
		}
	
		echo("<div class='container'>");
		echo("<div class='modal-body'>");
		
		echo("<form>");

		echo("<table class = 'table table-striped display' cellspacing='0' cellspadding = '0' width='100%'>");
		echo("<tbody>");
		echo("<tr>");
		echo("<td class = 'text-left' width ='30%'>");
		echo("<div id='employeeimage'>");
		if ($mode == 'A') {
			echo("<span class='btn btn-secondary fileinput-button'>");
			echo("<span><img  id='emp_image' src='employee_image/adminircp.jpg'  class='img-thumbnail' width='140'></span>");
			echo("<input id='imageupload' type='file' name='files[]' multiple >");
			echo("<input type='hidden' id='emp_file' value = 'adminircp.jpg'>");
			echo("</span>");
		}
		else {
			echo("<span class='btn btn-secondary fileinput-button'>");
			echo("<span><img id='emp_image' src='employee_image/".$imageemployee."'  class='img-thumbnail' width='140'></span>");
			echo("<input id='imageupload' type='file' name='files[]' multiple >");
			echo("<input type='hidden' id='emp_file' value = '".$imageemployee."'>");
			echo("</span>");						
		}
		
		echo("<div id='files' class='files'></div>");
		echo("</div>");
		echo("</td>");
		echo("<td class = 'text-left'>");
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>รหัสพนักงาน</label>");
        echo("<input type='text' class='form-control is-invalid' id='employeecode' value='".$employeecode."' ".$disable." maxlength='20' size='20' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
        echo("</div>");	

        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>สถานะ</label>");
		echo("<select class='selectpicker form-control' id='employeestatus' ".$disable." onchange=\"changeDisplay('employeestatus', 'enddate_section', '6', '".$menuid."', '".$employeeid."','".$mode."');\">");
		
		if ($mode == 'A') {
			$sql = "	SELECT LABEL, REFERENCE_CODE 
						FROM REFERENCE_CODE
						WHERE REFERENCE_TYPE_ID = 5 AND VALID_IND_CODE = 1 AND REFERENCE_CODE <> 2
						ORDER BY ORDERING, LABEL";

		}
		else {
			$sql = "	SELECT LABEL, REFERENCE_CODE 
						FROM REFERENCE_CODE
						WHERE REFERENCE_TYPE_ID = 5 AND VALID_IND_CODE = 1
						ORDER BY ORDERING, LABEL";
		}
		
		$stmt = $conn->prepare($sql);
		$stmt->execute();
	
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($employeestatus== $arr['REFERENCE_CODE']) {
				echo("<option value='".$arr['REFERENCE_CODE']."' selected>".$arr['LABEL']."</option>");
			}
			else {
				echo("<option value='".$arr['REFERENCE_CODE']."'>".$arr['LABEL']."</option>");
			}			
		}
		echo("</select>");
        echo("</div>");	
		
		echo("</td>");					
		echo("</tr>");
		echo("</tbody>");
		echo("</table>");
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>คำนำหน้า</label>");
		echo("<select class='selectpicker form-control' id='title' ".$disable.">");
		
		$sql = "	SELECT LABEL, REFERENCE_CODE 
					FROM REFERENCE_CODE
					WHERE REFERENCE_TYPE_ID = 6 AND VALID_IND_CODE = 1
					ORDER BY ORDERING, LABEL";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
	
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($title== $arr['LABEL']) {
				echo("<option value='".$arr['LABEL']."' selected>".$arr['LABEL']."</option>");
			}
			else {
				echo("<option value='".$arr['LABEL']."'>".$arr['LABEL']."</option>");
			}			
		}
		echo("</select>");
        echo("</div>");	
		
        echo("<div class='form-row'>");
		 echo("<div class='form-group col-md-5'>");
        echo("<label for='recipient-name' class='col-form-label'>ชื่อ (ภาษาไทย) </label>");
        echo("<input type='text' class='form-control is-invalid' id='firstname' value='".$firstname."' ".$disable." maxlength='80' size='80' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
        echo("</div>");	
		 echo("<div class='form-group col-md-7'>");
        echo("<label for='recipient-name' class='col-form-label'>นามสกุล (ภาษาไทย)</label>");
        echo("<input type='text' class='form-control is-invalid' id='lastname' value='".$lastname."' ".$disable." maxlength='200' size='200' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
        echo("</div>");	
		echo("</div>");	
		
        echo("<div class='form-row'>");
		 echo("<div class='form-group col-md-5'>");
        echo("<label for='recipient-name' class='col-form-label'>ชื่อ (ภาษาอังกฤษ) </label>");
        echo("<input type='text' class='form-control is-invalid' id='firstname_en' style='text-transform:uppercase'  value='".$firstnameen."' ".$disable." maxlength='80' size='80' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
        echo("</div>");	
		 echo("<div class='form-group col-md-7'>");
        echo("<label for='recipient-name' class='col-form-label'>นามสกุล (ภาษาอังกฤษ)</label>");
        echo("<input type='text' class='form-control is-invalid' id='lastname_en'  style='text-transform:uppercase'  value='".$lastnameen."' ".$disable." maxlength='200' size='200' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
        echo("</div>");	
		echo("</div>");	

        echo("<div class='form-row'>");
		echo("<div class='form-group col-md-6'>");
        echo("<label for='recipient-name' class='col-form-label'>ชื่อเล่น</label>");
        echo("<input type='text' class='form-control is-valid' id='nickname' value='".$nickname."' ".$disable." maxlength='50' size='50' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
        echo("</div>");	
       echo("<div class='form-group  col-md-6'>");
        echo("<label for='recipient-name' class='col-form-label'>กลุ่มผู้ใช้งาน</label>");
		echo("<select class='selectpicker form-control' id='groupid' ".$disable.">");
		
		if ($userid == 1) {
			$sql = "	SELECT GROUP_ID, GROUP_NAME 
						FROM GROUP_USER 
						WHERE VALID_IND_CODE = 1 AND COMPANY_ID = 1 
						ORDER BY GROUP_NAME";
		}
		else {
			$sql = "	SELECT GROUP_ID, GROUP_NAME 
						FROM GROUP_USER 
						WHERE VALID_IND_CODE = 1 AND COMPANY_ID = 1 AND GROUP_ID <> 1
						ORDER BY GROUP_NAME";
		}

		$stmt = $conn->prepare($sql);
		$stmt->execute();
	
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($groupid== $arr['GROUP_ID']) {
				echo("<option value='".$arr['GROUP_ID']."' selected>".$arr['GROUP_NAME']."</option>");
			}
			else {
				echo("<option value='".$arr['GROUP_ID']."'>".$arr['GROUP_NAME']."</option>");
			}			
		}
		
		echo("</select>");
        echo("</div>");		
		echo("</div>");	

		 echo("<div class='form-row'>");
        echo("<div class='form-group col-md-6'>");
        echo("<label for='recipient-name' class='col-form-label'>แผนก/ฝ่าย</label>");
		echo("<select class='selectpicker form-control' id='departmentid' ".$disable.">");
		
		$sql = "	SELECT DEPARTMENT_ID, DEPARTMENT_NAME
					FROM DEPARTMENT
					WHERE VALID_IND_CODE = 1
					ORDER BY DEPARTMENT_NAME";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
	
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($departmentid== $arr['DEPARTMENT_ID']) {
				echo("<option value='".$arr['DEPARTMENT_ID']."' selected>".$arr['DEPARTMENT_NAME']."</option>");
			}
			else {
				echo("<option value='".$arr['DEPARTMENT_ID']."'>".$arr['DEPARTMENT_NAME']."</option>");
			}			
		}
		echo("</select>");
        echo("</div>");	
		
        echo("<div class='form-group col-md-6'>");
        echo("<label for='recipient-name' class='col-form-label'>ตำแหน่ง</label>");
		echo("<select class='selectpicker form-control' id='positionid' ".$disable.">");
		
			$sql = "	SELECT LABEL, REFERENCE_CODE 
					FROM REFERENCE_CODE
					WHERE REFERENCE_TYPE_ID = 4 AND VALID_IND_CODE = 1
					ORDER BY ORDERING, LABEL";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
	
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($positionid== $arr['REFERENCE_CODE']) {
				echo("<option value='".$arr['REFERENCE_CODE']."' selected>".$arr['LABEL']."</option>");
			}
			else {
				echo("<option value='".$arr['REFERENCE_CODE']."'>".$arr['LABEL']."</option>");
			}			
		}
		echo("</select>");
        echo("</div>");	
		 echo("</div>");	
		
		echo("<div class='form-row'>");
		if ($employeestatus == 2) {
			echo("<div class='form-group col-md-6'>");
		}
		else {
			echo("<div class='form-group col-md-6'>");
		}
        echo("<label for='recipient-name' class='col-form-label'>วันที่เริ่มต้นทำงาน</label>");
		if ($mode == 'A') {
			$currentdate = date("d")."/".date("m")."/".((int) date("Y") + 543);
			echo("<input type='text' class='form-control ' id='startdate' placeholder='วันที่เริ่มต้นทำงาน' value = '".$currentdate."' maxlength='20' size='20'>");				
		}
		else {
			echo("<input type='text'  class='form-control is-invalid' id='startdate' placeholder='วันที่เริ่มต้นทำงาน' value = '".$startdate."' ".$disable." maxlength='20' size='20'>");
		}
        echo("</div>");		
		
		if ($employeestatus == 2) {
			echo("<div class='form-group col-md-6' id='enddate_section'>");
		}
		else {
			echo("<div class='form-group col-md-6' style='display:none'  id='enddate_section'>");
		}
        echo("<label for='recipient-name' class='col-form-label'>วันที่สิ้นสุดทำงาน</label>");
		
		if ($endate == '') {
			$endate = date("d")."/".date("m")."/".((int) date("Y") + 543);
		}
		
		if ($mode == 'A') {
			echo("<input type='text' class='form-control ' id='enddate' placeholder='วันที่สิ้นสุดทำงาน' value = '' maxlength='20' size='20'>");				
		}
		else {
			echo("<input type='text'  class='form-control is-invalid' id='enddate' placeholder='วันที่สิ้นสุดทำงาน' value = '".$endate."' ".$disable." maxlength='20' size='20'>");
		}
        echo("</div>");	
		echo("</div>");	

        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>Email</label>");
        echo("<input type='text' class='form-control is-invalid' id='email' value='".$email."' ".$disable." maxlength='90' size='90' >");
        echo("</div>");		
		
		if ($mode == 'A') {
			echo("<div class='form-group' style='display:none'>");
		}
		else {
			echo("<div class='form-group'>");
		}
        echo("<label for='recipient-name' class='col-form-label'>วันลาพักร้อนคงเหลือจากปีก่อน</label>");
        echo("<input type='text' class='form-control is-valid' id='cumulativevacation' value='".$cumulativevacation."' ".$disable." maxlength='90' size='90' >");
        echo("</div>");	

      echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ลงเวลาเข้างาน</label>");
		echo("<select class='selectpicker form-control' id='workingtime' ".$disable.">");
		
		$sql = "	SELECT LABEL, REFERENCE_CODE 
					FROM REFERENCE_CODE
					WHERE REFERENCE_TYPE_ID = 2 AND VALID_IND_CODE = 1
					ORDER BY ORDERING, LABEL";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
	
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($workingtime== $arr['REFERENCE_CODE']) {
				echo("<option value='".$arr['REFERENCE_CODE']."' selected>".$arr['LABEL']."</option>");
			}
			else {
				echo("<option value='".$arr['REFERENCE_CODE']."'>".$arr['LABEL']."</option>");
			}			
		}
		echo("</select>");
        echo("</div>");	

        echo("<div class='form-row'>");
		 echo("<div class='form-group col-md-5'>");
        echo("<label for='recipient-name' class='col-form-label'>Card code Prefix</label>");
        echo("<input type='text' class='form-control is-valid' id='cardcodeprefix' value='".$cardcodeprefix."' ".$disable." maxlength='80' size='80' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
        echo("</div>");	
		 echo("<div class='form-group col-md-7'>");
        echo("<label for='recipient-name' class='col-form-label'>Card code Suffix</label>");
        echo("<input type='text' class='form-control is-valid' id='cardcodesuffix' value='".$cardcodesuffix."' ".$disable." maxlength='200' size='200' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
        echo("</div>");	
		echo("</div>");		
		
		echo("</form>");
		echo("</div>");
		echo("<div class='modal-footer'>");
		echo("<div class='btn-group' role='group' aria-label='Basic example'>");
		echo("<button type='button' class='btn btn-secondary' data-dismiss='modal'  id='closeButton'>ปิด</button>");
		echo("<button type='button' class='btn btn-info' onclick = \"windowProcess('3', '".$menuid."', '".$mode."', '".$key."');\">บันทึก</button>");
		echo("</div>");
		echo("</div>");			
		echo("</div>");
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;
?>