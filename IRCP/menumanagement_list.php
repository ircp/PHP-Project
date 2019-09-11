<?
	require ('connection.php');
	
	try {
		
		$search = '%'.$parameter."%";
		
		$sql = "	SELECT MENU.MENU_ID, MENU.MENU_NAME, MENU.MENU_DESCRIPTION, MENU.MENU_URL, MENU.MENU_IMAGE, MENU.OBJECT_TYPE, RC.LABEL
					FROM MENU MENU
					LEFT JOIN REFERENCE_CODE RC ON RC.REFERENCE_CODE = MENU.OBJECT_TYPE AND RC.REFERENCE_TYPE_ID = 9
					WHERE MENU.MENU_NAME LIKE ? OR MENU.MENU_DESCRIPTION LIKE ?
					ORDER BY MENU.MENU_NAME, MENU.MENU_DESCRIPTION";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($search, $search));		
		
		echo("<div class ='row'>");
		echo("<table class = 'table table-striped display' cellspacing='0' cellspadding = '0' width='100%' id = 'listTable_".$menuid."'>");
		echo("<thead>");
		echo("<tr>");
		echo("<th class = 'text-center'></th>");
		echo("<th class = 'text-center'>ลำดับที่</th>");
		echo("<th class = 'text-center'>ประเภท Object</th>");
		echo("<th class = 'text-center'>ชื่อ Object</th>");
		echo("<th class = 'text-center'>รายละเอียด</th>");
		echo("<th class ='text-center'>URL</th>");
		echo("<th class ='text-center'>แก้ไข</th>");		
		echo("</tr>");
		echo("</thead>");
		echo("<tbody>");
		$i = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i +1;
			echo("<tr>");
			echo("<td class = 'text-left'>".$arr['MENU_ID']."</td>");
			echo("<td class = 'text-center'>".$i."</td>");	
			echo("<td class = 'text-center'>".$arr['LABEL']."</td>");
			echo("<td class = 'text-left'>".$arr['MENU_NAME']."</td>");
			echo("<td class = 'text-left'>".$arr['MENU_DESCRIPTION']."</td>");				
			echo("<td class = 'text-left'>".$arr['MENU_URL']."</td>");	
			echo("<td  class='text-center'>");
			echo("<div class='btn-group' role='group' aria-label='Basic example'>");
			if ($_SESSION[session_id()]['MENU'][$menuid]['UPDATE'] == 1) {
				echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$arr['MENU_ID']."');\">แก้ไข</button>");
			}
			if ($_SESSION[session_id()]['MENU'][$menuid]['DELETE'] == 1 ) {
				echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'D', '".$arr['MENU_ID']."');\">ลบ</button>");
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