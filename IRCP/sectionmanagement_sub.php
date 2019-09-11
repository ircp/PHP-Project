<?
	require ('connection.php');
	
	try {
		$sql = "	SELECT EMPLOYEE.EMPLOYEE_CODE, EMPLOYEE.TITLE, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, LABEL, EMPLOYEE.EMPLOYEE_ID
					FROM EMPLOYEE EMPLOYEE
					LEFT JOIN REFERENCE_CODE RC ON RC.REFERENCE_CODE = EMPLOYEE.POSITION_ID AND RC.REFERENCE_TYPE_ID = 4
					WHERE SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
					AND EMPLOYEE.EMPLOYEE_STATUS = 1
					AND EMPLOYEE.DEPARTMENT_ID = ?
					ORDER BY EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));		
		
		$i = 0;
		
		unset($EMPLOYEE);
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i +1;
			$EMPLOYEE[$arr['EMPLOYEE_ID']]['EMPLOYEE_CODE'] = $arr['EMPLOYEE_CODE'];
			$EMPLOYEE[$arr['EMPLOYEE_ID']]['EMPLOYEE_NAME'] = $arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME'];
			$EMPLOYEE[$arr['EMPLOYEE_ID']]['POSITION'] = $arr['LABEL'];
		}
		
		$total_employee = $i;
		
		$sql = "	SELECT EMPLOYEE.EMPLOYEE_CODE, EMPLOYEE.TITLE, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, EMPLOYEE.EMPLOYEE_ID, RC.LABEL
					FROM DEPARTMENT_EXECUTIVE DE
					LEFT JOIN EMPLOYEE EMPLOYEE ON EMPLOYEE.EMPLOYEE_ID = DE.EMPLOYEE_ID AND SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
					LEFT JOIN REFERENCE_CODE RC ON RC.REFERENCE_CODE = EMPLOYEE.POSITION_ID AND RC.REFERENCE_TYPE_ID = 4
					WHERE DE.DEPARTMENT_ID = ?
					ORDER BY DE.ORDERING, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME"; 

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));
			
		$i = 0;
		
		unset($EXECUTIVE);
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i +1;
			$EXECUTIVE[$arr['EMPLOYEE_ID']]['EMPLOYEE_CODE'] = $arr['EMPLOYEE_CODE'];
			$EXECUTIVE[$arr['EMPLOYEE_ID']]['EMPLOYEE_NAME'] = $arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME'];
			$EXECUTIVE[$arr['EMPLOYEE_ID']]['POSITION'] = $arr['LABEL'];
		}		
		
		$total_executive = $i;
		
		echo("<ul class = 'nav nav-tabs'>");
		echo("<li class='nav-item'>");
		echo("<a class='nav-link active' data-toggle='tab' href='#employee'>รายชื่อพนักงาน <span class='badge badge-info badge-pill'><div id='total_employee'>".$total_employee."</div></span></a>");
		echo("</li>");
		echo("<li class='nav-item'>");
		echo("<a class='nav-link' data-toggle='tab' href='#executive'>รายชื่อผู้บริหาร <span class='badge badge-info badge-pill'><div id='total_executive'>".$total_executive."</div></span></a>");
		echo("</li>");
		echo("</ul>");
		echo("<div class = 'tab-content'>");
		echo("<div class = 'tab-pane active' id='employee'>");
		
		$total = $total_employee;

		if ($total <= 5) {
			$sumpage = 1;
		}
		else {
			$temp = $total/5;
			$sumpage = ceil($temp);
		}
		
		$recordcount = 0;
		$pagecount = 1;
		
		echo("<div id='carouselExampleControls' class = 'carousel slide'  data-ride= 'carousel'>");
		echo("<div class = 'carousel-inner'>");
		echo("<div class='card border'>");
		echo("<div class='card-body'>");
		if ($total > 0) {
			$n = 0;
			$i = 0;
			
			foreach ($EMPLOYEE as $x => $mresult) {
				$i = $i + 1;
				$recordcount = $recordcount + 1;
				$n = $n + 1;
				if ($recordcount == 1) {
					if ($n == 1) {
						echo("<div class='carousel-item active'>");
					}
					else {
						echo("<div class='carousel-item'>");
					}
					echo("<table class = 'table table-bordered dt-responsive ' cellspacing='0' cellspadding = '0' width='100%'>");
					echo("<thead>");
					echo("<tr>");
					echo("<th class = 'text-center' bgcolor='#868e96'>ลำดับที่</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>ชื่อ-นามสกุล</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>ตำแหน่ง</th>");
					echo("</tr>");
					echo("</thead>");
					echo("<tbody>");
				}
				
				echo("<tr>");		
				echo("<td class = 'text-center'>".$i."</td>");	
				echo("<td class = 'text-left'>".$mresult['EMPLOYEE_NAME']."</td>");
				echo("<td class = 'text-left'>".$mresult['POSITION']."</td>");
				echo("</tr>");
				
				if ($recordcount == 5 && $n != $total) {
					$recordcount = 0;
					echo("</tbody>");
					echo("</table>");
					echo("</div>");
				}			
			};
			echo("</tbody>");
			echo("</table>");
			echo("</div>");
			
			if ($sumpage > 1) {
				echo("<a class='carousel-control-prev' href='#carouselExampleControls' role='button' data-slide='prev'>");
				echo("<span class='carousel-control-prev-icon' aria-hidden='true'></span>");
				echo("<span class='sr-only'>Previous</span>");
				echo("</a>");
				echo("<a class='carousel-control-next' href='#carouselExampleControls' role='button' data-slide='next'>");
				echo("<span class='carousel-control-next-icon' aria-hidden='true'></span>");
				echo("<span class='sr-only'>Next</span>");
				echo("</a>");			
			}
		}
		else {
			echo("<table class = 'table table-bordered dt-responsive' cellspacing='0' cellspadding = '0' width='100%' >");
			echo("<thead>");
			echo("<tr>");
			echo("<th class = 'text-center' bgcolor='#868e96'>ลำดับที่</th>");
			echo("<th class = 'text-center' bgcolor='#868e96'>ชื่อ-นามสกุล</th>");
			echo("<th class = 'text-center' bgcolor='#868e96'>ตำแหน่ง</th>");
			echo("</tr>");
			echo("</thead>");
			echo("<tbody>");
			
			echo("<tr>");		
			echo("<td class = 'text-center' colspan='3'>ไม่มีข้อมูลที่ต้องการค้นหา</td>");	
			echo("</tr>");
			echo("</tbody>");
			echo("</table>");
		}
		
		echo("</div>");
		echo("</div>");			
		echo("</div>");
		echo("</div>");	
		
		echo("</div>");
		echo("<div class = 'tab-pane fade' id='executive'>");

		$total = $total_executive;

		if ($total <= 5) {
			$sumpage = 1;
		}
		else {
			$temp = $total/5;
			$sumpage = ceil($temp);
		}
		
		$recordcount = 0;
		$pagecount = 1;
		
		echo("<div id='carouselExampleControls' class = 'carousel slide'  data-ride= 'carousel'>");
		echo("<div class = 'carousel-inner'>");
		echo("<div class='card border'>");
		echo("<div class='card-body'>");
		if ($total > 0) {
			$n = 0;
			$i = 0;
			
			foreach ($EXECUTIVE as $x => $mresult) {
				$i = $i + 1;
				$recordcount = $recordcount + 1;
				$n = $n + 1;
				if ($recordcount == 1) {
					if ($n == 1) {
						echo("<div class='carousel-item active'>");
					}
					else {
						echo("<div class='carousel-item'>");
					}
					if ($_SESSION[session_id()]['MENU'][$menuid]['UPDATE'] == 1) {
						echo("<table class = 'table table-bordered dt-responsive sorted_table' cellspacing='0' cellspadding = '0' width='100%'>");
					}
					else {
						echo("<table class = 'table table-bordered dt-responsive' cellspacing='0' cellspadding = '0' width='100%'>");
					}
					echo("<thead>");
					echo("<tr>");
					echo("<th class = 'text-center' bgcolor='#868e96'>ลำดับที่</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>แก้ไข</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>ชื่อ-นามสกุล</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>ตำแหน่ง</th>");
					echo("</tr>");
					echo("</thead>");
					echo("<tbody>");
				}
				
				echo("<tr>");		
				echo("<td class = 'text-center'>".$i."</td>");	
				echo("<td  class='text-center'>");
				echo("<div class='btn-group' role='group' aria-label='Basic example'>");
				if ($_SESSION[session_id()]['MENU'][$menuid]['DELETE'] == 1) {
					echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('5', '".$menuid."',  'D', '".$key."|".$x."');\">ลบ</button>");
				}			
				echo("</div>");
				echo("</td>");	
				echo("<td class = 'text-left'>".$mresult['EMPLOYEE_NAME']."</td>");
				echo("<td class = 'text-left'>".$mresult['POSITION']."</td>");
				echo("</tr>");
				
				if ($recordcount == 5 && $n != $total) {
					$recordcount = 0;
					echo("</tbody>");
					echo("</table>");
					echo("</div>");
				}			
			};
			echo("</tbody>");
			echo("</table>");
			echo("</div>");
			
			if ($sumpage > 1) {
				echo("<a class='carousel-control-prev' href='#carouselExampleControls' role='button' data-slide='prev'>");
				echo("<span class='carousel-control-prev-icon' aria-hidden='true'></span>");
				echo("<span class='sr-only'>Previous</span>");
				echo("</a>");
				echo("<a class='carousel-control-next' href='#carouselExampleControls' role='button' data-slide='next'>");
				echo("<span class='carousel-control-next-icon' aria-hidden='true'></span>");
				echo("<span class='sr-only'>Next</span>");
				echo("</a>");			
			}
		}
		else {
			echo("<table class = 'table table-bordered dt-responsive' cellspacing='0' cellspadding = '0' width='100%' >");
			echo("<thead>");
			echo("<tr>");
			echo("<th class = 'text-center' bgcolor='#868e96'>ลำดับที่</th>");
			echo("<th class = 'text-center' bgcolor='#868e96'>แก้ไข</th>");
			echo("<th class = 'text-center' bgcolor='#868e96'>ชื่อ-นามสกุล</th>");
			echo("<th class = 'text-center' bgcolor='#868e96'>ตำแหน่ง</th>");
			echo("</tr>");
			echo("</thead>");
			echo("<tbody>");
			
			echo("<tr>");		
			echo("<td class = 'text-center' colspan='4'>ไม่มีข้อมูลที่ต้องการค้นหา</td>");	
			echo("</tr>");
			echo("</tbody>");
			echo("</table>");
		}
		
		echo("</div>");
		echo("</div>");			
		echo("</div>");
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