<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<link rel="stylesheet" href="css/bootstrap.min.css">
<link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="css/fontawesome-all.min.css" rel="stylesheet" type="text/css">

<link rel="stylesheet" href="css/signin.css">
<link href="css/simple-line-icons.css" rel="stylesheet">

<link rel="stylesheet" href="css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="css/bootstrap-select.min.css">
<link rel="stylesheet" href="css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="css/select.bootstrap4.min.css">
<link rel="stylesheet" href="css/fixedHeader.bootstrap4.min.css">
<link href="css/bootstrap-material-datetimepicker.css" rel="stylesheet" type="text/css">
<link href="css/fullcalendar.min.css" rel="stylesheet" type="text/css">
<link href="css/fullcalendar.print.min.css" rel="stylesheet"media="print">
<link rel="stylesheet" href="css/ripples.min.css"/>
<link href="css/jquery.fileupload.css" rel="stylesheet" type="text/css">
<link href="css/jquery.fileupload-ui.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="css/style.css">

<!--<link rel="stylesheet" href="css/style.css">-->
<title>IRCP</title>
</head>
<?
	
	if (session_id() == '') {
		session_start();
	}

	if (empty($_SESSION[session_id()]['USER'])) {
		$userid = '';
	}
	else {
		$userid = $_SESSION[session_id()]['USER'];
	}
	
	if ($userid == '') {
		echo("<body>");
	}
	else {
		echo("<body onload= 'startSessionPage();'>");
	}
	echo("<div class='m-backdrop' id='mbackdrop' style='display:none'>");
	echo("<div class='mbackdrop-center' align ='center'> ");

	echo("<i class='fa fa-spinner fa-spin fa-5x fa-fw'></i>");
	echo("<span class='sr-only'>Loading...</span>");
	echo("</div>");
	echo("</div>");

	if ($userid == '') {
		echo("<form class='form-signin'>");
//			echo("<h3>ของดใช้งานตั้งแต่วันที่ 14 มิถุนายน 2562 - 18 มิถุนายน 2562</h3>");
		
		echo("<div class = 'card border-secondary mb-3'>");
	
		echo("<div class = 'card-body'>");
		echo("<div class='form-group'>");
		echo("<img class='form-signin-heading' src='img/logo.png' width='100'>");
		echo("</div>");						
		echo("<div class='form-group'>");
		echo("<input type='text' id='username' class='form-control' placeholder='&#xf007 รหัสผู้ใช้งาน' style='font-family:Prompt, FontAwesome' required autofocus>");
		echo("</div>");	
		echo("<div class='form-group'>");
		echo("<input type='password' id='password' class='form-control' placeholder='&#xf023 รหัสผ่าน' style='font-family:Prompt, FontAwesome' >");
		echo("</div>");							
		echo("<div class ='row justify-content-center'>");
		echo("<div class='col-md-auto col-xs-auto col-sm-auto'>");	
		echo("<div class='input-group'>");
//		echo("<a href='' class='btnx btnx-outline btnx-xl js-scroll-trigger' style='text-decoration: none' onclick=\"windowProcess();return false;\" disable> ลืมรหัสผ่าน</a>");
		echo("<a href='' class='btnx btnx-outline btnx-xl js-scroll-trigger' style='text-decoration: none' onclick=\"windowProcess('1','0','','');return false;\"> เข้าสู่ระบบ <i class='fas fa-arrow-circle-right'></i></a>");
		echo("</div>");	
		echo("</div>");	
		echo("</div>");	

		echo("</div>");
		echo("</div>");		
		
		echo("</form>");
	}
	else {
		include ('menu.php');
	}
?>


<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalTitle" aria-hidden="true">
<div class="modal-dialog modal-lg" role="document">
<div class="modal-content" id='myModalContent'>
<div class="modal-header">
	<h5 class="modal-title"></h5>
</div>
<div class="modal-body">

</div>
<div class="modal-footer">
	<button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
	<button type="button" class="btn btn-info">บันทึก</button>
</div>
</div>
</div>
</div>

<div class="modal fade" id="session-expire-warning-modal" aria-hidden="true" data-keyboard="false" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
<div class="modal-dialog" role="document">
<div class="modal-content">
<div class="modal-body">
Session ของคุณจะหมดอายุภายใน <span id="seconds-timer"></span> วินาที หากต้องการใช้งานต่อ กรุณากดปุ่มตกลง
</div>
<div class="modal-footer">
<div class="btn-group" role="group" aria-label="Basic example">
<button id="btnOk" type="button" class="btn  btn-info" >ตกลง</button>
<button id="btnLogoutNow" type="button" class="btn btn-danger" >ออกจากระบบ</button>
</div>
</div>
</div>
</div>
</div>

<div class="modal fade" id="session-expired-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
<div class="modal-dialog" role="document">
<div class="modal-content">
<div class="modal-body">
Session ของคุณไม่สามารถใช้งานได้แล้ว หากต้องการใช้งาน กรุณาเข้าสู่ระบบใหม่
</div>
<div class="modal-footer">
<button id="btnExpiredOk" onclick="sessionExpiredRedirect()" type="button" class="btn  btn-info" data-dismiss="modal" >ตกลง</button>
</div>
</div>
</div>
</div>

<script src="js/md5.js" ></script>	
<script src="js/jquery.min.js"></script>
<script src="js/popper.min.js" ></script>
<script src="js/bootstrap.min.js" ></script>
<script src="js/jquery.main.js" ></script>

<script src="js/jquery.dataTables.min.js" ></script>
<script src="js/dataTables.bootstrap4.min.js" ></script>
<script src="js/dataTables.responsive.min.js" ></script>
<script src="js/responsive.bootstrap4.min.js" ></script>
<script src="js/dataTables.buttons.min.js" ></script>
<script src="js/buttons.bootstrap4.min.js" ></script>
<script src="js/jszip.min.js" ></script>
<script src="js/pdfmake.min.js" ></script>
<script src="js/vfs_fonts.js" ></script>
<script src="js/buttons.html5.min.js" ></script>
<script src="js/buttons.print.min.js" ></script>
<script src="js/buttons.colVis.min.js" ></script>

<script src="js/dataTables.fixedHeader.min.js" ></script>
<script src="js/dataTables.select.min.js" ></script>
<script src="js/bootstrap-select.min.js" ></script>
<script src="js/moment-with-locales.min.js"></script>
<script src="js/bootstrap-material-datetimepicker.js"></script>
<script src="js/material.min.js"></script>
<script src="js/ripples.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/fullcalendar.min.js"></script>
<script src="js/locale-all.js"></script>
<script src="js/jquery.ui.widget.js"></script>
<script src="js/jquery.iframe-transport.js"></script>
<script src="js/jquery.fileupload.js"></script>
<script src="js/loader.js"></script>
<script src="js/jquery-sortable.js"></script>
</body>
</html>