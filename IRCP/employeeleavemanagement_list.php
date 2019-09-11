<?
	require ('connection.php');
	
	try {
		
		$sql = "	SELECT DEPARTMENT_ID, EMPLOYEE.EMPLOYEE_ID
					FROM EMPLOYEE
					LEFT JOIN USER ON USER.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID
					WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
					AND USER.USER_ID = ?";
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($userid));	
		
		$depart = 0;
		$supervisor = 0;
		if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$depart = $arr['DEPARTMENT_ID'];
			$supervisor = $arr['EMPLOYEE_ID'];
		}

		$explode = explode("|", $parameter);
		
		$yearselect = trim($explode[0]);
		$yearselect = (int) $yearselect - 543;
		$monthselect = trim($explode[1]);
		
		if ($depart == 10 || $depart == 12 || $depart == 1) {
			$sql = "	SELECT ELH.LEAVE_START_DATE, ELH.LEAVE_END_DATE, ELH.LEAVE_TYPE_ID, ELH.SUMMARY_LEAVE, LP.LEAVE_NAME, ELD.LEAVE_ID , 
						ELH.LEAVE_STATUS, RC.LABEL LEAVESTATUS, ELH.EMPLOYEE_ID, EM.TITLE, EM.FIRST_NAME, EM.LAST_NAME, DEP.DEPARTMENT_NAME
						FROM EMPLOYEE_LEAVE_HISTORY ELH
						LEFT JOIN EMPLOYEE_LEAVE_DETAIL ELD ON ELD.LEAVE_ID = ELH.LEAVE_ID
						LEFT JOIN LEAVE_POLICY LP ON LP.LEAVE_TYPE_ID = ELH.LEAVE_TYPE_ID 
						LEFT JOIN REFERENCE_CODE RC ON RC.REFERENCE_TYPE_ID = 8 AND RC.REFERENCE_CODE = ELH.LEAVE_STATUS
						LEFT JOIN EMPLOYEE EM ON SYSDATE() BETWEEN EM.EFFECTIVE_START_DATE AND EM.EFFECTIVE_END_DATE AND ELH.EMPLOYEE_ID = EM.EMPLOYEE_ID
						LEFT JOIN DEPARTMENT_EXECUTIVE DE ON DE.DEPARTMENT_ID = EM.DEPARTMENT_ID
						LEFT JOIN DEPARTMENT DEP ON DEP.DEPARTMENT_ID = EM.DEPARTMENT_ID
						WHERE SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
						AND ELD.LEAVE_DATE <> '' AND ELH.LEAVE_STATUS IN (1,2,3) AND YEAR(ELD.LEAVE_DATE) = ?
						AND MONTH(ELD.LEAVE_DATE) = ?
						GROUP BY ELH.LEAVE_ID
						ORDER BY ELD.LEAVE_DATE DESC, DEP.DEPARTMENT_NAME ASC, EM.FIRST_NAME ASC, EM.LAST_NAME ASC";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($yearselect, $monthselect));	
		}
		else {
			$sql = "	SELECT ELH.LEAVE_START_DATE, ELH.LEAVE_END_DATE, ELH.LEAVE_TYPE_ID, ELH.SUMMARY_LEAVE, LP.LEAVE_NAME, ELD.LEAVE_ID , 
						ELH.LEAVE_STATUS, RC.LABEL LEAVESTATUS, ELH.EMPLOYEE_ID, EM.TITLE, EM.FIRST_NAME, EM.LAST_NAME, DEP.DEPARTMENT_NAME
						FROM EMPLOYEE_LEAVE_HISTORY ELH
						LEFT JOIN EMPLOYEE_LEAVE_DETAIL ELD ON ELD.LEAVE_ID = ELH.LEAVE_ID
						LEFT JOIN LEAVE_POLICY LP ON LP.LEAVE_TYPE_ID = ELH.LEAVE_TYPE_ID 
						LEFT JOIN REFERENCE_CODE RC ON RC.REFERENCE_TYPE_ID = 8 AND RC.REFERENCE_CODE = ELH.LEAVE_STATUS
						LEFT JOIN EMPLOYEE EM ON SYSDATE() BETWEEN EM.EFFECTIVE_START_DATE AND EM.EFFECTIVE_END_DATE AND ELH.EMPLOYEE_ID = EM.EMPLOYEE_ID
						LEFT JOIN DEPARTMENT_EXECUTIVE DE ON DE.DEPARTMENT_ID = EM.DEPARTMENT_ID 
						LEFT JOIN DEPARTMENT DEP ON DEP.DEPARTMENT_ID = EM.DEPARTMENT_ID
						WHERE SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
						AND ELD.LEAVE_DATE <> '' AND ELH.LEAVE_STATUS IN (1,2,3) AND YEAR(ELD.LEAVE_DATE) = ?
						AND MONTH(ELD.LEAVE_DATE) = ?
						AND DE.EMPLOYEE_ID = ?
						GROUP BY ELH.LEAVE_ID
						ORDER BY ELD.LEAVE_DATE DESC, DEP.DEPARTMENT_NAME ASC, EM.FIRST_NAME ASC, EM.LAST_NAME ASC";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($yearselect, $monthselect, $supervisor));	
		}
		echo("<div class ='row'>");
		echo("<table class = 'table table-striped display' cellspacing='0' cellspadding = '0' width='100%' id = 'listTable_".$menuid."'>");
		echo("<thead>");
		echo("<tr>");
		echo("<th class = 'text-center'></th>");
		echo("<th class = 'text-center' bgcolor='#868e96'>ลำดับที่</th>");
		echo("<th class = 'text-center' bgcolor='#868e96'>วันที่ลา</th>");
		echo("<th class = 'text-center' bgcolor='#868e96'>แผนก/ฝ่าย</th>");
		echo("<th class = 'text-center' bgcolor='#868e96'>ชื่อ-นามสกุล</th>");
		echo("<th class = 'text-center' bgcolor='#868e96'>ประเภทใบลา</th>");
		
		echo("<th class = 'text-center' bgcolor='#868e96'>จำนวนวันที่ลา</th>");
		echo("<th class = 'text-center' bgcolor='#868e96'>สถานะใบลา</th>");
		echo("</tr>");
		echo("</thead>");
		echo("<tbody>");
		$i = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i +1;
			echo("<tr>");
			echo("<td class = 'text-left'>".$arr['LEAVE_ID']."</td>");
			echo("<td class = 'text-center'>".$i."</td>");	
			$leavestart = substr($arr['LEAVE_START_DATE'],8,2)."/".substr($arr['LEAVE_START_DATE'],5,2)."/".((int)substr($arr['LEAVE_START_DATE'],0,4) + 543)." ".substr($arr['LEAVE_START_DATE'],11,5);
			$leaveend = substr($arr['LEAVE_END_DATE'],8,2)."/".substr($arr['LEAVE_END_DATE'],5,2)."/".((int)substr($arr['LEAVE_END_DATE'],0,4) + 543)." ".substr($arr['LEAVE_END_DATE'],11,5);	
			echo("<td class = 'text-center'>". $leavestart." - ".$leaveend."</td>");
			echo("<td class = 'text-left'>".$arr['DEPARTMENT_NAME']."</td>");
			echo("<td class = 'text-left'>".$arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME']."</td>");
			echo("<td class = 'text-center'>".$arr['LEAVE_NAME']."</td>");

			echo("<td class = 'text-right'>".$arr['SUMMARY_LEAVE']."</td>");
			echo("<td class = 'text-center'>".$arr['LEAVESTATUS']."</td>");			
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