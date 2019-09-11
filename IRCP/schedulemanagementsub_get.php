<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $key);
		$scheduleid = $explode[0];
		$employeeid = $explode[1];
		
		$disable = '';

		if ($mode == 'D') {
			$disable = 'disabled';
		}		
	
		echo("<div class='container'>");
		echo("<div class='modal-body'>");
		
		echo("<form>");

		echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ชื่อ-นามสกุล</label>");
		echo("<select class='selectpicker form-control' id='employeeid' ".$disable.">");
		
		if ($mode == 'A') {
			if ($userid == 1) {
				$sql = "	SELECT TITLE, FIRST_NAME, LAST_NAME, EMPLOYEE_ID
							FROM EMPLOYEE
							WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
							AND EMPLOYEE_ID NOT IN (SELECT EMPLOYEE_ID FROM SCHEDULE_MAIL WHERE SCHEDULE_ID = ?)
							ORDER BY FIRST_NAME, LAST_NAME";
				$stmt = $conn->prepare($sql);
				$stmt->execute(array($scheduleid));
			}
			else {
				$sql = "	SELECT TITLE, FIRST_NAME, LAST_NAME, EMPLOYEE_ID
							FROM EMPLOYEE
							WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
							AND EMPLOYEE_ID NOT IN (SELECT EMPLOYEE_ID FROM SCHEDULE_MAIL WHERE SCHEDULE_ID = ?) AND EMPLOYEE_ID <> 1
							ORDER BY FIRST_NAME, LAST_NAME";
				$stmt = $conn->prepare($sql);
				$stmt->execute(array($scheduleid));
			}
		}
		else {
			$sql = "	SELECT TITLE, FIRST_NAME, LAST_NAME, EMPLOYEE_ID
						FROM EMPLOYEE
						WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
						AND EMPLOYEE_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($employeeid));			
		}

		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($employeeid== $arr['EMPLOYEE_ID']) {
				echo("<option value='".$arr['EMPLOYEE_ID']."' selected>".$arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME']."</option>");
			}
			else {
				echo("<option value='".$arr['EMPLOYEE_ID']."'>".$arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME']."</option>");
			}			
		}
		echo("</select>");
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