<?
	require ('connection.php');
	
	try {
		
		$search = '%'.$parameter."%";
		
		if ($group == 1) {
			$sql = "	SELECT GROUP_USER.GROUP_ID, GROUP_USER.GROUP_NAME, GROUP_USER.GROUP_ABBREVIATION, GROUP_USER.GROUP_DESCRIPTION, GROUP_USER.VALID_IND_CODE, GROUP_USER.COMPANY_ID, COMPANY.COMPANY_NAME
						FROM GROUP_USER GROUP_USER
						LEFT JOIN COMPANY COMPANY ON COMPANY.COMPANY_ID = GROUP_USER.COMPANY_ID
						WHERE GROUP_USER.GROUP_NAME LIKE ?
						ORDER BY COMPANY.COMPANY_TYPE, COMPANY.COMPANY_NAME, GROUP_USER.GROUP_NAME ASC";			
		}
		else {
			$sql = "	SELECT GROUP_USER.GROUP_ID, GROUP_USER.GROUP_NAME, GROUP_USER.GROUP_ABBREVIATION, GROUP_USER.GROUP_DESCRIPTION, GROUP_USER.VALID_IND_CODE, GROUP_USER.COMPANY_ID, COMPANY.COMPANY_NAME
						FROM GROUP_USER GROUP_USER
						WHERE GROUP_USER.GROUP_NAME LIKE ? AND GROUP_USER.GROUP_ID <> 1
						ORDER BY COMPANY.COMPANY_TYPE, COMPANY.COMPANY_NAME, GROUP_USER.GROUP_NAME ASC";
		}

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($search));		
		
		echo("<div class ='row'>");
		echo("<table class = 'table table-striped display' cellspacing='0' cellspadding = '0' width='100%' id = 'listTable_".$menuid."'>");
		echo("<thead>");
		echo("<tr>");
		echo("<th class = 'text-center'></th>");
		echo("<th class = 'text-center'>ลำดับที่</th>");
		echo("<th class = 'text-center'>ชื่อกลุ่มผู้ใช้งาน</th>");
		echo("<th class = 'text-center'>ชื่อบริษัท</th>");
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
			echo("<td class = 'text-left'>".$arr['GROUP_ID']."</td>");
			echo("<td class = 'text-center'>".$i."</td>");	
			echo("<td class = 'text-left'>".$arr['GROUP_NAME']."</td>");
			echo("<td class = 'text-left'>".$arr['COMPANY_NAME']."</td>");
			echo("<td class = 'text-left'>".$arr['GROUP_ABBREVIATION']."</td>");	
			echo("<td class = 'text-left'>".$arr['GROUP_DESCRIPTION']."</td>");				
			if ($arr['VALID_IND_CODE'] == 1) {
				echo("<td class = 'text-center'><font color = '#00C851'><i class='fas fa-check'></i></font></td>");	
			}
			else {
				echo("<td class = 'text-center'><font color = '#CC0000'><i class='fas fa-times'></i></font></td>");	
			}
			echo("<td  class='text-center'>");
			echo("<div class='btn-group' role='group' aria-label='Basic example'>");
			echo("<button type='button' class='btn  btn-outline-success' id='subtable' >เมนู</button>");
			if ($_SESSION[session_id()]['MENU'][$menuid]['UPDATE'] == 1) {
				echo("<button type='button' class='btn  btn-outline-success' data-toggle='modal' data-target='#myModal' onclick = \"windowOpen('5', '".$menuid."', 'A', '".$arr['GROUP_ID']."|');\">เพิ่มเมนู</button>");
				echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$arr['GROUP_ID']."');\">แก้ไข</button>");
			}
			else {
				echo("<button type='button' class='btn  btn-outline-success' data-toggle='modal' data-target='#myModal' onclick = \"windowOpen('5', '".$menuid."', 'A', '".$arr['GROUP_ID']."|');\" disabled>เพิ่มเมนู</button>");
				echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$arr['GROUP_ID']."');\" disabled>แก้ไข</button>");				
			}
			if ($_SESSION[session_id()]['MENU'][$menuid]['DELETE'] == 1 ) {
				echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'D', '".$arr['GROUP_ID']."');\">ลบ</button>");
			}
			else {
				echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'D', '".$arr['GROUP_ID']."');\" disabled>ลบ</button>");
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