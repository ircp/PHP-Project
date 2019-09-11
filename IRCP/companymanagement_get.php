<?
	require ('connection.php');
	
	try {
		$sql = "	SELECT COMPANY.COMPANY_ID, COMPANY.COMPANY_NAME, COMPANY.ABBREVIATION, COMPANY.DESCRIPTION, COMPANY.COMPANY_TYPE, COMPANY.VALID_IND_CODE
					FROM COMPANY COMPANY
					WHERE COMPANY_ID = ?";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));

		$companyid = '';
		$companyname = '';
		$abbreviation = '';
		$description = '';
		$companytype = '';
		$validindcode = '';
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$companyid = $arr['COMPANY_ID'];
			$companyname = $arr['COMPANY_NAME'];
			$abbreviation = $arr['ABBREVIATION'];
			$description = $arr['DESCRIPTION'];
			$companytype = $arr['COMPANY_TYPE'];
			$validindcode = $arr['VALID_IND_CODE'];
		}
		
		$disable = '';

		if ($mode == 'D') {
			$disable = 'disabled';
		}		
	
		echo("<div class='container'>");
		echo("<div class='modal-body'>");
		
		echo("<form>");
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ชื่อบริษัท</label>");
        echo("<input type='text' class='form-control is-invalid' id='companyname' value='".$companyname."' ".$disable." maxlength='80' size='80' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
        echo("</div>");	

        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ชื่อย่อ</label>");
        echo("<input type='text' class='form-control is-invalid' id='abbreviation' value='".$abbreviation."' ".$disable." maxlength='20' size='20' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
        echo("</div>");	
		
		echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ประเภทบริษัท</label>");
		echo("<select class='selectpicker form-control' id='companytype' ".$disable.">");
		
		$sql = "	SELECT LABEL, REFERENCE_CODE 
					FROM REFERENCE_CODE
					WHERE REFERENCE_TYPE_ID = 3 AND VALID_IND_CODE = 1 AND REFERENCE_CODE <> 1
					ORDER BY ORDERING, LABEL";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
	
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($companytype == $arr['REFERENCE_CODE']) {
				echo("<option value='".$arr['REFERENCE_CODE']."' selected>".$arr['LABEL']."</option>");
			}
			else {
				echo("<option value='".$arr['REFERENCE_CODE']."'>".$arr['LABEL']."</option>");
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
        echo("<textarea class='form-control is-valid' id='description' rows='3' ".$disable.">".$description."</textarea>");
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