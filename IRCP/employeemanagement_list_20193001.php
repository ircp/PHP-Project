<?
	require ('connection.php');
	
	try {
		
		$search = '%'.$parameter."%";
		
		
		if ($userid == 1) {
			$sql = "	SELECT EMPLOYEE.EMPLOYEE_CODE, EMPLOYEE.TITLE, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, EMPLOYEE.EMPLOYEE_STATUS, 
						EMPLOYEE.DEPARTMENT_ID, EMPLOYEE.POSITION_ID, EMPLOYEE.EMAIL, EMPLOYEE.SEX, EMPLOYEE.EMPLOYEE_ID,
						RC1.LABEL EMPLOYEESTATUS, DEPARTMENT.DEPARTMENT_NAME, RC2.LABEL POSITION
						FROM EMPLOYEE EMPLOYEE
						LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 5 AND RC1.REFERENCE_CODE = EMPLOYEE.EMPLOYEE_STATUS
						LEFT JOIN REFERENCE_CODE RC2 ON RC2.REFERENCE_TYPE_ID = 4 AND RC2.REFERENCE_CODE = EMPLOYEE.POSITION_ID
						LEFT JOIN DEPARTMENT DEPARTMENT ON DEPARTMENT.DEPARTMENT_ID = EMPLOYEE.DEPARTMENT_ID
						WHERE SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE 
						AND (DEPARTMENT.DEPARTMENT_NAME LIKE ? OR EMPLOYEE.FIRST_NAME LIKE ? OR EMPLOYEE.LAST_NAME LIKE ? )
						ORDER BY DEPARTMENT.DEPARTMENT_NAME, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME";				
		}
		else {
			$sql = "	SELECT EMPLOYEE.EMPLOYEE_CODE, EMPLOYEE.TITLE, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, EMPLOYEE.EMPLOYEE_STATUS, 
						EMPLOYEE.DEPARTMENT_ID, EMPLOYEE.POSITION_ID, EMPLOYEE.EMAIL, EMPLOYEE.SEX, EMPLOYEE.EMPLOYEE_ID,
						RC1.LABEL EMPLOYEESTATUS, DEPARTMENT.DEPARTMENT_NAME, RC2.LABEL POSITION
						FROM EMPLOYEE EMPLOYEE
						LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 5 AND RC1.REFERENCE_CODE = EMPLOYEE.EMPLOYEE_STATUS
						LEFT JOIN REFERENCE_CODE RC2 ON RC2.REFERENCE_TYPE_ID = 4 AND RC2.REFERENCE_CODE = EMPLOYEE.POSITION_ID
						LEFT JOIN DEPARTMENT DEPARTMENT ON DEPARTMENT.DEPARTMENT_ID = EMPLOYEE.DEPARTMENT_ID
						WHERE SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE 
						AND EMPLOYEE.EMPLOYEE_ID <> 1
						AND (DEPARTMENT.DEPARTMENT_NAME LIKE ? OR EMPLOYEE.FIRST_NAME LIKE ? OR EMPLOYEE.LAST_NAME LIKE ? )
						ORDER BY DEPARTMENT.DEPARTMENT_NAME, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME";			
		}
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($search, $search, $search));	
		
		echo("<div class ='row'>");
		echo("<table class = 'table table-striped display' cellspacing='0' cellspadding = '0' width='100%' id = 'listTable_".$menuid."'>");
		echo("<thead>");
		echo("<tr>");
		echo("<th class = 'text-center'></th>");
		echo("<th class = 'text-center'>ลำดับที่</th>");
		echo("<th class = 'text-center'>รหัสพนักงาน</th>");
		echo("<th class = 'text-center'>ชื่อ-นามสกุล</th>");
		echo("<th class ='text-center'>แผนก/ฝ่าย</th>");
		echo("<th class ='text-center'>ตำแหน่ง</th>");
		echo("<th class ='text-center'>สถานะ</th>");
		echo("<th class ='text-center'>แก้ไข</th>");		
		echo("</tr>");
		echo("</thead>");
		echo("<tbody>");
		$i = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i +1;
			echo("<tr>");
			echo("<td class = 'text-left'>".$arr['EMPLOYEE_ID']."</td>");
			echo("<td class = 'text-center'>".$i."</td>");	
			echo("<td class = 'text-center'>".$arr['EMPLOYEE_CODE']."</td>");
			echo("<td class = 'text-left'>".$arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME']."</td>");	
			echo("<td class = 'text-left'>".$arr['DEPARTMENT_NAME']."</td>");	
			echo("<td class = 'text-left'>".$arr['POSITION']."</td>");			
			if ($arr['EMPLOYEE_STATUS'] == 1) {
				echo("<td class = 'text-center'><font color = '#00C851'>".$arr['EMPLOYEESTATUS']."</font></td>");	
			}
			else {
				echo("<td class = 'text-center'><font color = '#CC0000'>".$arr['EMPLOYEESTATUS']."</font></td>");	
			}
			echo("<td  class='text-center'>");
			echo("<div class='btn-group' role='group' aria-label='Basic example'>");
			echo("<button type='button' class='btn  btn-outline-success' id='subtable' >รายละเอียด</button>");
			if ($_SESSION[session_id()]['MENU'][$menuid]['UPDATE'] == 1) {
				echo("<button type='button' class='btn  btn-outline-success' data-toggle='modal' data-target='#myModal' onclick = \"windowOpen('5', '".$menuid."', 'A', '".$arr['EMPLOYEE_ID']."|');\">เพิ่มวันลา</button>");
//				echo("<button type='button' class='btn  btn-outline-success' data-toggle='modal' data-target='#myModal' onclick = \"windowOpen('5', '".$menuid."', 'A', '".$arr['EMPLOYEE_ID']."|');\">เพิ่มวันมาสาย</button>");
				echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$arr['EMPLOYEE_ID']."');\">แก้ไข</button>");
			}
			else {
				echo("<button type='button' class='btn  btn-outline-success' data-toggle='modal' data-target='#myModal' onclick = \"windowOpen('5', '".$menuid."', 'A', '".$arr['EMPLOYEE_ID']."');\" disabled>เพิ่มวันลา</button>");
//				echo("<button type='button' class='btn  btn-outline-success' data-toggle='modal' data-target='#myModal' onclick = \"windowOpen('5', '".$menuid."', 'A', '".$arr['EMPLOYEE_ID']."|');\" disabled>เพิ่มวันมาสาย</button>");
				echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'C', '".$arr['EMPLOYEE_ID']."');\" disabled>แก้ไข</button>");				
			}
			if ($_SESSION[session_id()]['MENU'][$menuid]['DELETE'] == 1 ) {
				echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'D', '".$arr['EMPLOYEE_ID']."');\">ลบ</button>");
			}
			else {
				echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('3', '".$menuid."', 'D', '".$arr['EMPLOYEE_ID']."');\" disabled>ลบ</button>");
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