<?
	require ('connection.php');
	
	try {
		$sql = "	SELECT MENU_ID, MENU_NAME, MENU_DESCRIPTION, MENU_URL, MENU_IMAGE, OBJECT_TYPE
					FROM MENU
					WHERE MENU_ID = ?";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));

		$menu = '';
		$menudescription = '';
		$menuurl = '';
		$menuimage = '';
		$objecttype = '';
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$menu = $arr['MENU_NAME'];
			$menudescription = $arr['MENU_DESCRIPTION'];
			$menuurl = $arr['MENU_URL'];
			$menuimage = $arr['MENU_IMAGE'];
			$objecttype = $arr['OBJECT_TYPE'];
		}
		
		$disable = '';

		if ($mode == 'D') {
			$disable = 'disabled';
		}		
	
		echo("<div class='container'>");
		echo("<div class='modal-body'>");
		
		echo("<form>");

       echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ประเภท Object</label>");
		echo("<select class='selectpicker form-control' id='objecttype' ".$disable." onchange=\"changeDisplay('objecttype', 'object_display', '7', '".$menuid."', '".$key."','".$mode."');\">");
		
		$sql = "	SELECT LABEL, REFERENCE_CODE 
					FROM REFERENCE_CODE
					WHERE REFERENCE_TYPE_ID = 9 AND VALID_IND_CODE = 1
					ORDER BY ORDERING, LABEL";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
	
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($objecttype== $arr['REFERENCE_CODE']) {
				echo("<option value='".$arr['REFERENCE_CODE']."' selected>".$arr['LABEL']."</option>");
			}
			else {
				echo("<option value='".$arr['REFERENCE_CODE']."'>".$arr['LABEL']."</option>");
			}
		}
		
		if ($mode == 'A') {
			$objecttype = 1;
		}
		
		echo("</select>");
        echo("</div>");		

		$sql = "	SELECT LABEL, REFERENCE_CODE 
					FROM REFERENCE_CODE
					WHERE REFERENCE_TYPE_ID = 9 AND VALID_IND_CODE = 1 AND REFERENCE_CODE = ?
					ORDER BY ORDERING, LABEL";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($objecttype));
	
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$objectname = "ชื่อ".$arr['LABEL'];
		}		
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label' id='object_display'>".$objectname."</label>");
        echo("<input type='text' class='form-control is-invalid' id='menuname' value='".$menu."' ".$disable." maxlength='80' size='80' onkeypress=\"return validateinput('2',event.charCode);(event.charCode >= 3565 && event.charCode <= 3630) || (event.charCode == 0) || (event.charCode >= 3632 && event.charCode <= 3662) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode >= 65 && event.charCode <= 90) || event.charCode == 39 || event.charCode == 44 || event.charCode == 45  || event.charCode == 32  || (event.charCode >= 48 && event.charCode <= 57);\">");
        echo("</div>");	
		
		if ($objecttype == 3) {
			echo("<div class='form-group' id = 'url_display' style = 'display:none'>");
		}
		else {
			echo("<div class='form-group' id = 'url_display'>");
		}
        echo("<label for='recipient-name' class='col-form-label'>URL</label>");
        echo("<input type='text' class='form-control is-invalid' id='menuurl' value='".$menuurl."' ".$disable." maxlength='80' size='80'>");
        echo("</div>");	

		if ($objecttype == 3) {
			echo("<div class='form-group' id = 'image_display' style = 'display:none'>");
		}
		else {
			echo("<div class='form-group' id = 'image_display'>");
		}		
        echo("<label for='recipient-name' class='col-form-label'>Image</label>");
        echo("<input type='text' class='form-control is-valid' id='menuimage' value='".$menuimage."' ".$disable." maxlength='80' size='80'>");
        echo("</div>");	

        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>รายละเอียด</label>");
        echo("<textarea class='form-control is-valid' id='description' rows='3' ".$disable.">".$menudescription."</textarea>");
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