<?
	require ('connection.php');
	
	try {
		
		$search = '%'.$parameter."%";
		
	
		$sql = "	SELECT TASK_SCHEDULE.TASK_ID, TASK_SCHEDULE.SCHEDULE_ID, TASK_SCHEDULE.TASK_STATUS, TASK_SCHEDULE.LAST_MODIFIED, 
					TASK_SCHEDULE.ERROR_MESSAGE, TASK_SCHEDULE.FILENAME, TASK_SCHEDULE.MAIL_STATUS,
					SCHEDULE.SCHEDULE_NAME, RC.LABEL TASKSTATUS
					FROM TASK_SCHEDULE TASK_SCHEDULE
					LEFT JOIN SCHEDULE SCHEDULE ON TASK_SCHEDULE.SCHEDULE_ID = SCHEDULE.SCHEDULE_ID
					LEFT JOIN REFERENCE_CODE RC ON RC.REFERENCE_TYPE_ID = 12 AND RC.REFERENCE_CODE = TASK_SCHEDULE.TASK_STATUS
					WHERE SCHEDULE.SCHEDULE_NAME LIKE ?
					ORDER BY TASK_SCHEDULE.LAST_MODIFIED DESC";
		
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($search));	
		
		echo("<div class ='row'>");
		echo("<table class = 'table table-striped display' cellspacing='0' cellspadding = '0' width='100%' id = 'listTable_".$menuid."'>");
		echo("<thead>");
		echo("<tr>");
		echo("<th class = 'text-center'></th>");
		echo("<th class = 'text-center'>ลำดับที่</th>");
		echo("<th class = 'text-center'>วันที่ดำเนินการ</th>");
		echo("<th class = 'text-center'>ชื่อ Schedule</th>");
		echo("<th class = 'text-center'>สถานะ</th>");
		echo("<th class = 'text-center'>การส่งเมล์</th>");
		echo("<th class = 'text-center'>ชื่อไฟล์</th>");
		echo("</tr>");
		echo("</thead>");
		echo("<tbody>");
		$i = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i +1;
			echo("<tr>");
			echo("<td class = 'text-left'>".$arr['TASK_ID']."</td>");
			echo("<td class = 'text-center'>".$i."</td>");	
			echo("<td class = 'text-center'>".$arr['LAST_MODIFIED']."</td>");	
			echo("<td class = 'text-left'>".$arr['SCHEDULE_NAME']."</td>");	
			echo("<td class = 'text-center'>".$arr['TASKSTATUS']."</td>");	
			if ($arr['MAIL_STATUS'] == 1) {
				echo("<td class = 'text-center'><font color = '#00C851'><i class='fas fa-check'></i></font></td>");	
			}
			else {
				echo("<td class = 'text-center'><font color = '#CC0000'><i class='fas fa-times'></i></font></td>");	
			}
			$location = "FILES/REPORT/".$arr['FILENAME'];
			echo("<td class = 'text-left'><a href='".$location."' download target='_blank'>".$arr['FILENAME']."</a></td>");
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