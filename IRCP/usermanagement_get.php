<?
	require ('connection.php');
	
	try {
		$sql = "	SELECT USER.USER_ID, USER.USER_NAME, USER.FIRST_NAME, USER.LAST_NAME, USER.TELEPHONE, USER.EMAIL, USER.VALID_IND_CODE, USER.GROUP_ID, USER.COMPANY_ID, USER.EMPLOYEE_ID
					FROM USER USER
					WHERE USER_ID = ?";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));

		$username = '';
		$firstname = '';
		$lastname = '';
		$telephone = '';
		$email = '';
		$validindcode = '';
		$groupid = '';
		$companyid = '';
		$employeeid = '';
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$username = $arr['USER_NAME'];
			$firstname = $arr['FIRST_NAME'];
			$lastname = $arr['LAST_NAME'];
			$telephone = $arr['TELEPHONE'];
			$email = $arr['EMAIL'];
			$validindcode = $arr['VALID_IND_CODE'];
			$groupid = $arr['GROUP_ID'];
			$companyid = $arr['COMPANY_ID'];
			$employeeid = $arr['EMPLOYEE_ID'];
		}
		
		$disable = '';

		if ($mode == 'D') {
			$disable = 'disabled';
		}		
	
		echo("<div class='container'>");
		echo("<div class='modal-body'>");
		
		echo("<form>");
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>รหัสผู้ใช้งาน</label>");
        echo("<input type='text' class='form-control is-invalid' id='username' value='".$username."' ".$disable." maxlength='80' size='80' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
        echo("</div>");	

        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ชื่อ</label>");
        echo("<input type='text' class='form-control is-invalid' id='firstname' value='".$firstname."' ".$disable." maxlength='80' size='80' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
        echo("</div>");	
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>นามสกุล</label>");
        echo("<input type='text' class='form-control is-valid' id='lastname' value='".$lastname."' ".$disable." maxlength='80' size='80' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
        echo("</div>");	
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>เบอร์โทร</label>");
        echo("<input type='text' class='form-control is-valid' id='telephone' value='".$telephone."' ".$disable." maxlength='10' size='10'  onkeypress=\"return validateinput('1',event.charCode);(event.charCode >= 48 && event.charCode<= 57) || (event.charCode == 0);\">");
        echo("</div>");	
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>Email</label>");
        echo("<input type='text' class='form-control is-valid' id='email' value='".$email."' ".$disable." maxlength='80' size='80'>");
        echo("</div>");	

		if ($employeeid == 0) {
			echo("<div class='form-group'>");
		}
		else {
			echo("<div class='form-group' style='display:none'>");
		}
        echo("<label for='recipient-name' class='col-form-label'>บริษัท</label>");
		echo("<select class='selectpicker form-control' id='companyid' ".$disable." onchange=\"changeDropdown('companyid', 'groupid', '6', '".$menuid."', '".$companyid."','".$mode."');\">");
		
		$sql = "	SELECT COMPANY_ID, COMPANY_NAME 
					FROM COMPANY
					WHERE VALID_IND_CODE = 1 
					ORDER BY COMPANY_TYPE, COMPANY_NAME";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
	
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($companyid== $arr['COMPANY_ID']) {
				echo("<option value='".$arr['COMPANY_ID']."' selected>".$arr['COMPANY_NAME']."</option>");
			}
			else {
				echo("<option value='".$arr['COMPANY_ID']."'>".$arr['COMPANY_NAME']."</option>");
			}			
		}
		echo("</select>");
        echo("</div>");			
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>กลุ่มผู้ใช้งาน</label>");
		echo("<select class='selectpicker form-control' id='groupid' ".$disable.">");
		
		if ($employeeid != 0) {
			$sql = "	SELECT GROUP_ID, GROUP_NAME 
						FROM GROUP_USER 
						WHERE VALID_IND_CODE = 1 AND COMPANY_ID = 1
						ORDER BY GROUP_NAME";

			$stmt = $conn->prepare($sql);
			$stmt->execute();
		}
		else {
			$sql = "	SELECT GROUP_ID, GROUP_NAME 
						FROM GROUP_USER 
						WHERE VALID_IND_CODE = 1 AND COMPANY_ID = ?
						ORDER BY GROUP_NAME";

			$stmt = $conn->prepare($sql);
			$stmt->execute($companyid);
		}
	
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
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ใช้งาน</label>");
		echo("<select class='selectpicker form-control' id='validindcode' ".$disable.">");
		
		$sql = "	SELECT LABEL, REFERENCE_CODE 
					FROM REFERENCE_CODE
					WHERE REFERENCE_TYPE_ID = 2 AND VALID_IND_CODE = 1
					ORDER BY ORDERING, LABEL";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
	
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($validindcode== $arr['REFERENCE_CODE']) {
				echo("<option value='".$arr['REFERENCE_CODE']."' selected>".$arr['LABEL']."</option>");
			}
			else {
				echo("<option value='".$arr['REFERENCE_CODE']."'>".$arr['LABEL']."</option>");
			}			
		}
		echo("</select>");
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