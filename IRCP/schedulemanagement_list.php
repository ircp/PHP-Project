<?
	require ('connection.php');
	
	try {
		
		$search = '%'.$parameter."%";
		
		$sql = "	SELECT SCHEDULE_ID, SCHEDULE_NAME, SCHEDULE.DESCRIPTION, URL, SCHEDULE_TYPE, SCHEDULE.VALID_IND_CODE, MAIL, RC.LABEL
					FROM SCHEDULE  SCHEDULE
					LEFT JOIN REFERENCE_CODE RC ON RC.REFERENCE_TYPE_ID = 11 AND SCHEDULE_TYPE = RC.REFERENCE_CODE
					WHERE SCHEDULE_NAME LIKE ?
					ORDER BY RC.LABEL, SCHEDULE_NAME, SCHEDULE.DESCRIPTION";
		

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($search));		
		
		echo("<div class ='row'>");
		echo("<table class = 'table table-striped display' cellspacing='0' cellspadding = '0' width='100%' id = 'listTable_".$menuid."'>");
		echo("<thead>");
		echo("<tr>");
		echo("<th class = 'text-center'></th>");
		echo("<th class = 'text-center'>ลำดับที่</th>");
		echo("<th class = 'text-center'>ชื๋อ Schedule</th>");
		echo("<th class = 'text-center'>ประเภท Schedule</th>");
		echo("<th class = 'text-center'>URL</th>");
		echo("<th class ='text-center'>ส่งเมล์?</th>");
		echo("<th class ='text-center'>ใช้งาน?</th>");
		echo("<th class ='text-center'>แก้ไข</th>");		
		echo("</tr>");
		echo("</thead>");
		echo("<tbody>");
		$i = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i +1;
			echo("<tr>");
			echo("<td class = 'text-left'>".$arr['SCHEDULE_ID']."</td>");
			echo("<td class = 'text-center'>".$i."</td>");	
			echo("<td class = 'text-left'>".$arr['SCHEDULE_NAME']."</td>");
			echo("<td class = 'text-left'>".$arr['LABEL']."</td>");
			echo("<td class = 'text-left'>".$arr['URL']."</td>");		
			if ($arr['MAIL'] == 1) {
				echo("<td class = 'text-center'><font color = '#00C851'><i class='fas fa-check'></i></font></td>");	
			}
			else {
				echo("<td class = 'text-center'><font color = '#CC0000'><i class='fas fa-times'></i></font></td>");	
			}			
			if ($arr['VALID_IND_CODE'] == 1) {
				echo("<td class = 'text-center'><font color = '#00C851'><i class='fas fa-check'></i></font></td>");	
			}
			else {
				echo("<td class = 'text-center'><font color = '#CC0000'><i class='fas fa-times'></i></font></td>");	
			}
			echo("<td  class='text-center'>");
			echo("<div class='btn-group' role='group' aria-label='Basic example'>");
			echo("<button type='button' class='btn  btn-outline-success' id='subtable' >ผู้รับ</button>");
			if ($_SESSION[session_id()]['MENU'][$menuid]['UPDATE'] == 1) {
				echo("<button type='button' class='btn  btn-outline-success' data-toggle='modal' data-target='#myModal' onclick = \"windowOpen('5', '".$menuid."', 'A', '".$arr['SCHEDULE_ID']."|');\">เพิ่มผู้รับ</button>");
				echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$arr['SCHEDULE_ID']."');\">แก้ไข</button>");
			}
			else {
				echo("<button type='button' class='btn  btn-outline-success' data-toggle='modal' data-target='#myModal' onclick = \"windowOpen('5', '".$menuid."', 'A', '".$arr['SCHEDULE_ID']."|');\" disabled>เพิ่มผู้รับ</button>");
				echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$arr['SCHEDULE_ID']."');\" disabled>แก้ไข</button>");				
			}
			if ($_SESSION[session_id()]['MENU'][$menuid]['DELETE'] == 1 ) {
				echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'D', '".$arr['SCHEDULE_ID']."');\">ลบ</button>");
			}
			else {
				echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'D', '".$arr['SCHEDULE_ID']."');\" disabled>ลบ</button>");
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