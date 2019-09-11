<?
	require ('connection.php');
	
	try {

		$currentyear = date("Y");
		$currentmonth = (int)date("m");
		$currentdate = date("d");
		
		$search = '%'.$parameter."%";
		
		if ($userid == 1) {
			$userid = 53;
		}
		
		$sql = "	SELECT EMPLOYEE_ID
					FROM USER
					WHERE USER_ID = ?";
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($userid));		
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$employeeid = $arr['EMPLOYEE_ID'];
		}
					
		$sql = "	SELECT YEAR(WD.WORKING_DAY) WORKING_YEAR
					FROM WORKING_DAY WD
					WHERE SYSDATE() BETWEEN WD.EFFECTIVE_START_DATE AND WD.EFFECTIVE_END_DATE
					AND WD.EMPLOYEE_ID = ?
					GROUP BY YEAR(WD.WORKING_DAY)
					ORDER BY YEAR(WD.WORKING_DAY)";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($employeeid));							

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
		$stmt->execute(array($employeeid));		

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
		
		echo("<ul class = 'nav nav-tabs'>");
		if (isset($SUM_WORKING_YEAR)) {		
			foreach ($SUM_WORKING_YEAR as $year => $mresult) {
				$th_year = (int)$SUM_WORKING_YEAR[$year] + 543;
				echo("<li class='nav-item dropdown'>");
				echo("<a class='nav-link dropdown-toggle' data-toggle='dropdown' href='#' role='button' aria-haspopup='true' aria-expanded='false'>ประวัติเวลาทำงาน ปี ".$th_year."</a>");
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
//					$monthname = monthname($x);
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
		if (isset($SUM_WORKING_YEAR)) {
			foreach ($SUM_WORKING_YEAR as $year => $mresult) {
				if ($currentyear == $SUM_WORKING_YEAR[$year] ) {
					$sum_month = $currentmonth + 1;
				}
				else {
					$sum_month = 12;
				}
				
				for ($x = 1; $x <= $sum_month; $x++) {
					if ($currentyear == $SUM_WORKING_YEAR[$year] && $currentmonth ==  $x ) {
						echo("<div class = 'tab-pane active' id='WYEAR_".$SUM_WORKING_YEAR[$year]."_".$x."'>");
					}
					else {
						echo("<div class = 'tab-pane fade' id='WYEAR_".$SUM_WORKING_YEAR[$year]."_".$x."'>");
					}
					
					echo("<div class='card border'>");
					echo("<div class='card-body'>");
					echo("<table class = 'table table-bordered table-striped dt-responsive' cellspacing='0' cellspadding = '0' width='100%' >");
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
					
					echo("<table class = 'table table-bordered table-striped dt-responsive' cellspacing='0' cellspadding = '0' width='100%'>");
					echo("<thead>");
					echo("<tr>");
					echo("<th class = 'text-center' bgcolor='#868e96'>ลำดับที่</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>วันที่</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>เวลาเข้า - ออก</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>จำนวนเวลาเข้าสาย (นาที)</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>จำนวนเวลาออกเร็ว (นาที)</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>สถานะ</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>รายละเอียด</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>แก้ไข</th>");
					echo("</tr>");
					echo("</thead>");
					echo("<tbody>");		
					$i = 0;
					if (isset($SUMMARY_WORKING[$SUM_WORKING_YEAR[$year]][$x] )) {
						foreach ($SUMMARY_WORKING[$SUM_WORKING_YEAR[$year]][$x] as $m => $xresult) {
							$i = $i + 1;
							$currentday = date("Y-m-d");
							$current = new DateTime($currentday);
							$currentm = new DateTime($m);
							
							echo("<tr>");
							echo("<td class = 'text-center'>".$i."</td>");	
							if ($xresult['WORKING_STATUS'] == 3 || $xresult['WORKING_STATUS'] == 5  ) {
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
							echo("<td  class='text-center'>");							
							echo("<div class='btn-group' role='group' aria-label='Basic example'>");
							if ($_SESSION[session_id()]['MENU'][$menuid]['UPDATE'] == 1 && $xresult['WORKING_STATUS'] == 1) {
								echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$employeeid."|".$m."');\">ส่งอนุมัติ</button>");	
							}	
							else {
								echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$employeeid."|".$m."');\" disabled>ส่งอนุมัติ</button>");	
							}
							echo("</div>");
							echo("</td>");	
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