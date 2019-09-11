<?
	require ('connection.php');
	
	try {
		
		$search = '%'.$parameter."%";
		
		if ($group == 1) {
			$sql = "	SELECT COMPANY.COMPANY_ID, COMPANY.COMPANY_NAME, COMPANY.ABBREVIATION, COMPANY.DESCRIPTION, COMPANY.COMPANY_TYPE, COMPANY.VALID_IND_CODE,
						RC1.LABEL
						FROM COMPANY COMPANY
						LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 3 AND RC1.REFERENCE_CODE = COMPANY.COMPANY_TYPE
						WHERE COMPANY_NAME LIKE ? 
						ORDER BY COMPANY_TYPE, COMPANY_NAME";

		}
		else {
			$sql = "	SELECT COMPANY.COMPANY_ID, COMPANY.COMPANY_NAME, COMPANY.ABBREVIATION, COMPANY.DESCRIPTION, COMPANY.COMPANY_TYPE, COMPANY.VALID_IND_CODE,
						RC1.LABEL
						FROM COMPANY COMPANY
						LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 3 AND RC1.REFERENCE_CODE = COMPANY.COMPANY_TYPE
						WHERE COMPANY_NAME LIKE ? AND COMPANY_ID <> 1
						ORDER BY COMPANY_TYPE, COMPANY_NAME";
		
		}
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($search));		
		
		echo("<div class ='row'>");
		echo("<table class = 'table table-striped display' cellspacing='0' cellspadding = '0' width='100%' id = 'listTable_".$menuid."'>");
		echo("<thead>");
		echo("<tr>");
		echo("<th class = 'text-center'></th>");
		echo("<th class = 'text-center'>ลำดับที่</th>");
		echo("<th class = 'text-center'>ชื่อบริษัท</th>");
		echo("<th class = 'text-center'>ชื่อย่อ</th>");
		echo("<th class ='text-center'>รายละเอียด</th>");
		echo("<th class ='text-center'>ประเภทบริษัท</th>");
		echo("<th class ='text-center'>ใช้งาน?</th>");
		echo("<th class ='text-center'>แก้ไข</th>");		
		echo("</tr>");
		echo("</thead>");
		echo("<tbody>");
		$i = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i +1;
			echo("<tr>");
			echo("<td class = 'text-left'>".$arr['COMPANY_ID']."</td>");
			echo("<td class = 'text-center'>".$i."</td>");	
			echo("<td class = 'text-left'>".$arr['COMPANY_NAME']."</td>");
			echo("<td class = 'text-left'>".$arr['ABBREVIATION']."</td>");	
			echo("<td class = 'text-left'>".$arr['DESCRIPTION']."</td>");				
			echo("<td class = 'text-left'>".$arr['LABEL']."</td>");	
			if ($arr['VALID_IND_CODE'] == 1) {
				echo("<td class = 'text-center'><font color = '#00C851'><i class='fas fa-check'></i></font></td>");	
			}
			else {
				echo("<td class = 'text-center'><font color = '#CC0000'><i class='fas fa-times'></i></font></td>");	
			}
			echo("<td  class='text-center'>");
			echo("<div class='btn-group' role='group' aria-label='Basic example'>");
			if ($_SESSION[session_id()]['MENU'][$menuid]['UPDATE'] == 1) {
				echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$arr['COMPANY_ID']."');\">แก้ไข</button>");
			}
			else {
				echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$arr['COMPANY_ID']."');\" disabled>แก้ไข</button>");				
			}
			if ($_SESSION[session_id()]['MENU'][$menuid]['DELETE'] == 1 ) {
				echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'D', '".$arr['COMPANY_ID']."');\">ลบ</button>");
			}
			else {
				echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'D', '".$arr['COMPANY_ID']."');\" disabled>ลบ</button>");
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