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
	
	echo("</div>");
	echo("</div>");
	echo("</div>");	
?>