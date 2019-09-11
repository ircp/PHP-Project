<?
	require ('connection.php');
	
	try {
		$sql = "	SELECT YEAR(ELD.LEAVE_DATE) LEAVE_YEAR
					FROM EMPLOYEE_LEAVE_DETAIL ELD
					LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON ELH.LEAVE_ID = ELD.LEAVE_ID AND SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
					WHERE ELH.EMPLOYEE_ID = ? AND ELH.LEAVE_STATUS IN (2,3) AND YEAR(ELD.LEAVE_DATE) <> 0
					GROUP BY YEAR(ELD.LEAVE_DATE)
					ORDER BY YEAR(ELD.LEAVE_DATE) DESC";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));		
		
		$i = 0;
		
		UNSET ($SUMMARY_YEAR);
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i + 1 ;
			
			if ($i <= 5) {
				$SUMMARY_YEAR[$i] = $arr['LEAVE_YEAR'];
			}
		}
		
		$sql = "	SELECT YEAR(ELD.LEAVE_DATE) LEAVE_YEAR, SUM(ELD.SUMMARY_LEAVE) SUM_LEAVE, ELH.LEAVE_TYPE_ID, LP.LEAVE_NAME
					FROM EMPLOYEE_LEAVE_DETAIL ELD
					LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON ELH.LEAVE_ID = ELD.LEAVE_ID AND SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
					LEFT JOIN LEAVE_POLICY LP ON LP.LEAVE_TYPE_ID = ELH.LEAVE_TYPE_ID 
					WHERE ELH.EMPLOYEE_ID = ? AND ELH.LEAVE_STATUS IN (2,3) AND YEAR(ELD.LEAVE_DATE) <> 0
					GROUP BY YEAR(ELD.LEAVE_DATE), ELH.LEAVE_TYPE_ID, LP.LEAVE_NAME
					ORDER BY YEAR(ELD.LEAVE_DATE), LP.ORDERING";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));	

		UNSET ($SUMMARY_LEAVE);
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$SUMMARY_LEAVE[$arr['LEAVE_YEAR']][$arr['LEAVE_TYPE_ID']]['SUMMARY_LEAVE'] =  $arr['SUM_LEAVE'];
			$SUMMARY_LEAVE[$arr['LEAVE_YEAR']][$arr['LEAVE_TYPE_ID']]['LEAVE_NAME'] =  $arr['LEAVE_NAME'];
		}		
		
		$sql = "	SELECT YEAR(ELD.LEAVE_DATE) LEAVE_YEAR, ELH.LEAVE_START_DATE, ELH.LEAVE_END_DATE, ELH.LEAVE_TYPE_ID, ELH.SUMMARY_LEAVE, LP.LEAVE_NAME, ELD.LEAVE_ID , ELH.LEAVE_STATUS, RC.LABEL LEAVESTATUS
					FROM EMPLOYEE_LEAVE_HISTORY ELH
					LEFT JOIN EMPLOYEE_LEAVE_DETAIL ELD ON ELD.LEAVE_ID = ELH.LEAVE_ID
					LEFT JOIN LEAVE_POLICY LP ON LP.LEAVE_TYPE_ID = ELH.LEAVE_TYPE_ID 
					LEFT JOIN REFERENCE_CODE RC ON RC.REFERENCE_TYPE_ID = 8 AND RC.REFERENCE_CODE = ELH.LEAVE_STATUS
					WHERE SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
					AND ELH.EMPLOYEE_ID = ? AND ELD.LEAVE_DATE <> '' AND ELH.LEAVE_STATUS IN (2,3)
					GROUP BY ELH.LEAVE_ID AND ELH.LEAVE_STATUS IN (2,3) AND YEAR(ELD.LEAVE_DATE) <> 0
					ORDER BY YEAR(ELD.LEAVE_DATE) DESC, ELD.LEAVE_DATE DESC";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));	
		
		UNSET ($RESULT);
		
		$i = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i + 1;
			$leavestart = substr($arr['LEAVE_START_DATE'],8,2)."/".substr($arr['LEAVE_START_DATE'],5,2)."/".((int)substr($arr['LEAVE_START_DATE'],0,4) + 543)." ".substr($arr['LEAVE_START_DATE'],11,5);
			$leaveend = substr($arr['LEAVE_END_DATE'],8,2)."/".substr($arr['LEAVE_END_DATE'],5,2)."/".((int)substr($arr['LEAVE_END_DATE'],0,4) + 543)." ".substr($arr['LEAVE_END_DATE'],11,5);	
			$RESULT[$arr['LEAVE_YEAR']][$arr['LEAVE_ID']]['LEAVE_DATE'] = $leavestart." - ".$leaveend;
			$RESULT[$arr['LEAVE_YEAR']][$arr['LEAVE_ID']]['LEAVE_ID'] = $arr['LEAVE_ID'];
			$RESULT[$arr['LEAVE_YEAR']][$arr['LEAVE_ID']]['LEAVE_NAME'] = $arr['LEAVE_NAME'];
			$RESULT[$arr['LEAVE_YEAR']][$arr['LEAVE_ID']]['SUMMARY_LEAVE'] = $arr['SUMMARY_LEAVE'];
			$RESULT[$arr['LEAVE_YEAR']][$arr['LEAVE_ID']]['LEAVE_STATUS'] = $arr['LEAVESTATUS'];
		}	

		$total_record = $i;

		$sql = "	SELECT EMPLOYEE.EMPLOYEE_ID, EMPLOYEE.EMPLOYEE_CODE, EMPLOYEE.TITLE, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, EMPLOYEE.NICK_NAME,
					EMPLOYEE.TITLE_EN, EMPLOYEE.FIRST_NAME_EN, EMPLOYEE.LAST_NAME_EN, EMPLOYEE.START_DATE, EMPLOYEE.END_DATE, EMPLOYEE.EMPLOYEE_STATUS, 
					EMPLOYEE.DEPARTMENT_ID, EMPLOYEE.POSITION_ID, EMPLOYEE.EMAIL, EMPLOYEE.CUMULATIVE_VACATION,
					RC1.LABEL EMPLOYEESTATUS, DEPARTMENT.DEPARTMENT_NAME, RC2.LABEL POSITION
					FROM EMPLOYEE EMPLOYEE
					LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 5 AND RC1.REFERENCE_CODE = EMPLOYEE.EMPLOYEE_STATUS
					LEFT JOIN REFERENCE_CODE RC2 ON RC2.REFERENCE_TYPE_ID = 4 AND RC2.REFERENCE_CODE = EMPLOYEE.POSITION_ID
					LEFT JOIN DEPARTMENT DEPARTMENT ON DEPARTMENT.DEPARTMENT_ID = EMPLOYEE.DEPARTMENT_ID					
					WHERE SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
					AND EMPLOYEE.EMPLOYEE_ID = ?
					";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));		
		
		$employeeid = '';
		$employeecode = '';
		$employeename = '';
		$employeename_en = '';
		$startdate = '';
		$enddate = '';
		$employee_status = '';
		$departmentid = '';
		$positionid = '';
		$email = '';
		$cumulativevacation = '';
		$employeestatus = '';
		$departmentname = '';
		$position = '';
		$nickname = '';
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			
			$employeeid = $arr['EMPLOYEE_ID'];
			$employeecode = $arr['EMPLOYEE_CODE'];
			$employeename = $arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME'];
			$employeename_en = $arr['TITLE_EN']." ".$arr['FIRST_NAME_EN']." ".$arr['LAST_NAME_EN'];
			$startdate = substr($arr['START_DATE'],8,2)."/".substr($arr['START_DATE'],5,2)."/".((int)substr($arr['START_DATE'],0,4) + 543);
			$enddate =  substr($arr['END_DATE'],8,2)."/".substr($arr['END_DATE'],5,2)."/".((int)substr($arr['END_DATE'],0,4) + 543);
			$employee_status = $arr['EMPLOYEE_STATUS'];
			$departmentid = $arr['DEPARTMENT_ID'];
			$positionid = $arr['POSITION_ID'];
			$email = $arr['EMAIL'];
			$cumulativevacation = (int)$arr['CUMULATIVE_VACATION'];
			$employeestatus = $arr['EMPLOYEESTATUS'];
			$departmentname = $arr['DEPARTMENT_NAME'];
			$position = $arr['POSITION'];
			$nickname = $arr['NICK_NAME'];
			
		}
		
		echo("<ul class = 'nav nav-tabs'>");
