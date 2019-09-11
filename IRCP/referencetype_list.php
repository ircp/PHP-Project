<?
	require ('connection.php');
	
	try {
		
		$search = '%'.$parameter."%";
		
		$sql = "	SELECT REFERENCE_TYPE_ID, LABEL, ABBREVIATION, DESCRIPTION, ORDERING
					FROM REFERENCE_TYPE
					WHERE LABEL LIKE ? 
					ORDER BY REFERENCE_TYPE_ID";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($search));		
		
		echo("<div class ='row'>");
		echo("<table class = 'table table-striped display' cellspacing='0' cellspadding = '0' width='100%' id = 'listTable_".$menuid."'>");
		echo("<thead>");
		echo("<tr>");
		echo("<th class = 'text-center'></th>");
		echo("<th class = 'text-center'>ลำดับที่</th>");
		echo("<th class = 'text-center'>Reference Type ID</th>");
		echo("<th class = 'text-center'>ชื่อ</th>");
		echo("<th class = 'text-center'>ชื่อย่อ</th>");
		echo("<th class ='text-center'>รายละเอียด</th>");
		echo("<th class ='text-center'>แก้ไข</th>");		
		echo("</tr>");
		echo("</thead>");
		echo("<tbody>");
		$i = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i +1;
			echo("<tr>");
			echo("<td class = 'text-left'>".$arr['REFERENCE_TYPE_ID']."</td>");
			echo("<td class = 'text-center'>".$i."</td>");
			echo("<td class = 'text-center'>".$arr['REFERENCE_TYPE_ID']."</td>");	
			echo("<td class = 'text-left'>".$arr['LABEL']."</td>");
			echo("<td class = 'text-left'>".$arr['ABBREVIATION']."</td>");	
			echo("<td class = 'text-left'>".$arr['DESCRIPTION']."</td>");				
			echo("<td  class='text-center'>");
			echo("<div class='btn-group' role='group' aria-label='Basic example'>");
			echo("<button type='button' class='btn  btn-outline-success' id='subtable' >Reference Code</button>");
			if ($_SESSION[session_id()]['MENU'][$menuid]['UPDATE'] == 1) {
				echo("<button type='button' class='btn  btn-outline-success' data-toggle='modal' data-target='#myModal' onclick = \"windowOpen('5', '".$menuid."', 'A', '".$arr['REFERENCE_TYPE_ID']."|');\">เพิ่ม Reference Code</button>");
				echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$arr['REFERENCE_TYPE_ID']."');\">แก้ไข</button>");
			}
			else {
				echo("<button type='button' class='btn  btn-outline-success' data-toggle='modal' data-target='#myModal' onclick = \"windowOpen('5', '".$menuid."', 'A', '".$arr['REFERENCE_TYPE_ID']."|');\" disabled>เพิ่ม Reference Code</button>");
				echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$arr['REFERENCE_TYPE_ID']."');\" disabled>แก้ไข</button>");				
			}
			if ($_SESSION[session_id()]['MENU'][$menuid]['DELETE'] == 1 ) {
				echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'D', '".$arr['REFERENCE_TYPE_ID']."');\">ลบ</button>");
			}
			else {
				echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'D', '".$arr['REFERENCE_TYPE_ID']."');\" disabled>ลบ</button>");
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