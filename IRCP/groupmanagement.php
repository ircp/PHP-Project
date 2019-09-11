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
	echo("<br>");
	echo("<div class='form-group'>");
	echo("<div class='col-md-12 col-xs-12 col-sm-12'>");	
	echo("<div class='input-group mb-3'>");
	echo("<input type='text' class='form-control' placeholder='' id='searchparameter' maxlength='50' size='50'>");
	echo("<div class='input-group-append'>");
    echo("<button class='btn btn-outline-info' type='button' onclick=\"windowOpen('2', '".$menuid."', '', '');return false;\"><i class='fas fa-search'></i></button>");
	echo("</div>");
	echo("</div>");
	echo("</div>");
	echo("</div>");	
	echo("<br>");
	echo("<div class='form-group'>");
	echo("<div class='d-flex justify-content-center'>");	
	echo("<div class='feature-item'>");
	echo("<div class='input-group'>");
	if ($_SESSION[session_id()]['MENU'][$menuid]['ADD'] == 1) {
		echo("<a href='' class='btnx-info btnx-xl js-scroll-trigger' style='text-decoration: none' data-toggle='modal' data-target='#myModal' onclick = \"windowOpen('3', '".$menuid."', 'A','');return false;\"><i class='fas fa-plus-circle'></i> เพิ่มข้อมูล</a>");
	}
	echo("</div>");
	echo("</div>");
	echo("</div>");
	echo("</div>");
	
	echo("</div>");
	echo("</div>");
	echo("</div>");	
?>