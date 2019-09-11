<?
	require ('connection.php');
	
	try {		
		$disable = '';

		if ($mode == 'D') {
			$disable = 'disabled';
		}		
		
		$explode = explode("|", $key);
		
		$workingdate = substr($explode[1],8,2)."/".substr($explode[1],5,2)."/".((int)substr($explode[1],0,4) + 543);
		
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
		echo("<button type='button' class='btn btn-info' onclick = \"windowProcess('3', '".$menuid."', '".$mode."', '".$key."');\">ส่งอนุมัติ</button>");
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