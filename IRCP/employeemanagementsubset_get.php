<?
	require ('connection.php');
	
	try {

		$disable = '';


		$explode = explode("|", $key);
		$employeeid = $explode[0];
		$workingdate =  substr($explode[1],8,2)."/".substr($explode[1],5,2)."/".((int)substr($explode[1],0,4) + 543);

		echo("<div class='container'>");
		echo("<div class='modal-body'>");
		
		echo("<form>");

        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>วันที่</label>");
        echo("<input type='text' class='form-control is-valid' value='".$workingdate."' disabled maxlength='80' size='80' >");
        echo("</div>");	
			
        echo("<div class='form-group'>");
        echo("<label for='recipient-name' class='col-form-label'>รายละเอียด</label>");
        echo("<textarea class='form-control is-valid' id='description' rows='3' ".$disable." maxlength='200'></textarea>");
        echo("</div>");			
		
		echo("</form>");
		echo("</div>");
		echo("<div class='modal-footer'>");
		echo("<div class='btn-group' role='group' aria-label='Basic example'>");
		echo("<button type='button' class='btn btn-secondary' data-dismiss='modal'  id='closeButton'>ปิด</button>");
		if ($mode == 'D') {
			echo("<button type='button' class='btn btn-info' onclick = \"windowProcess('6', '".$menuid."', '".$mode."', '".$key."');\">บันทึกเป็นวันลา</button>");
		}
		else {
			echo("<button type='button' class='btn btn-info' onclick = \"windowProcess('6', '".$menuid."', '".$mode."', '".$key."');\">อนุมัติ</button>");
		}
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