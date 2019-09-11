<?
	require ('connection.php');
	
	try {
		
		$search = '%'.$parameter."%";
		
		if ($group == 1) {
			$sql = "	SELECT DEPARTMENT_ID, CREATED_DATE , LAST_MODIFIED, USER_ID, DEPARTMENT_NAME, ABBREVIATION, DESCRIPTION, VALID_IND_CODE
						FROM DEPARTMENT
						WHERE DEPARTMENT_NAME LIKE ? 
						ORDER BY DEPARTMENT_NAME";

		}
		else {
			$sql = "	SELECT DEPARTMENT_ID, CREATED_DATE , LAST_MODIFIED, USER_ID, DEPARTMENT_NAME, ABBREVIATION, DESCRIPTION, VALID_IND_CODE
						FROM DEPARTMENT
						WHERE DEPARTMENT_NAME LIKE ? AND DEPARTMENT_ID <> 1
						ORDER BY DEPARTMENT_NAME";
		}
		
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($search));	
		
		echo("<div class ='row'>");
		echo("<table class = 'table table-striped display' cellspacing='0' cellspadding = '0' width='100%' id = 'listTable_".$menuid."'>");
		echo("<thead>");
		echo("<tr>");
		echo("<th class = 'text-center'></th>");
		echo("<th class = 'text-center'>ลำดับที่</th>");
		echo("<th class = 'text-center'>ชื่อแผนก/ฝ่าย</th>");
		echo("<th class = 'text-center'>ชื่อย่อ</th>");
		echo("<th class ='text-center'>รายละเอียด</th>");
		echo("<th class ='text-center'>ใช้งาน?</th>");
		echo("<th class ='text-center'>แก้ไข</th>");		
		echo("</tr>");
		echo("</thead>");
		echo("<tbody>");
		$i = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i +1;
			echo("<tr>");
			echo("<td class = 'text-left'>".$arr['DEPARTMENT_ID']."</td>");
			echo("<td class = 'text-center'>".$i."</td>");	
			echo("<td class = 'text-left'>".$arr['DEPARTMENT_NAME']."</td>");
			echo("<td class = 'text-left'>".$arr['ABBREVIATION']."</td>");	
			echo("<td class = 'text-left'>".$arr['DESCRIPTION']."</td>");				
			if ($arr['VALID_IND_CODE'] == 1) {
				echo("<td class = 'text-center'><font color = '#00C851'><i class='fas fa-check'></i></font></td>");	
			}
			else {
				echo("<td class = 'text-center'><font color = '#CC0000'><i class='fas fa-times'></i></font></td>");	
			}
			echo("<td  class='text-center'>");
			echo("<div class='btn-group' role='group' aria-label='Basic example'>");
			echo("<button type='button' class='btn  btn-outline-success' id='subtable' >รายชื่อพนักงาน</button>");
			if ($_SESSION[session_id()]['MENU'][$menuid]['UPDATE'] == 1) {
				echo("<button type='button' class='btn  btn-outline-success' data-toggle='modal' data-target='#myModal' onclick = \"windowOpen('5', '".$menuid."', 'A', '".$arr['DEPARTMENT_ID']."|');\">เพิ่มผู้บริหาร</button>");
				echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$arr['DEPARTMENT_ID']."');\">แก้ไข</button>");
			}
			else {
				echo("<button type='button' class='btn  btn-outline-success' data-toggle='modal' data-target='#myModal' onclick = \"windowOpen('5', '".$menuid."', 'A', '".$arr['DEPARTMENT_ID']."|');\" disabled>เพิ่มผู้บริหาร</button>");
				echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$arr['DEPARTMENT_ID']."');\" disabled>แก้ไข</button>");				
			}
			if ($_SESSION[session_id()]['MENU'][$menuid]['DELETE'] == 1 ) {
				echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'D', '".$arr['DEPARTMENT_ID']."');\">ลบ</button>");
			}
			else {
				echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'D', '".$arr['DEPARTMENT_ID']."');\" disabled>ลบ</button>");
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