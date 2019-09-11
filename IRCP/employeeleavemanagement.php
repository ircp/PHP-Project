<?
	echo("<div class ='row'>");
	echo("<div class='card text-white bg-info mb-12'>");

	echo("<div class = 'card-body'>");
	echo("<div class='form-group'>");
	echo("<div class='d-flex justify-content-center'>");
	echo("<div class='feature-item'>");
	echo("<div class='input-group'>");
	echo("<button type='button' class='btny-info btnx-xl js-scroll-trigger'>".$_SESSION[session_id()]['MENU'][$menuid]['MENU_NAME']."</button> ");
	echo("</div>");
	echo("</div>");
	echo("</div>");
	echo("</div>");
/*
	echo("<br>");
	echo("<div class='form-group' >");
	echo("<div class='col-md-12 col-xs-12 col-sm-12'>");	
	echo("<div class='input-group mb-3'>");
	echo("<input type='text' class='form-control' placeholder='' id='searchparameter' maxlength='50' size='50'>");
	echo("<div class='input-group-append'>");
    echo("<button class='btn btn-outline-info' type='button' onclick=\"windowOpen('2', '".$menuid."', '', '');return false;\"><i class='fas fa-search'></i></button>");
	echo("</div>");
	echo("</div>");
	echo("</div>");
	echo("</div>");	
*/
	$currentyear = (int)date("Y") + 543;
	$currentmonth = (int)date("m");
	
	echo("<br>");
	echo("<div class='form-group'>");
	echo("<div class='col-md-12 col-xs-12 col-sm-12'>");	
	echo("<div class='input-group mb-4'>");
	echo("<select class='selectpicker form-control' id='yearselected' >");
	for ($x = 0; $x < 3; $x++) {
		echo("<option value='".$currentyear."'>ปี ".$currentyear."</option>");
		$currentyear = $currentyear - 1;
	}
	echo("</select>");
	echo("</div>");
	echo("</div>");
	echo("</div>");	
	echo("<div class='form-group'>");
	echo("<div class='col-md-12 col-xs-12 col-sm-12'>");	
	echo("<div class='input-group mb-4'>");
	echo("<select class='selectpicker form-control' id='monthselected' >");
	if ($currentmonth == 1) {
		echo("<option value='1' selected>มกราคม</option>");
	}
	else {
		echo("<option value='1' >มกราคม</option>");
	}
	if ($currentmonth == 2) {
		echo("<option value='2' selected>กุมภาพันธ์</option>");
	}
	else {
		echo("<option value='2' >กุมภาพันธ์</option>");
	}
	if ($currentmonth == 3) {
		echo("<option value='3' selected>มีนาคม</option>");
	}
	else {
		echo("<option value='3' >มีนาคม</option>");
	}
	if ($currentmonth == 4) {
		echo("<option value='4' selected>เมษายน</option>");
	}
	else {
		echo("<option value='4' >เมษายน</option>");
	}
	if ($currentmonth == 5) {
		echo("<option value='5' sel\>พฤษภาคม</option>");
	}
	else {
		echo("<option value='5'>พฤษภาคม</option>");
	}
	if ($currentmonth == 6) {
		echo("<option value='6' selected>มิถุนายน</option>");
	}
	else {
		echo("<option value='6'>มิถุนายน</option>");
	}
	if ($currentmonth == 7) {
		echo("<option value='7' selected>กรกฎาคม</option>");
	}
	else {
		echo("<option value='7'>กรกฎาคม</option>");
	}
	if ($currentmonth == 8) {
		echo("<option value='8' selected>สิงหาคม</option>");
	}
	else {
		echo("<option value='8'>สิงหาคม</option>");
	}
	if ($currentmonth == 9) {
		echo("<option value='9' selected>กันยายน</option>");
	}
	else {
		echo("<option value='9'>กันยายน</option>");
	}
	if ($currentmonth == 10) {
		echo("<option value='10' selected>ตุลาคม</option>");
	}
	else {
		echo("<option value='10'>ตุลาคม</option>");
	}
	if ($currentmonth == 11) {
		echo("<option value='11' selected>พฤศจิกายน</option>");
	}
	else {
		echo("<option value='11'>พฤศจิกายน</option>");
	}
	if ($currentmonth == 12) {
		echo("<option value='12' selected>ธันวาคม</option>");
	}
	else {
		echo("<option value='12'>ธันวาคม</option>");
	}
	echo("</select>");
	echo("</div>");
	echo("</div>");
	echo("</div>");	

	echo("<div class='form-group'>");
	echo("<div class='d-flex justify-content-center'>");	
	echo("<div class='feature-item'>");
	echo("<div class='input-group'>");

	echo("<a href='' class='btnx-info btnx-xl js-scroll-trigger' style='text-decoration: none' onclick=\"windowOpen('2', '".$menuid."', '', '');return false;\"><i class='fas fa-search'></i> ค้นหา</a>");
	
	echo("</div>");
	echo("</div>");
	echo("</div>");
	echo("</div>");

	echo("<div class='form-group' style='display:none'>");
	echo("<div class='col-md-12 col-xs-12 col-sm-12'>");	
	echo("<div class='input-group mb-3'>");
	echo("<input type='text' class='form-control' placeholder='' id='searchparameter' maxlength='50' size='50'>");
	echo("<div class='input-group-append'>");
    echo("<button class='btn btn-outline-info' type='button' onclick=\"windowOpen('2', '".$menuid."', '', '');return false;\"><i class='fas fa-search'></i></button>");
	echo("</div>");
	echo("</div>");
	echo("</div>");
	echo("</div>");	
	
	echo("</div>");
	echo("</div>");
	echo("</div>");	
?>