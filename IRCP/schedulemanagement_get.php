<?
	require ('connection.php');
	
	try {
		$sql = "	SELECT SCHEDULE_ID, SCHEDULE_NAME, DESCRIPTION, URL, SCHEDULE_TYPE, VALID_IND_CODE, MAIL, PERIOD
					FROM SCHEDULE 
					WHERE SCHEDULE_ID = ?
					ORDER BY SCHEDULE_NAME, DESCRIPTION";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));

		$schedulename = '';
		$description = '';
		$url = '';
		$validindcode = '';
		$mail = '';
		$scheduletype = '';
		$period = '';
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$schedulename = $arr['SCHEDULE_NAME'];
			$description = $arr['DESCRIPTION'];
			$scheduletype = $arr['SCHEDULE_TYPE'];
			$url = $arr['URL'];
			$validindcode = $arr['VALID_IND_CODE'];
			$mail = $arr['MAIL'];
			$period = $arr['PERIOD'];
		}
		
		$disable = '';

		if ($mode == 'D') {
			$disable = 'disabled';
		}		
	
		echo("<div class='container'>");
		echo("<div class='modal-body'>");
		
		echo("<form>");
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ชื่อ Schedule</label>");
        echo("<input type='text' class='form-control is-invalid' id='schedulename' value='".$schedulename."' ".$disable." maxlength='80' size='80'>");
        echo("</div>");	


        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>URL</label>");
        echo("<input type='text' class='form-control is-invalid' id='url' value='".$url."' ".$disable." maxlength='80' size='80' >");
        echo("</div>");	

		echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ประเภท Schedule</label>");
		echo("<select class='selectpicker form-control' id='scheduletype' ".$disable.">");
		
		$sql = "	SELECT LABEL, REFERENCE_CODE 
					FROM REFERENCE_CODE
					WHERE REFERENCE_TYPE_ID = 11 AND VALID_IND_CODE = 1
					ORDER BY ORDERING, LABEL";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
	
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($scheduletype== $arr['REFERENCE_CODE']) {
				echo("<option value='".$arr['REFERENCE_CODE']."' selected>".$arr['LABEL']."</option>");
			}
			else {
				echo("<option value='".$arr['REFERENCE_CODE']."'>".$arr['LABEL']."</option>");
			}			
		}
		echo("</select>");
        echo("</div>");	
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ส่งเมล์</label>");
		echo("<select class='selectpicker form-control' id='mail' ".$disable.">");
		
		$sql = "	SELECT LABEL, REFERENCE_CODE 
					FROM REFERENCE_CODE
					WHERE REFERENCE_TYPE_ID = 2 AND VALID_IND_CODE = 1
					ORDER BY ORDERING, LABEL";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
	
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($mail== $arr['REFERENCE_CODE']) {
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