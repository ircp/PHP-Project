<?
	require ('connection.php');
	
	try {
		
		$search = '%'.$parameter."%";
		
		
		if ($group == 1) {
			$sql = "	SELECT USER.USER_ID, USER.USER_NAME, USER.FIRST_NAME, USER.LAST_NAME, USER.TELEPHONE, USER.EMAIL, USER.VALID_IND_CODE, USER.GROUP_ID,
						GROUP_USER.GROUP_NAME, COMPANY.COMPANY_NAME
						FROM USER USER
						LEFT JOIN GROUP_USER GROUP_USER ON GROUP_USER.GROUP_ID = USER.GROUP_ID
						LEFT JOIN COMPANY COMPANY ON COMPANY.COMPANY_ID = USER.COMPANY_ID
						WHERE USER_NAME LIKE ? OR FIRST_NAME LIKE ? OR COMPANY.COMPANY_NAME LIKE ?
						ORDER BY USER_NAME";
			
		}
		else {
			$sql = "	SELECT USER.USER_ID, USER.USER_NAME, USER.FIRST_NAME, USER.LAST_NAME, USER.TELEPHONE, USER.EMAIL, USER.VALID_IND_CODE, USER.GROUP_ID,
						GROUP_USER.GROUP_NAME, COMPANY.COMPANY_NAME
						FROM USER USER
						LEFT JOIN GROUP_USER GROUP_USER ON GROUP_USER.GROUP_ID = USER.GROUP_ID
						LEFT JOIN COMPANY COMPANY ON COMPANY.COMPANY_ID = USER.COMPANY_ID
						WHERE (USER_NAME LIKE ? OR FIRST_NAME LIKE ? OR COMPANY.COMPANY_NAME LIKE ?) AND USER.USER_ID <> 1
						ORDER BY USER_NAME";
		}
		
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($search, $search, $search));	
		
		echo("<div class ='row'>");
		echo("<table class = 'table table-striped display' cellspacing='0' cellspadding = '0' width='100%' id = 'listTable_".$menuid."'>");
		echo("<thead>");
		echo("<tr>");
		echo("<th class = 'text-center'></th>");
		echo("<th class = 'text-center'>ลำดับที่</th>");
		echo("<th class = 'text-center'>รหัสผู้ใช้งาน</th>");
		echo("<th class = 'text-center'>ชื่อ-นามสกุล</th>");
		echo("<th class ='text-center'>กลุ่มผู้ใช้งาน</th>");
		echo("<th class ='text-center'>เบอร์โทร</th>");
		echo("<th class ='text-center'>Email</th>");
		echo("<th class ='text-center'>บริษัท</th>");
		echo("<th class ='text-center'>ใช้งาน?</th>");
		echo("<th class ='text-center'>แก้ไข</th>");		
		echo("</tr>");
		echo("</thead>");
		echo("<tbody>");
		$i = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i +1;
			echo("<tr>");
			echo("<td class = 'text-left'>".$arr['USER_ID']."</td>");
			echo("<td class = 'text-center'>".$i."</td>");	
			echo("<td class = 'text-left'>".$arr['USER_NAME']."</td>");
			echo("<td class = 'text-left'>".$arr['FIRST_NAME']." ".$arr['LAST_NAME']."</td>");	
			echo("<td class = 'text-left'>".$arr['GROUP_NAME']."</td>");				
			echo("<td class = 'text-left'>".$arr['TELEPHONE']."</td>");	
			echo("<td class = 'text-left'>".$arr['EMAIL']."</td>");
			echo("<td class = 'text-left'>".$arr['COMPANY_NAME']."</td>");
			if ($arr['VALID_IND_CODE'] == 1) {
				echo("<td class = 'text-center'><font color = '#00C851'><i class='fas fa-check'></i></font></td>");	
			}
			else {
				echo("<td class = 'text-center'><font color = '#CC0000'><i class='fas fa-times'></i></font></td>");	
			}
			echo("<td  class='text-center'>");
			echo("<div class='btn-group' role='group' aria-label='Basic example'>");
			echo("<button type='button' class='btn  btn-outline-success' id='subtable' >ประวัติการเข้าใช้งาน</button>");
			if ($_SESSION[session_id()]['MENU'][$menuid]['UPDATE'] == 1) {
//				echo("<button type='button' class='btn  btn-outline-success' onclick = \"windowProcess('2', '".$menuid."', 'R', '".$arr['USER_ID']."');\">Reset Password</button>");
				echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$arr['USER_ID']."');\">แก้ไข</button>");
			}
			else {
//				echo("<button type='button' class='btn  btn-outline-success' onclick = \"windowProcess('3', '".$menuid."', 'A', '".$arr['USER_ID']."');\" disabled>Reset Password</button>");
				echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$arr['USER_ID']."');\" disabled>แก้ไข</button>");				
			}
			if ($_SESSION[session_id()]['MENU'][$menuid]['DELETE'] == 1 ) {
				echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'D', '".$arr['USER_ID']."');\">ลบ</button>");
			}
			else {
				echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'D', '".$arr['USER_ID']."');\" disabled>ลบ</button>");
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