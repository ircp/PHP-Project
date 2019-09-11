<?
	require ('connection.php');
	
	try {
		$search = '%'.$parameter."%";
		
		$sql = "	SELECT ELH.LEAVE_ID, ELH.EMPLOYEE_ID, ELH.LEAVE_START_DATE, ELH.LEAVE_END_DATE, ELH.LEAVE_TYPE_ID, ELH.LEAVE_STATUS, ELH.LEAVE_DESCRIPTION, ELH.SUMMARY_LEAVE,
					LP.LEAVE_NAME, EM.TITLE, EM.FIRST_NAME, EM.LAST_NAME, EM.POSITION_ID, RC1.LABEL POSITION
					FROM EMPLOYEE_LEAVE_HISTORY ELH
					LEFT JOIN USER USER ON USER.EMPLOYEE_ID = ELH.APPROVED_ID
					LEFT JOIN LEAVE_POLICY LP ON LP.LEAVE_TYPE_ID = ELH.LEAVE_TYPE_ID
					LEFT JOIN EMPLOYEE EM ON SYSDATE() BETWEEN EM.EFFECTIVE_START_DATE AND EM.EFFECTIVE_END_DATE AND EM.EMPLOYEE_ID = ELH.EMPLOYEE_ID
					LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 4 AND RC1.REFERENCE_CODE = EM.POSITION_ID
					WHERE SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
					AND USER.USER_ID = ? AND ELH.APPROVED_DATE IS NULL AND EM.FIRST_NAME LIKE ?
					ORDER BY EM.FIRST_NAME, EM.LAST_NAME";
		
		
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($userid, $search));		
		
		echo("<div class ='row'>");
		echo("<table class = 'table table-striped display' cellspacing='0' cellspadding = '0' width='100%' id = 'listTable_".$menuid."'>");
		echo("<thead>");
		echo("<tr>");
		echo("<th class = 'text-center'></th>");
		echo("<th class = 'text-center'>ลำดับที่</th>");
		echo("<th class = 'text-center'>ชื่อ-นามสกุล</th>");
//		echo("<th class = 'text-center'>ตำแหน่ง</th>");
		echo("<th class ='text-center'>ประเภทใบลา</th>");
		echo("<th class ='text-center'>วันที่ลา</th>");
		echo("<th class ='text-center'>จำนวนวันลาทั้งหมด</th>");
		echo("<th class ='text-center'>หมายเหตุ</th>");
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
//			echo("<td class = 'text-left'>".$arr['POSITION']."</td>");	
			echo("<td class = 'text-left'>".$arr['LEAVE_NAME']."</td>");		
			$leavestart = substr($arr['LEAVE_START_DATE'],8,2)."/".substr($arr['LEAVE_START_DATE'],5,2)."/".((int)substr($arr['LEAVE_START_DATE'],0,4) + 543)." ".substr($arr['LEAVE_START_DATE'],11,5);
			$leaveend = substr($arr['LEAVE_END_DATE'],8,2)."/".substr($arr['LEAVE_END_DATE'],5,2)."/".((int)substr($arr['LEAVE_END_DATE'],0,4) + 543)." ".substr($arr['LEAVE_END_DATE'],11,5);				
			echo("<td class = 'text-center'>".$leavestart." - ".$leaveend."</td>");	
			echo("<td class = 'text-center'>".$arr['SUMMARY_LEAVE']."</td>");	
			echo("<td class = 'text-left'>".$arr['LEAVE_DESCRIPTION']."</td>");	
			echo("<td  class='text-center'>");
			echo("<div class='btn-group' role='group' aria-label='Basic example'>");
			echo("<button type='button' class='btn  btn-outline-success' id='subtable' >ประวัติการลา</button>");
			if ($_SESSION[session_id()]['MENU'][$menuid]['UPDATE'] == 1) {
				echo("<button type='button' class='btn  btn-outline-success' onclick = \"windowProcess('3', '".$menuid."', 'C', '".$arr['LEAVE_ID']."');\">อนุมัติ</button>");
				echo("<button type='button' class='btn  btn-outline-danger' onclick = \"windowProcess('3', '".$menuid."', 'D', '".$arr['LEAVE_ID']."');\">ไม่อนุมัติ</button>");
			}
			else {
				echo("<button type='button' class='btn  btn-outline-success' onclick = \"windowProcess('3', '".$menuid."', 'C', '".$arr['LEAVE_ID']."');\" disabled>อนุมัติ</button>");	
				echo("<button type='button' class='btn  btn-outline-danger' onclick = \"windowProcess('3', '".$menuid."', 'D', '".$arr['LEAVE_ID']."');\" disabled>ไม่อนุมัติ</button>");
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