<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $key);
		$referencetypeid = $explode[0];
		$referencecode = $explode[1];
		
		$label = '';
		$abbreviation = '';
		$description = '';
		$ordering = '';
		$validindcode = '';
		
		if ($referencecode != '') {
			$sql = "	SELECT REFERENCE_CODE, LABEL, ABBREVIATION, DESCRIPTION, ORDERING, VALID_IND_CODE
						FROM REFERENCE_CODE
						WHERE REFERENCE_TYPE_ID = ? AND REFERENCE_CODE = ?
						ORDER BY REFERENCE_CODE ASC";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($referencetypeid, $referencecode));

			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$label = $arr['LABEL'];
				$abbreviation = $arr['ABBREVIATION'];
				$description = $arr['DESCRIPTION'];
				$ordering = $arr['ORDERING'];
				$validindcode = $arr['VALID_IND_CODE'];
			}
		}
		
		$disable = '';

		if ($mode == 'D') {
			$disable = 'disabled';
		}		
	
		echo("<div class='container'>");
		echo("<div class='modal-body'>");
		
		echo("<form>");
		
		echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>Reference Code</label>");
		if ($mode == 'C' || $mode == 'D') {
			echo("<input type='text' class='form-control is-invalid' id='referencecode' value='".$referencecode."' disabled maxlength='10' size='10' onkeypress=\"return validateinput('1',event.charCode);(event.charCode >= 48 && event.charCode<= 57) || (event.charCode == 0);\">");
		}
		else {
			echo("<input type='text' class='form-control is-invalid' id='referencecode' value='".$referencecode."'  maxlength='10' size='10' onkeypress=\"return validateinput('1',event.charCode);(event.charCode >= 48 && event.charCode<= 57) || (event.charCode == 0);\">");
		}
        echo("</div>");	
		
		echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ชื่อ</label>");
        echo("<input type='text' class='form-control is-invalid' id='label' value='".$label."' ".$disable." maxlength='80' size='80'>");
        echo("</div>");	
		
		echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ชื่อย่อ</label>");
        echo("<input type='text' class='form-control is-valid' id='abbreviation' value='".$abbreviation."' ".$disable." maxlength='20' size='20'>");
        echo("</div>");	

        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>Order</label>");
        echo("<input type='text' class='form-control is-valid' id='ordering' value='".$ordering."' ".$disable." maxlength='2' size='2' onkeypress=\"return validateinput('1',event.charCode);(event.charCode >= 48 && event.charCode<= 57) || (event.charCode == 0);\">");
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
		echo("<button type='button' class='btn btn-info' onclick = \"windowProcess('4', '".$menuid."', '".$mode."', '".$key."');\">บันทึก</button>");
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