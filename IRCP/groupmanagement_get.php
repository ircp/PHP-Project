<?
	require ('connection.php');
	
	try {
		$sql = "	SELECT GROUP_ID, GROUP_NAME, GROUP_ABBREVIATION, GROUP_DESCRIPTION, VALID_IND_CODE, COMPANY_ID
					FROM GROUP_USER
					WHERE GROUP_ID = ?";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));

		$groupname = '';
		$groupabbreviation = '';
		$groupdescription = '';
		$validindcode = '';
		$companyid = '';
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$groupname = $arr['GROUP_NAME'];
			$groupabbreviation = $arr['GROUP_ABBREVIATION'];
			$groupdescription = $arr['GROUP_DESCRIPTION'];
			$validindcode = $arr['VALID_IND_CODE'];
			$companyid = $arr['COMPANY_ID'];
		}
		
		$disable = '';

		if ($mode == 'D') {
			$disable = 'disabled';
		}		
	
		echo("<div class='container'>");
		echo("<div class='modal-body'>");
		
		echo("<form>");
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ชื่อกลุ่มผู้ใช้งาน</label>");
        echo("<input type='text' class='form-control is-invalid' id='groupname' value='".$groupname."' ".$disable." maxlength='80' size='80'>");
        echo("</div>");	

        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ชื่อย่อ</label>");
        echo("<input type='text' class='form-control is-valid' id='abbreviation' value='".$groupabbreviation."' ".$disable." maxlength='20' size='20' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
        echo("</div>");	

		echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>บริษัท</label>");
		echo("<select class='selectpicker form-control' id='companyid' ".$disable.">");
		
		$sql = "	SELECT COMPANY_ID, COMPANY_NAME, COMPANY_TYPE
					FROM COMPANY
					WHERE VALID_IND_CODE = 1
					ORDER BY COMPANY_TYPE, COMPANY_NAME";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
	
		$type = 1;
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
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>รายละเอียด</label>");
        echo("<textarea class='form-control is-valid' id='description' rows='3' ".$disable.">".$groupdescription."</textarea>");
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