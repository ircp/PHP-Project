<?
	require ('connection.php');
	
	try {
		$sql = "	SELECT WEEKEND_ID, WEEKEND_NAME, WEEKEND_DATE 
					FROM WEEKEND
					WHERE WEEKEND_DATE = ?";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($parameter));

		$weekendid = '';
		$weekendname = '';
		$weekenddate = '';
		
		$mode = 'A';
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$weekendid = $arr['WEEKEND_ID'];
			$weekendname = $arr['WEEKEND_NAME'];
			$weekenddate = $arr['WEEKEND_DATE'];
			$mode = 'C';
		}
		
		$weekend = substr($parameter,8,2)."/".substr($parameter,5,2)."/".((int)substr($parameter,0,4) + 543);

		$disable = '';

		if ($mode == 'D') {
			$disable = 'disabled';
		}	
		
		echo("<div class='container'>");
		echo("<div class='modal-body'>");
		
		echo("<form>");
		
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>วันที่</label>");
		echo("<input type='text'  class='form-control is-invalid' id='weekenddate'  value = '".$weekend."' disabled maxlength='20' size='20'>");
        echo("</div>");			

        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>ชื่อวันหยุด</label>");
        echo("<input type='text' class='form-control is-invalid' id='description' value='".$weekendname."' ".$disable." maxlength='150' size='150' >");
        echo("</div>");	
		
		echo("</form>");
		echo("</div>");
		echo("<div class='modal-footer'>");
		echo("<div class='btn-group' role='group' aria-label='Basic example'>");
		echo("<button type='button' class='btn btn-secondary' data-dismiss='modal'  id='closeButton'>ปิด</button>");
		if ($mode != 'A') {
			echo("<button type='button' class='btn btn-danger' onclick = \"windowProcess('3', '".$menuid."', 'D', '".$weekendid."');\">ลบ</button>");
		}
		echo("<button type='button' class='btn btn-info' onclick = \"windowProcess('3', '".$menuid."', '".$mode."', '".$weekendid."');\">บันทึก</button>");
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