<?
	require ('connection.php');
	
	try {
		
		$search = '%'.$parameter."%";
		
		$sql = "	SELECT SCHEDULE_MAIL.EMPLOYEE_ID, EMPLOYEE.TITLE, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME
					FROM SCHEDULE_MAIL SCHEDULE_MAIL
					LEFT JOIN EMPLOYEE EMPLOYEE ON SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE 
					AND SCHEDULE_MAIL.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID
					WHERE SCHEDULE_MAIL.SCHEDULE_ID = ?
					ORDER BY EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));		
		
		$i = 0;
		
		unset($RESULT);
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {		
			$i = $i +1;
			$RESULT[$i]['EMPLOYEE_ID'] = $arr['EMPLOYEE_ID'];
			$RESULT[$i]['NAME'] = $arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME'];	
		}
		
		$total = $i;

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
			
			foreach ($RESULT as $x => $mresult) {
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
//					echo("<div class='container'>");
					echo("<table class = 'table table-bordered dt-responsive' cellspacing='0' cellspadding = '0' width='100%'>");
					echo("<thead>");
					echo("<tr>");
					echo("<th class = 'text-center' bgcolor='#868e96'>ลำดับที่</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>แก้ไข</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>ชื่อ-นามสกุล</th>");
					echo("</tr>");
					echo("</thead>");
					echo("<tbody>");
				}
				
				echo("<tr>");		
				echo("<td class = 'text-center'>".$i."</td>");	
				echo("<td  class='text-center'>");
				echo("<div class='btn-group' role='group' aria-label='Basic example'>");
				if ($_SESSION[session_id()]['MENU'][$menuid]['DELETE'] == 1) {
					echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('5', '".$menuid."',  'D', '".$key."|".$mresult['EMPLOYEE_ID']."');\">ลบ</button>");
				}			
				echo("</div>");
				echo("</td>");
				echo("<td class = 'text-left'>".$mresult['NAME']."</td>");
				echo("</tr>");
				
				if ($recordcount == 5 && $n != $total) {
					$recordcount = 0;
					echo("</tbody>");
					echo("</table>");
//					echo("</div>");
					echo("</div>");
				}			
			};
			echo("</tbody>");
			echo("</table>");
			echo("</div>");
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
//			echo("<div class='container'>");
			echo("<table class = 'table table-bordered dt-responsive' cellspacing='0' cellspadding = '0' width='100%' >");
			echo("<thead>");
			echo("<tr>");
			echo("<th class = 'text-center' bgcolor='#868e96'>ลำดับที่</th>");
			echo("<th class = 'text-center' bgcolor='#868e96'>แก้ไข</th>");
			echo("<th class = 'text-center' bgcolor='#868e96'>ชื่อ-นามสกุล</th>");
			echo("</tr>");
			echo("</thead>");
			echo("<tbody>");
			
			echo("<tr>");		
			echo("<td class = 'text-center' colspan='3'>ไม่มีข้อมูลที่ต้องการค้นหา</td>");	
			echo("</tr>");
			echo("</tbody>");
			echo("</table>");
//			echo("</div>");
		}
		
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