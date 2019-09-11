<?
	require ('connection.php');
	
	try {
		$sql = "	SELECT LEAVE_TYPE_ID, LEAVE_NAME, ALLOWANCE, VALID_IND_CODE, ORDERING
					FROM LEAVE_POLICY
					WHERE LEAVE_TYPE_ID = ?
					ORDER BY ORDERING, LEAVE_NAME";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));

		$leavename = '';
		$allowance = 0;
		$validindcode = '';
		$ordering ='';
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$leavename = $arr['LEAVE_NAME'];
			$allowance = $arr['ALLOWANCE'];
			$validindcode = $arr['VALID_IND_CODE'];
			$ordering = $arr['ORDERING'];
		}
		
		$disable = '';

		if ($mode == 'D') {
			$disable = 'disabled';
		}		
	
		echo("<div class='container'>");
		echo("<div class='modal-body'>");
		
		echo("<form>");
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ประเภทวันลา</label>");
        echo("<input type='text' class='form-control is-invalid' id='leavename' value='".$leavename."' ".$disable." maxlength='80' size='80' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
        echo("</div>");	
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>จำนวนวันที่สามารถลาได้</label>");
        echo("<input type='text' class='form-control is-valid' id='allowance' value='".$allowance."' ".$disable." maxlength='2' size='2'  onkeypress=\"return validateinput('1',event.charCode);(event.charCode >= 48 && event.charCode<= 57) || (event.charCode == 0);\">");
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