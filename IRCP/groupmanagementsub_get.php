<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $key);
		$groupid = $explode[0];
		$menu = $explode[1];
		
		$sql = "	SELECT GROUP_MENU.MENU_ID, GROUP_MENU.PERMISSION, GROUP_MENU.FIRST_MENU, GROUP_MENU.MENU_LEVEL, GROUP_MENU.MENU_ORDER, GROUP_MENU.PARENT_MENU_ID, GROUP_MENU.VALID_IND_CODE,
					MENU.MENU_NAME, MENU_1.MENU_NAME PARENT_MENU, MENU.OBJECT_TYPE
					FROM GROUP_MENU GROUP_MENU
					LEFT JOIN MENU MENU ON MENU.MENU_ID = GROUP_MENU.MENU_ID
					LEFT JOIN MENU MENU_1 ON MENU_1.MENU_ID = GROUP_MENU.PARENT_MENU_ID
					WHERE GROUP_MENU.GROUP_ID = ? AND GROUP_MENU.MENU_ID = ?
					ORDER BY MENU.MENU_NAME ASC";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($groupid, $menu));

		$permission = '';
		$firstmenu = '';
		$menulevel = '';
		$menuorder = '';
		$parentmenuid = '';
		$validindcode = '';
		$menuname = '';
		$objecttype = '';
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$permission = $arr['PERMISSION'];
			$firstmenu = $arr['FIRST_MENU'];
			$menulevel = $arr['MENU_LEVEL'];
			$menuorder = $arr['MENU_ORDER'];
			$parentmenuid = $arr['PARENT_MENU_ID'];
			$validindcode = $arr['VALID_IND_CODE'];
			$menuname = $arr['MENU_NAME'];
			$objecttype = $arr['OBJECT_TYPE'];
		}
		
		$viewflag = 0;
		$updateflag =0;
		$deleteflag = 0;
		$addflag = 0;
		
		if ($permission >= (pow(2,4))) {
			$viewflag = 1;
			$permission = $permission - (pow(2,4));
		}
		if ($permission >= (pow(2,3))) {
			$updateflag = 1;
			$permission = $permission - (pow(2,3));
		}
		if ($permission >= (pow(2,2))) {
			$deleteflag = 1;
			$permission = $permission - (pow(2,2));
		}
		if ($permission >= (pow(2,1))) {
			$addflag = 1;
			$permission = $permission - (pow(2,1));
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
		echo("<select class='selectpicker form-control' id='objecttype' onchange=\"changeDropdown('objecttype', 'parentmenuid', '6', '".$menuid."', '".$groupid."|".$menu."','".$mode."');changeDisplay('objecttype', 'parentmenu_label', '7', '".$menuid."', '".$groupid."|".$menu."','".$mode."');changeDropdown('objecttype', 'menuid', '6', '".$menuid."', '".$groupid."|".$menu."','".$mode."');changeDisplay('objecttype', 'menu_name', '7', '".$menuid."', '".$groupid."|".$menu."','".$mode."');\" ".$disable.">");
		
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
		
		if ($objecttype == 1) {
			echo("<div class='form-group' id = 'menu_level'>");
		}
		else {
			echo("<div class='form-group' id = 'menu_level' style='display:none'>");
		}
		echo("<label for='recipient-name' class='col-form-label'>ประเภทเมนู</label>");
		echo("<select class='selectpicker form-control' id='menulevel' onchange=\"changeDropdown('menulevel', 'parentmenuid', '6', '".$menuid."', '".$groupid."|".$menu."','".$mode."');changeDropdown('menulevel', 'menuid', '6', '".$menuid."', '".$groupid."|".$menu."','".$mode."');\" ".$disable.">");
		
		$sql = "	SELECT LABEL, REFERENCE_CODE 
					FROM REFERENCE_CODE
					WHERE REFERENCE_TYPE_ID = 10 AND VALID_IND_CODE = 1
					ORDER BY ORDERING, LABEL";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
	
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($menulevel== $arr['REFERENCE_CODE']) {
				echo("<option value='".$arr['REFERENCE_CODE']."' selected>".$arr['LABEL']."</option>");
			}
			else {
				echo("<option value='".$arr['REFERENCE_CODE']."'>".$arr['LABEL']."</option>");
			}
		}
		
		if ($mode == 'A') {
			$menulevel = 1;
		}

		echo("</select>");		
		echo("</div>");	

		if ($menulevel == 1) {
			echo("<div class='form-group' id ='parent_menu' style='display:none'>");
		}
		else {
			echo("<div class='form-group' id ='parent_menu'>");
		}
		
		echo("<label for='recipient-name' class='col-form-label' id='parentmenu_label'>Parent</label>");
		echo("<select class='selectpicker form-control' id='parentmenuid' ".$disable.">");
		
		if ($menulevel == 1) {
			echo("<option value=''></option>");
		}
		else if ($menulevel == 2){
			$sql = "	SELECT MENU_ID, MENU_NAME 
						FROM MENU
						WHERE MENU_ID IN (SELECT MENU_ID FROM GROUP_MENU WHERE GROUP_ID = ? AND MENU_LEVEL = 1) AND MENU_ID <> ?
						ORDER BY MENU_NAME";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($groupid, $menu));
		
			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				if ($parentmenuid== $arr['MENU_ID']) {
					echo("<option value='".$arr['MENU_ID']."' selected>".$arr['MENU_NAME']."</option>");
				}
				else {
					echo("<option value='".$arr['MENU_ID']."'>".$arr['MENU_NAME']."</option>");
				}			
			}			
		}
		else if ($menulevel == 3){
			$sql = "	SELECT MENU_ID, MENU_NAME 
						FROM MENU
						WHERE MENU_ID IN (SELECT MENU_ID FROM GROUP_MENU WHERE GROUP_ID = ? AND MENU_URL <> '' AND MENU_LEVEL IN (1,2)) AND MENU_ID <> ?
						ORDER BY MENU_NAME";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($groupid, $menu));
		
			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				if ($parentmenuid== $arr['MENU_ID']) {
					echo("<option value='".$arr['MENU_ID']."' selected>".$arr['MENU_NAME']."</option>");
				}
				else {
					echo("<option value='".$arr['MENU_ID']."'>".$arr['MENU_NAME']."</option>");
				}			
			}			
		}
		else if ($menulevel == 4) {
			$sql = "	SELECT MENU_ID, MENU_NAME 
						FROM MENU
						WHERE MENU_ID IN (SELECT MENU_ID FROM GROUP_MENU WHERE GROUP_ID = ? AND MENU_LEVEL = 3) AND MENU_ID <> ?
						ORDER BY MENU_NAME";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($groupid, $menu));
		
			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				if ($parentmenuid== $arr['MENU_ID']) {
					echo("<option value='".$arr['MENU_ID']."' selected>".$arr['MENU_NAME']."</option>");
				}
				else {
					echo("<option value='".$arr['MENU_ID']."'>".$arr['MENU_NAME']."</option>");
				}			
			}				
		}
		
		echo("</select>");
        echo("</div>");	
		
       echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label' id = 'menu_name'>ชื่อเมนู</label>");
		
		if ($mode == 'A') {
			echo("<select class='selectpicker form-control' id='menuid' ".$disable.">");
		}
		else {
			echo("<select class='selectpicker form-control' id='menuid' disabled>");
		}
		
		if ($mode == 'A' || $menulevel == 1) {
			if ($mode == 'A') {
				$sql = "	SELECT MENU_ID, MENU_NAME 
							FROM MENU
							WHERE MENU_ID NOT IN (SELECT MENU_ID FROM GROUP_MENU WHERE GROUP_ID = ?)
							ORDER BY MENU_NAME";	

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($groupid));	
			}
			else {
				$sql = "	SELECT MENU_ID, MENU_NAME 
							FROM MENU
							WHERE MENU_ID NOT IN (SELECT MENU_ID FROM GROUP_MENU WHERE GROUP_ID = ?) || MENU_ID = ?
							ORDER BY MENU_NAME";	

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($groupid, $menu));					
			}
		}
		else {
			$sql = "	SELECT MENU_ID, MENU_NAME 
						FROM MENU
						WHERE MENU_ID NOT IN (SELECT MENU_ID FROM GROUP_MENU WHERE GROUP_ID = ?) || MENU_ID = ?
						ORDER BY MENU_NAME";	

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($groupid, $menu));			
		}
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($menu == $arr['MENU_ID']) {
				echo("<option value='".$arr['MENU_ID']."' selected>".$arr['MENU_NAME']."</option>");
			}
			else {
				echo("<option value='".$arr['MENU_ID']."'>".$arr['MENU_NAME']."</option>");
			}			
		}	
			
		echo("</select>");
        echo("</div>");	
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>Menu Order</label>");
        echo("<input type='text' class='form-control is-valid' id='menuorder' value='".$menuorder."' ".$disable." maxlength='2' size='2' onkeypress=\"return validateinput('1',event.charCode);(event.charCode >= 48 && event.charCode<= 57) || (event.charCode == 0);\">");
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
		echo("<div class='form-check '>");
		
		$firstmenuid = '';
		
		$sql = " SELECT MENU_ID FROM GROUP_MENU
					WHERE FIRST_MENU = 1 AND GROUP_ID = ?";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($groupid));
	
		if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$firstmenuid = $arr['MENU_ID'];
		}
		
		if ($firstmenuid == '' && $mode == 'A') {
			echo("<input class='form-check-input' type='checkbox' id='firstmenu' checked>");
		}
		else {
			if ($firstmenu == 1) {
				echo("<input class='form-check-input' type='checkbox' id='firstmenu' checked>");
			}
			else {
				if ($firstmenuid == '') {
					echo("<input class='form-check-input' type='checkbox' id='firstmenu' >");
				}
				else {
					echo("<input class='form-check-input' type='checkbox' id='firstmenu' disabled>");
				}
			}
		}
		
		echo("<label class='form-check-label' for='inlineCheckbox1'>First Menu</label>");
		echo("</div>");
		echo("</div>");
		
		echo("<div class='form-group'>");
		echo("<div class='form-check form-check-inline'>");
		
		if ($mode == 'A') {
			echo("<input class='form-check-input' type='checkbox' id='add' >");
		}
		else {
			if ($addflag == 1) {
				echo("<input class='form-check-input' type='checkbox' id='add' checked ".$disable.">");
			}
			else {
				echo("<input class='form-check-input' type='checkbox' id='add' ".$disable.">");
			}
		}
		
		echo("<label class='form-check-label' for='inlineCheckbox1'>Add</label>");
		echo("</div>");
		echo("<div class='form-check form-check-inline'>");
		if ($mode == 'A') {
			echo("<input class='form-check-input' type='checkbox' id='update' >");
		}
		else {
			if ($updateflag == 1) {
				echo("<input class='form-check-input' type='checkbox' id='update' checked ".$disable.">");
			}
			else {
				echo("<input class='form-check-input' type='checkbox' id='update' ".$disable.">");
			}
		}
		echo("<label class='form-check-label' for='inlineCheckbox1'>Update</label>");
		echo("</div>");
		echo("<div class='form-check form-check-inline'>");
		if ($mode == 'A') {
			echo("<input class='form-check-input' type='checkbox' id='delete' >");
		}
		else {
			if ($deleteflag == 1) {
				echo("<input class='form-check-input' type='checkbox' id='delete' checked ".$disable.">");
			}
			else {
				echo("<input class='form-check-input' type='checkbox' id='delete' ".$disable.">");
			}
		}
		echo("<label class='form-check-label' for='inlineCheckbox1'>Delete</label>");
		echo("</div>");
		echo("<div class='form-check form-check-inline'>");
		echo("<input class='form-check-input' type='checkbox' id='view' checked disabled>");
		echo("<label class='form-check-label' for='inlineCheckbox1'>View</label>");
		echo("</div>");	
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