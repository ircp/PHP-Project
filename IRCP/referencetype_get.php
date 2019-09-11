<?
	require ('connection.php');
	
	try {
		$sql = "	SELECT REFERENCE_TYPE_ID, LABEL, ABBREVIATION, DESCRIPTION, ORDERING
					FROM REFERENCE_TYPE
					WHERE REFERENCE_TYPE_ID = ?
					ORDER BY REFERENCE_TYPE_ID";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));

		$label = '';
		$abbreviation = '';
		$description = '';
		
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$label = $arr['LABEL'];
			$abbreviation = $arr['ABBREVIATION'];
			$description = $arr['DESCRIPTION'];
		}
		
		$disable = '';

		if ($mode == 'D') {
			$disable = 'disabled';
		}		
	
		echo("<div class='container'>");
		echo("<div class='modal-body'>");
		
		echo("<form>");
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ชื่อ</label>");
        echo("<input type='text' class='form-control is-invalid' id='label' value='".$label."' ".$disable." maxlength='80' size='80'>");
        echo("</div>");	

        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ชื่อย่อ</label>");
        echo("<input type='text' class='form-control is-valid' id='abbreviation' value='".$abbreviation."' ".$disable." maxlength='20' size='20' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
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