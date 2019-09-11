<?
	require ('connection.php');
	
	try {
		$explode = explode("|", $key);
		$departmentid = $explode[0];
		$employeeid = $explode[1];
		
		$employeename = '';
		$position = '';
		
		if ($employeeid != '') {
			$sql = "	SELECT EMPLOYEE.EMPLOYEE_CODE, EMPLOYEE.TITLE, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, EMPLOYEE.EMPLOYEE_ID, RC.LABEL
						FROM DEPARTMENT_EXECUTIVE DE
						LEFT JOIN EMPLOYEE EMPLOYEE ON EMPLOYEE.EMPLOYEE_ID = DE.EMPLOYEE_ID AND SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
						LEFT JOIN REFERENCE_CODE RC ON RC.REFERENCE_CODE = EMPLOYEE.POSITION_ID AND RC.REFERENCE_TYPE_ID = 4
						WHERE DE.DEPARTMENT_ID = ? AND DE.EMPLOYEE_ID = ?
						AND EMPLOYEE.EMPLOYEE_STATUS = 1
						ORDER BY DE.ORDERING, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME"; 

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($departmentid, $employeeid));

			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$employeename = $arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME'];
				$position = $arr['LABEL'];
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
        echo("<label for='recipient-name' class='col-form-label'>ชื่อ-นามสกุล</label>");
		
		echo("<select class='selectpicker form-control' id='employeeid' ".$disable.">");
		
		if ($mode == 'A') {
			
			$sql = "	SELECT EMPLOYEE.EMPLOYEE_CODE, EMPLOYEE.TITLE, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, EMPLOYEE.EMPLOYEE_ID, RC.LABEL
						FROM EMPLOYEE EMPLOYEE
						LEFT JOIN REFERENCE_CODE RC ON RC.REFERENCE_CODE = EMPLOYEE.POSITION_ID AND RC.REFERENCE_TYPE_ID = 4
						WHERE SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
						AND EMPLOYEE.EMPLOYEE_STATUS = 1 AND EMPLOYEE.EMPLOYEE_ID <> 1
						AND EMPLOYEE.EMPLOYEE_ID NOT IN (SELECT DE.EMPLOYEE_ID FROM DEPARTMENT_EXECUTIVE DE WHERE DE.DEPARTMENT_ID = ?)
						ORDER BY EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($departmentid));
		}
		else {
			$sql = "	SELECT EMPLOYEE.EMPLOYEE_CODE, EMPLOYEE.TITLE, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, EMPLOYEE.EMPLOYEE_ID, RC.LABEL
						FROM EMPLOYEE EMPLOYEE
						LEFT JOIN REFERENCE_CODE RC ON RC.REFERENCE_CODE = EMPLOYEE.POSITION_ID AND RC.REFERENCE_TYPE_ID = 4
						WHERE SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
						AND EMPLOYEE.EMPLOYEE_STATUS = 1 AND EMPLOYEE.EMPLOYEE_ID <> 1 AND EMPLOYEE.EMPLOYEE_ID = ?
						ORDER BY EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME";
						
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