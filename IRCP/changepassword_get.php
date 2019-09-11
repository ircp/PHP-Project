<?
	require ('connection.php');
	
	try {
		echo("<div class='modal-body'>");
		
		echo("<form>");
		echo("<div class='form-group row'>");
		echo("<label for='lastPassword' class='col-md-3  col-form-label'>รหัสผ่านเดิม</label>");
		echo("<div class='col-md-9'>");
		echo("<input type='password' class='form-control is-invalid' id='lastpassword' placeholder='รหัสผ่านเดิม' maxlength='15' size='15'>");
		echo("</div>");
		echo("</div>");	
		echo("<div class='form-group row'>");
		echo("<label for='newPassword' class='col-md-3 col-form-label'>รหัสผ่านใหม่</label>");
		echo("<div class='col-md-9'>");
		echo("<input type='password' class='form-control is-invalid' id='newpassword' placeholder='รหัสผ่านใหม่' maxlength='15' size='15'>");
		echo("</div>");
		echo("</div>");			
		echo("</form>");
		echo("</div>");
		echo("<div class='modal-footer'>");
		echo("<div class='btn-group' role='group' aria-label='Basic example'>");
		echo("<button type='button' class='btn btn-secondary' data-dismiss='modal'  id='closeButton'>ปิด</button>");
		echo("<button type='button' class='btn btn-info' onclick = \"windowProcess('3', '0', '', '');\">บันทึก</button>");
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