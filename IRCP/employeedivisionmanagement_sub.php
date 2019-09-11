<?
	require ('connection.php');
	
	try {
		$currentyear = date("Y");
		$currentmonth = (int)date("m");

		$sql = "	SELECT YEAR(WD.WORKING_DAY) WORKING_YEAR
					FROM WORKING_DAY WD
					WHERE SYSDATE() BETWEEN WD.EFFECTIVE_START_DATE AND WD.EFFECTIVE_END_DATE
					AND WD.EMPLOYEE_ID = ?
					GROUP BY YEAR(WD.WORKING_DAY)
					ORDER BY YEAR(WD.WORKING_DAY)";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));							

		$i = 0;
		
		UNSET ($SUM_WORKING_YEAR);
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i + 1 ;
			
			if ($i <= 3) {
				$SUM_WORKING_YEAR[$i] = $arr['WORKING_YEAR'];
			}
		}
		
		$sql = "	SELECT WD.EMPLOYEE_ID, WD.WORKING_DAY, WD.WORKING_STATUS, WD.WORKING_IN, WD.WORKING_OUT, WD.DESCRIPTION, WD.IN_OUT_ID, WD.LEAVE_ID, YEAR(WD.WORKING_DAY) WD_YEAR, MONTH(WD.WORKING_DAY) WD_MONTH, 
					RC1.LABEL WORKINGSTATUS, LP.LEAVE_NAME, WD.INTERVAL_IN, WD.INTERVAL_OUT
					FROM WORKING_DAY WD
					LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 14 AND RC1.REFERENCE_CODE = WD.WORKING_STATUS
					LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE AND ELH.LEAVE_STATUS IN (2,3) AND ELH.LEAVE_ID = WD.LEAVE_ID
					LEFT JOIN LEAVE_POLICY LP ON LP.LEAVE_TYPE_ID = ELH.LEAVE_TYPE_ID
					WHERE SYSDATE() BETWEEN WD.EFFECTIVE_START_DATE AND WD.EFFECTIVE_END_DATE
					AND WD.EMPLOYEE_ID = ?
					ORDER BY  YEAR(WD.WORKING_DAY),  MONTH(WD.WORKING_DAY), WD.WORKING_DAY";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));		

		UNSET ($SUMMARY_WORKING);
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$working_in = '';
			$working_out = '';
			
			if ($arr['IN_OUT_ID'] == '' && $arr['LEAVE_ID'] == '') {
				$SUMMARY_WORKING[$arr['WD_YEAR']][$arr['WD_MONTH']][$arr['WORKING_DAY']]['WORKING_TIME'] = "";
				$SUMMARY_WORKING[$arr['WD_YEAR']][$arr['WD_MONTH']][$arr['WORKING_DAY']]['WORKING_IN'] = '';
				$SUMMARY_WORKING[$arr['WD_YEAR']][$arr['WD_MONTH']][$arr['WORKING_DAY']]['WORKING_OUT'] = '';
			}
			else {
				if ($arr['IN_OUT_ID'] != '') {
					$working_in = substr($arr['WORKING_IN'],11,5);
					$working_out = substr($arr['WORKING_OUT'],11,5);	
					$SUMMARY_WORKING[$arr['WD_YEAR']][$arr['WD_MONTH']][$arr['WORKING_DAY']]['WORKING_TIME'] = $working_in." - ".$working_out;
					$SUMMARY_WORKING[$arr['WD_YEAR']][$arr['WD_MONTH']][$arr['WORKING_DAY']]['WORKING_IN'] = $arr['WORKING_IN'];
					$SUMMARY_WORKING[$arr['WD_YEAR']][$arr['WD_MONTH']][$arr['WORKING_DAY']]['WORKING_OUT'] = $arr['WORKING_OUT'];					
				}
				if ($arr['LEAVE_ID'] != '') {
					$SUMMARY_WORKING[$arr['WD_YEAR']][$arr['WD_MONTH']][$arr['WORKING_DAY']]['WORKING_TIME'] = $arr['LEAVE_NAME'];
				}
			}
			
			$SUMMARY_WORKING[$arr['WD_YEAR']][$arr['WD_MONTH']][$arr['WORKING_DAY']]['WORKING_DAY'] =  substr($arr['WORKING_DAY'],8,2)."/".substr($arr['WORKING_DAY'],5,2)."/".((int)substr($arr['WORKING_DAY'],0,4) + 543);
			$SUMMARY_WORKING[$arr['WD_YEAR']][$arr['WD_MONTH']][$arr['WORKING_DAY']]['IN_OUT_ID'] = $arr['IN_OUT_ID'];
			$SUMMARY_WORKING[$arr['WD_YEAR']][$arr['WD_MONTH']][$arr['WORKING_DAY']]['WORKING_STATUS'] = $arr['WORKING_STATUS'];
			$SUMMARY_WORKING[$arr['WD_YEAR']][$arr['WD_MONTH']][$arr['WORKING_DAY']]['WORKINGSTATUS'] = $arr['WORKINGSTATUS'];
			$SUMMARY_WORKING[$arr['WD_YEAR']][$arr['WD_MONTH']][$arr['WORKING_DAY']]['DESCRIPTION'] = $arr['DESCRIPTION'];		
			$SUMMARY_WORKING[$arr['WD_YEAR']][$arr['WD_MONTH']][$arr['WORKING_DAY']]['IN_OUT_ID'] = $arr['IN_OUT_ID'];
			$SUMMARY_WORKING[$arr['WD_YEAR']][$arr['WD_MONTH']][$arr['WORKING_DAY']]['LEAVE_ID'] = $arr['LEAVE_ID'];
			$SUMMARY_WORKING[$arr['WD_YEAR']][$arr['WD_MONTH']][$arr['WORKING_DAY']]['INTERVAL_IN'] = $arr['INTERVAL_IN'];
			$SUMMARY_WORKING[$arr['WD_YEAR']][$arr['WD_MONTH']][$arr['WORKING_DAY']]['INTERVAL_OUT'] = $arr['INTERVAL_OUT'];
		}	
		
		$sql = "	SELECT YEAR(ELD.LEAVE_DATE) LEAVE_YEAR
					FROM EMPLOYEE_LEAVE_DETAIL ELD
					LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON ELH.LEAVE_ID = ELD.LEAVE_ID AND SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
					WHERE ELH.EMPLOYEE_ID = ? AND ELH.LEAVE_STATUS IN (1, 2,3) AND YEAR(ELD.LEAVE_DATE) <> 0
					GROUP BY YEAR(ELD.LEAVE_DATE)
					ORDER BY YEAR(ELD.LEAVE_DATE) DESC";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));		
		
		$i = 0;
		
		UNSET ($SUMMARY_YEAR);
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i + 1 ;
			
			if ($i <= 3) {
				$SUMMARY_YEAR[$i] = $arr['LEAVE_YEAR'];
			}
		}
		
		$sql = "	SELECT YEAR(ELD.LEAVE_DATE) LEAVE_YEAR, SUM(ELD.SUMMARY_LEAVE) SUM_LEAVE, ELH.LEAVE_TYPE_ID, LP.LEAVE_NAME
					FROM EMPLOYEE_LEAVE_DETAIL ELD
					LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON ELH.LEAVE_ID = ELD.LEAVE_ID AND SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
					LEFT JOIN LEAVE_POLICY LP ON LP.LEAVE_TYPE_ID = ELH.LEAVE_TYPE_ID 
					WHERE ELH.EMPLOYEE_ID = ? AND ELH.LEAVE_STATUS IN (1,2,3) AND YEAR(ELD.LEAVE_DATE) <> 0
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
					AND ELH.EMPLOYEE_ID = ? AND ELD.LEAVE_DATE <> '' AND ELH.LEAVE_STATUS IN (1,2,3)
					GROUP BY ELH.LEAVE_ID
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
		echo("<li class='nav-item'>");
		echo("<a class='nav-link active' data-toggle='tab' href='#employee'>รายละเอียดทั่วไป</a>");
		echo("</li>");
		
		if (isset($SUMMARY_YEAR)) {		
			foreach ($SUMMARY_YEAR as $year => $mresult) {
				$th_year = (int)$SUMMARY_YEAR[$year] + 543;
				echo("<li class='nav-item'>");
				echo("<a class='nav-link' data-toggle='tab' href='#YEAR_".$SUMMARY_YEAR[$year]."'>ประวัติวันลา ปี ".$th_year."</a>");
				echo("</li>");			
			}
		}
		
		if (isset($SUM_WORKING_YEAR)) {		
			foreach ($SUM_WORKING_YEAR as $year => $mresult) {
				$th_year = (int)$SUM_WORKING_YEAR[$year] + 543;
				echo("<li class='nav-item dropdown'>");
				echo("<a class='nav-link dropdown-toggle' href='#' id='navbarDropdownMenuLink' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>ประวัติเวลาทำงาน ปี ".$th_year."</a>");
				echo("<div class='dropdown-menu'>");
				if ($currentyear == $SUM_WORKING_YEAR[$year] ) {
					$sum_month = $currentmonth + 1;
				}
				else {
					$sum_month = 12;
				}
				
				for ($x = 1; $x <= $sum_month; $x++) {
					switch ($x) {
						case '01': 
							$monthname =  'มกราคม';
						break;
						case '02': 
							$monthname =  'กุมภาพันธ์';
						break;
						case '03': 
							$monthname =  'มีนาคม';
						break;
						case '04': 
							$monthname =  'เมษายน';
						break;
						case '05': 
							$monthname =  'พฤษภาคม';
						break;
						case '06': 
							$monthname =  'มิถุนายน';
						break;
						case '07': 
							$monthname =  'กรกฎาคม';
						break;
						case '08': 
							$monthname =  'สิงหาคม';
						break;
						case '09': 
							$monthname =  'กันยายน';
						break;
						case '10': 
							$monthname =  'ตุลาคม';
						break;
						case '11': 
							$monthname =  'พฤศจิกายน';
						break;
						case '12': 
							$monthname =  'ธันวาคม';
						break;
					}
					echo("<a class='dropdown-item' data-toggle='tab' href='#WYEAR_".$SUM_WORKING_YEAR[$year]."_".$x."'>".$monthname."</a>");
					if ($x < $sum_month) {
						echo("<div class='dropdown-divider'></div>");
					}
				}
				
				echo("</div>");
				echo("</li>");			
			}
		}

		echo("</ul>");

		
		echo("<div class = 'tab-content'>");
		echo("<div class = 'tab-pane active' id='employee'>");
		echo("<div class='card border'>");
		echo("<div class='card-body'>");	
		echo("<table class = 'table table-bordered dt-responsive' cellspacing='0' cellspadding = '0' width='100%' >");
		echo("<tbody>");
		
		echo("<tr>");		
		echo("<td class = 'text-left' >ชื่อ-นามสกุล ภาษาอังกฤษ: <font color = '#17a2b8'>".$employeename_en."</font></td>");	
		echo("</tr>");
		echo("<tr>");
		echo("<td class = 'text-left' >ชื่อเล่น: <font color = '#17a2b8'>".$nickname."</font></td>");	
		echo("</tr>");
		echo("<tr>");
		echo("<td class = 'text-left' >วันที่เริ่มต้นการทำงาน: <font color = '#17a2b8'>".$startdate."</font></td>");	
		echo("</tr>");
		if ($employee_status != 1) {
			echo("<tr>");
			echo("<td class = 'text-left' >วันที่สิ้นสุดการทำงาน: <font color = '#17a2b8'>".$enddate."</font></td>");	
			echo("</tr>");
		}
		echo("<tr>");
		echo("<td class = 'text-left' >Email: <font color = '#17a2b8'>".$email."</font></td>");	
		echo("</tr>");
		echo("<tr>");
		echo("<td class = 'text-left' >วันลาพักร้อนคงเหลือจากปีก่อน: <font color = '#CC0000'>".$cumulativevacation."</font> วัน</td>");	
		echo("</tr>");
		echo("</tbody>");
		echo("</table>");
		echo("</div>");
		echo("</div>");
		echo("</div>");
		
		if (isset($SUMMARY_YEAR)) {
			foreach ($SUMMARY_YEAR as $year => $mresult) {
				echo("<div class = 'tab-pane fade' id='YEAR_".$SUMMARY_YEAR[$year]."'>");
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
				
				echo("</div>");
				echo("</div>");
				echo("</div>");					
			}
		}
		
		if (isset($SUM_WORKING_YEAR)) {
			foreach ($SUM_WORKING_YEAR as $year => $mresult) {
				
				if ($currentyear == $SUM_WORKING_YEAR[$year] ) {
					$sum_month = $currentmonth + 1;
				}
				else {
					$sum_month = 12;
				}
				for ($x = 1; $x <= $sum_month; $x++) {
					echo("<div class = 'tab-pane fade' id='WYEAR_".$SUM_WORKING_YEAR[$year]."_".$x."'>");
					echo("<div class='card border'>");
					
					echo("<div class='card-body'>");
					echo("<table class = 'table table-bordered dt-responsive' cellspacing='0' cellspadding = '0' width='100%' >");
					echo("<tbody>");
					echo("<tr>");		
					switch ($x) {
						case '01': 
							$monthname =  'มกราคม';
						break;
						case '02': 
							$monthname =  'กุมภาพันธ์';
						break;
						case '03': 
							$monthname =  'มีนาคม';
						break;
						case '04': 
							$monthname =  'เมษายน';
						break;
						case '05': 
							$monthname =  'พฤษภาคม';
						break;
						case '06': 
							$monthname =  'มิถุนายน';
						break;
						case '07': 
							$monthname =  'กรกฎาคม';
						break;
						case '08': 
							$monthname =  'สิงหาคม';
						break;
						case '09': 
							$monthname =  'กันยายน';
						break;
						case '10': 
							$monthname =  'ตุลาคม';
						break;
						case '11': 
							$monthname =  'พฤศจิกายน';
						break;
						case '12': 
							$monthname =  'ธันวาคม';
						break;
					}
					echo("<td class = 'text-left' ><font color = '#17a2b8'><h5>".$monthname."</h5></font></td>");	
					echo("</tr>");					
					echo("</tbody>");
					echo("</table>");
		
					echo("<table class = 'table table-bordered dt-responsive' cellspacing='0' cellspadding = '0' width='100%'>");
					echo("<thead>");
					echo("<tr>");
					echo("<th class = 'text-center' bgcolor='#868e96'>ลำดับที่</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>วันที่</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>เวลาเข้า - ออก</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>จำนวนเวลาเข้าสาย (นาที)</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>จำนวนเวลาออกเร็ว (นาที)</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>สถานะ</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>รายละเอียด</th>");
					echo("</tr>");
					echo("</thead>");
					echo("<tbody>");		
					$i = 0;
					if (isset($SUMMARY_WORKING[$SUM_WORKING_YEAR[$year]][$x] )) {
						foreach ($SUMMARY_WORKING[$SUM_WORKING_YEAR[$year]][$x] as $m => $xresult) {
							$i = $i + 1;
							echo("<tr>");
							echo("<td class = 'text-center'>".$i."</td>");	
							if ($xresult['WORKING_STATUS'] == 3 || $xresult['WORKING_STATUS'] == 5) {
								echo("<td class = 'text-center'><font color ='#007bff'>".$xresult['WORKING_DAY']."</font></td>");
								if ($xresult['LEAVE_ID'] == '') {
									echo("<td class = 'text-center'><font color ='#007bff'>". $xresult['WORKING_TIME']."</font></td>");
									echo("<td class = 'text-right'><font color ='#007bff'>". $xresult['INTERVAL_IN']."</font></td>");
									echo("<td class = 'text-right'><font color ='#007bff'>". $xresult['INTERVAL_OUT']."</font></td>");
								}
								else {
									echo("<td class = 'text-center' colspan = '3'><font color ='#007bff'>". $xresult['WORKING_TIME']."</font></td>");
								}

								echo("<td class = 'text-center'><font color ='#007bff'>".$xresult['WORKINGSTATUS']."</font></td>");
								echo("<td class = 'text-center'><font color ='#007bff'>".$xresult['DESCRIPTION']."</font></td>");								
							}
							else if ($xresult['WORKING_STATUS'] == 2) {
								echo("<td class = 'text-center'><font color ='#28a745'>".$xresult['WORKING_DAY']."</font></td>");
								echo("<td class = 'text-center'><font color ='#28a745'>". $xresult['WORKING_TIME']."</font></td>");
								echo("<td class = 'text-right'><font color ='#28a745'>". $xresult['INTERVAL_IN']."</font></td>");
								echo("<td class = 'text-right'><font color ='#28a745'>". $xresult['INTERVAL_OUT']."</font></td>");
								echo("<td class = 'text-center'><font color ='#28a745'>".$xresult['WORKINGSTATUS']."</font></td>");
								echo("<td class = 'text-center'><font color ='#28a745'>".$xresult['DESCRIPTION']."</font></td>");								
							}
							else {
								echo("<td class = 'text-center'><font color ='#dc3545'>".$xresult['WORKING_DAY']."</font></td>");
								echo("<td class = 'text-center'><font color ='#dc3545'>". $xresult['WORKING_TIME']."</font></td>");
								echo("<td class = 'text-right'><font color ='#dc3545'>". $xresult['INTERVAL_IN']."</font></td>");
								echo("<td class = 'text-right'><font color ='#dc3545'>". $xresult['INTERVAL_OUT']."</font></td>");
								echo("<td class = 'text-center'><font color ='#dc3545'>".$xresult['WORKINGSTATUS']."</font></td>");
								echo("<td class = 'text-center'><font color ='#dc3545'>".$xresult['DESCRIPTION']."</font></td>");
							}
							echo("</tr>");						
						}
					}
					else {
						echo("<tr>");		
						echo("<td class = 'text-center' colspan = '8'>ไม่มีข้อมูลที่ต้องการค้นหา</td>");	
						echo("</tr>");							
					}
					echo("</tbody>");
					echo("</table>");	
	
					echo("</div>");
					echo("</div>");
					echo("</div>");							
				}			
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