//		echo("<li class='nav-item'>");
//		echo("<a class='nav-link active' data-toggle='tab' href='#employee'>รายละเอียดทั่วไป</a>");
//		echo("</li>");
		
		if (isset($SUMMARY_YEAR)) {	
			$i = 0;
			foreach ($SUMMARY_YEAR as $year => $mresult) {
				$i = $i + 1;
				$th_year = (int)$SUMMARY_YEAR[$year] + 543;
				echo("<li class='nav-item'>");
				if ($i ==1) {
					echo("<a class='nav-link active' data-toggle='tab' href='#YEAR_".$SUMMARY_YEAR[$year]."'>ประวัติวันลา ปี ".$th_year."</a>");
				}
				else {
					echo("<a class='nav-link' data-toggle='tab' href='#YEAR_".$SUMMARY_YEAR[$year]."'>ประวัติวันลา ปี ".$th_year."</a>");
				}
				echo("</li>");			
			}
		}

		echo("</ul>");

		
		echo("<div class = 'tab-content'>");
		
		$n = 0;
		if (isset($SUMMARY_YEAR)) {
			foreach ($SUMMARY_YEAR as $year => $mresult) {
				$n = $n + 1;
				if ($n == 1) {
					echo("<div class = 'tab-pane active' id='YEAR_".$SUMMARY_YEAR[$year]."'>");
				}
				else {
					echo("<div class = 'tab-pane fade' id='YEAR_".$SUMMARY_YEAR[$year]."'>");
				}
				echo("<div class='card border'>");
				echo("<div class='card-body'>");
				
				echo("<table class = 'table table-bordered dt-responsive' cellspacing='0' cellspadding = '0' width='100%' >");
				echo("<tbody>");
				
				foreach ($SUMMARY_LEAVE[$SUMMARY_YEAR[$year]] as $leavetypeid => $nresult) {
					echo("<tr>");		
					echo("<td class = 'text-left' >".$nresult['LEAVE_NAME']." ทั้งหมด : <font color = '#17a2b8'>".$nresult['SUMMARY_LEAVE']." วัน</font></td>");	
					echo("</tr>");
				}
				echo("</tbody>");
				echo("</table>");
				
	//			echo("<div id='carouselExampleControls' class = 'carousel slide'  data-ride= 'carousel'>");
	//			echo("<div class = 'carousel-inner'>");
				echo("<div class='card border'>");
				echo("<div class='card-body'>");
	//----------------
				echo("<table class = 'table table-bordered dt-responsive' cellspacing='0' cellspadding = '0' width='100%'>");
				echo("<thead>");
				echo("<tr>");
				echo("<th class = 'text-center' bgcolor='#868e96'>ลำดับที่</th>");
				echo("<th class = 'text-center' bgcolor='#868e96'>ประเภทใบลา</th>");
				echo("<th class = 'text-center' bgcolor='#868e96'>วันที่ลา</th>");
				echo("<th class = 'text-center' bgcolor='#868e96'>จำนวนวันที่ลา</th>");
				echo("<th class = 'text-center' bgcolor='#868e96'>สถานะใบลา</th>");
				echo("</tr>");
				echo("</thead>");
				echo("<tbody>");		
				$i = 0;
				foreach ($RESULT[$SUMMARY_YEAR[$year]] as $x => $xresult) {
					$i = $i + 1;
					echo("<tr>");		
					echo("<td class = 'text-center'>".$i."</td>");	
					echo("<td class = 'text-center'>".$xresult['LEAVE_NAME']."</td>");
					echo("<td class = 'text-center'>". $xresult['LEAVE_DATE']."</td>");
					echo("<td class = 'text-right'>".$xresult['SUMMARY_LEAVE']."</td>");
					echo("<td class = 'text-center'>".$xresult['LEAVE_STATUS']."</td>");
				
					echo("</tr>");
				}
				echo("</tbody>");
				echo("</table>");
	//-----------------			
				echo("</div>");
				echo("</div>");
	//			echo("</div>");
	//			echo("</div>");				
				
				echo("</div>");
				echo("</div>");
				echo("</div>");					
			}
		}
		echo("</div>");
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;
?>