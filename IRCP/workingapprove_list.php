<?
	require ('connection.php');
	
	try {
		$search = '%'.$parameter."%";

		if ($userid == 1) {
			$userid = 53;
		}
		
		$sql = "	SELECT WD.EMPLOYEE_ID, WD.WORKING_DAY, WD.WORKING_IN, WD.WORKING_OUT, WD.DESCRIPTION, WD.IN_OUT_ID, 
					WD.INTERVAL_IN, WD.INTERVAL_OUT, EMPLOYEE.TITLE, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME
					FROM WORKING_DAY WD
					LEFT JOIN USER USER ON USER.EMPLOYEE_ID = WD.APPROVED_ID
					LEFT JOIN EMPLOYEE EMPLOYEE ON EMPLOYEE.EMPLOYEE_ID = WD.EMPLOYEE_ID 
					AND SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE AND EMPLOYEE.EMPLOYEE_STATUS = 1
					WHERE SYSDATE() BETWEEN WD.EFFECTIVE_START_DATE AND WD.EFFECTIVE_END_DATE
					AND USER.USER_ID = ? AND APPROVED_DATE IS NULL AND WORKING_STATUS = 2
					ORDER BY EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, WD.WORKING_DAY";
		
		
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($userid));		
		
		echo("<div class ='row'>");
		echo("<table class = 'table table-striped display' cellspacing='0' cellspadding = '0' width='100%' id = 'listTable_".$menuid."'>");
		echo("<thead>");
		echo("<tr>");
		echo("<th class = 'text-center'></th>");
		echo("<th class = 'text-center'>ลำดับที่</th>");
		echo("<th class = 'text-center'>ชือ-นามสกุล</th>");
		echo("<th class = 'text-center'>วันที่</th>");
		echo("<th class = 'text-center'>เวลาเข้า - ออก</th>");
		echo("<th class ='text-center'>จำนวนเวลาเข้าสาย (นาที)</th>");
		echo("<th class ='text-center'>จำนวนเวลาออกเร็ว (นาที)</th>");
		echo("<th class ='text-center'>รายละเอียด</th>");
		echo("<th class ='text-center'>แก้ไข</th>");		
		echo("</tr>");
		echo("</thead>");
		echo("<tbody>");
		$i = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i +1;
			echo("<tr>");
			echo("<td class = 'text-left'>".$arr['EMPLOYEE_ID']."</td>");
			echo("<td class = 'text-center'>".$i."</td>");	
			echo("<td class = 'text-left'>".$arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME']."</td>");
			$workingdate = substr($arr['WORKING_DAY'],8,2)."/".substr($arr['WORKING_DAY'],5,2)."/".((int)substr($arr['WORKING_DAY'],0,4) + 543);
			echo("<td class = 'text-center'>".$workingdate."</td>");	
			$working_in = substr($arr['WORKING_IN'],11,5);
			$working_out = substr($arr['WORKING_OUT'],11,5);	
			echo("<td class = 'text-center'>".$working_in." - ".$working_out."</td>");	
			echo("<td class = 'text-right'>".$arr['INTERVAL_IN']."</td>");
			echo("<td class = 'text-right'>".$arr['INTERVAL_OUT']."</td>");
			echo("<td class = 'text-left'>".$arr['DESCRIPTION']."</td>");		

			echo("<td  class='text-center'>");
			echo("<div class='btn-group' role='group' aria-label='Basic example'>");
			if ($_SESSION[session_id()]['MENU'][$menuid]['UPDATE'] == 1) {
				echo("<button type='button' class='btn  btn-outline-success' onclick = \"windowProcess('3', '".$menuid."', 'C', '".$arr['EMPLOYEE_ID']."|".$arr['WORKING_DAY']."');\">อนุมัติ</button>");
				echo("<button type='button' class='btn  btn-outline-danger' onclick = \"windowProcess('3', '".$menuid."', 'D', '".$arr['EMPLOYEE_ID']."|".$arr['WORKING_DAY']."');\">ไม่อนุมัติ</button>");
			}
			else {
				echo("<button type='button' class='btn  btn-outline-success' onclick = \"windowProcess('3', '".$menuid."', 'C', '".$arr['EMPLOYEE_ID']."|".$arr['WORKING_DAY']."');\" disabled>อนุมัติ</button>");	
				echo("<button type='button' class='btn  btn-outline-danger' onclick = \"windowProcess('3', '".$menuid."', 'D', '".$arr['EMPLOYEE_ID']."|".$arr['WORKING_DAY']."');\" disabled>ไม่อนุมัติ</button>");
			}
			echo("</div>");			
			echo("</td>");			
			echo("</tr>");		
		}	
		echo("</tbody>");
		echo("</table>");			
		echo("</div>");
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;
?>