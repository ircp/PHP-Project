<?
	require ('connection.php');
	
	try {
		
		if ($parameter == '') {
			$parameter = date("Y")."-".date("m")."-".date("d");
		}
		
		$sql = "	SELECT ELD.LEAVE_ID, ELD.LEAVE_DATE, ELD.SUMMARY_LEAVE SUM_LEAVE, ELH.LEAVE_TYPE_ID, ELH.LEAVE_STATUS, ELH.LEAVE_START_DATE, ELH.LEAVE_END_DATE,
					ELH.SUMMARY_LEAVE, RC1.LABEL LEAVESTATUS, LP.LEAVE_NAME,
					EMPLOYEE.TITLE, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, ELH.EMPLOYEE_ID, ELH.LEAVE_DESCRIPTION
					FROM EMPLOYEE_LEAVE_DETAIL ELD
					LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE AND ELH.LEAVE_ID = ELD.LEAVE_ID
					LEFT JOIN USER USER ON USER.EMPLOYEE_ID = ELH.EMPLOYEE_ID
					LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 8 AND RC1.REFERENCE_CODE = ELH.LEAVE_STATUS
					LEFT JOIN LEAVE_POLICY LP ON LP.LEAVE_TYPE_ID = ELH.LEAVE_TYPE_ID
					LEFT JOIN EMPLOYEE EMPLOYEE ON SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE AND EMPLOYEE.EMPLOYEE_ID = ELH.EMPLOYEE_ID
					WHERE USER.USER_ID = ? AND ELD.LEAVE_DATE = ?";
		
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($userid,$parameter));

		$leave_id = '';
		$leave_date = '';
		$sum_leave = '';
		$leave_status = '';
		$leavestartdate = '';
		$leaveenddate = '';
		$summary_leave = 1;
		$leavestatus = '';
		$leavename = '';
		$title = '';
		$firstname = '';
		$lastname = '';
		$mode = 'A';
		$employeeid = '';
		$description = '';
		
		$mode = 'A';
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$leave_id = $arr['LEAVE_ID'];
			$leave_date = $arr['LEAVE_DATE'];
			$sum_leave = $arr['SUM_LEAVE'];
			$leave_status = $arr['LEAVE_STATUS'];
			$leavestartdate = $arr['LEAVE_START_DATE'];
			$leaveenddate = $arr['LEAVE_END_DATE'];
			$summary_leave = $arr['SUMMARY_LEAVE'];
			$leavestatus = $arr['LEAVESTATUS'];
			$leavename = $arr['LEAVE_NAME'];
			$title = $arr['TITLE'];
			$firstname = $arr['FIRST_NAME'];
			$lastname = $arr['LAST_NAME'];
			$employeeid = $arr['EMPLOYEE_ID'];
			$description = $arr['LEAVE_DESCRIPTION'];
			$leavetypeid = $arr['LEAVE_TYPE_ID'];
			$mode = 'C';
		}
		
		if ($mode == 'A')  {
			$sql = "	SELECT EMPLOYEE_ID FROM USER
						WHERE USER_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid));
			
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$employeeid = $arr['EMPLOYEE_ID'];
			}
		}
		
		if ($mode == 'A') {
			$leavestart = substr($parameter,8,2)."/".substr($parameter,5,2)."/".((int)substr($parameter,0,4) + 543)." "."08:00";
			$leaveend = substr($parameter,8,2)."/".substr($parameter,5,2)."/".((int)substr($parameter,0,4) + 543)." "."17:00";
		}
		else {
			$leavestart = substr($leavestartdate,8,2)."/".substr($leavestartdate,5,2)."/".((int)substr($leavestartdate,0,4) + 543)." ".substr($leavestartdate,11,5);
			$leaveend = substr($leaveenddate,8,2)."/".substr($leaveenddate,5,2)."/".((int)substr($leaveenddate,0,4) + 543)." ".substr($leaveenddate,11,5);			
		}
		

		$disable = '';

		if ($mode == 'D' || $leave_status == 3 || $leave_status == 2) {
			$disable = 'disabled';
		}	
		
		echo("<div class='container'>");
		echo("<div class='modal-body'>");
		
		echo("<form>");
		
		echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ชื่อพนักงาน</label>");
		echo("<select class='selectpicker form-control' id='employeeid' disabled>");
		
		$sql = "	SELECT TITLE, FIRST_NAME, LAST_NAME, EMPLOYEE_ID
					FROM EMPLOYEE
					WHERE SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
					ORDER BY FIRST_NAME, LAST_NAME ";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
	
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($employeeid == $arr['EMPLOYEE_ID']) {
				echo("<option value='".$arr['EMPLOYEE_ID']."' selected>".$arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME']."</option>");
			}
			else {
				echo("<option value='".$arr['EMPLOYEE_ID']."'>".$arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME']."</option>");
			}			
		}
		echo("</select>");
        echo("</div>");		

		
		echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ประเภทใบลา</label>");
		echo("<select class='selectpicker form-control' id='leavetypeid' ".$disable.">");
		
		$sql = "	SELECT LEAVE_TYPE_ID, LEAVE_NAME
					FROM LEAVE_POLICY
					WHERE VALID_IND_CODE = 1
					ORDER BY ORDERING ";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
	
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($leavetypeid == $arr['LEAVE_TYPE_ID']) {
				echo("<option value='".$arr['LEAVE_TYPE_ID']."' selected>".$arr['LEAVE_NAME']."</option>");
			}
			else {
				echo("<option value='".$arr['LEAVE_TYPE_ID']."'>".$arr['LEAVE_NAME']."</option>");
			}			
		}
		echo("</select>");
        echo("</div>");	

        echo("<div class='form-row'>");
		 echo("<div class='form-group col-md-6'>");
        echo("<label for='recipient-name' class='col-form-label'>วันลาเริ่มต้น</label>");
		echo("<input type='text' class='form-control is-invalid' id='startdate' placeholder='วันลาเริ่มต้น' value = '".$leavestart."' maxlength='20' size='20' ".$disable.">");		
        echo("</div>");	
		 echo("<div class='form-group col-md-6'>");
        echo("<label for='recipient-name' class='col-form-label'>วันลาสิ้นสุด</label>");
		echo("<input type='text' class='form-control is-invalid' id='enddate' placeholder='วันลาสิ้นสุด' value = '".$leaveend."' maxlength='20' size='20' ".$disable.">");		
        echo("</div>");	
		echo("</div>");	

        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>จำนวนวันที่ลา</label>");
        echo("<input type='text' class='form-control is-valid' id='sumleave' value='".$summary_leave."' disabled>");
        echo("</div>");

        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>รายละเอียด</label>");
        echo("<textarea class='form-control is-valid' id='description' rows='3' ".$disable."  maxlength='200'>".$description."</textarea>");
        echo("</div>");	
		
		echo("</form>");
		echo("</div>");
		echo("<div class='modal-footer'>");
		echo("<div class='btn-group' role='group' aria-label='Basic example'>");
		echo("<button type='button' class='btn btn-secondary' data-dismiss='modal'  id='closeButton'>ปิด</button>");
		if ($mode != 'A' && $leave_status != 3 && $leave_status != 2) {
			echo("<button type='button' class='btn btn-danger' onclick = \"windowProcess('3', '".$menuid."', 'D', '".$leave_id."');\">ลบ</button>");
		}
		if ($leave_status != 3 && $leave_status != 2) {
			echo("<button type='button' class='btn btn-info' onclick = \"windowProcess('3', '".$menuid."', 'A', '".$leave_id."');\">ส่งอนุมัติ</button>");
		}
		
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