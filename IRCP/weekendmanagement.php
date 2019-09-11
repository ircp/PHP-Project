<?

	$currentyear = ((int) date("Y") + 543);
	echo("<div class ='row'>");
	echo("<div class='card text-white bg-info mb-12' style='width: 30rem;'>");

	echo("<div class = 'card-body'>");
	echo("<div class='form-group'>");
	echo("<div class='d-flex justify-content-center'>");
	echo("<div class='feature-item'>");
	echo("<div class='input-group'>");
	echo("<button type='button' class='btny-info btnx-xl js-scroll-trigger'>สรุปวันหยุดประจำปี ".$currentyear."</button> ");
	echo("</div>");
	echo("</div>");
	echo("</div>");
	echo("</div>");
	echo("<br>");
	
	include_once ("weekendmanagement_summary.php");

	echo("<br>");
	echo("<div class='form-group'>");
	echo("<div class='d-flex justify-content-center'>");	
	echo("<div class='feature-item'>");
	echo("<div class='input-group'>");
//	if ($_SESSION[session_id()]['MENU'][$menuid]['ADD'] == 1) {
//		echo("<a href='' class='btnx-info btnx-xl js-scroll-trigger' style='text-decoration: none' data-toggle='modal' data-target='#myModal' onclick = \"windowOpen('3', '".$menuid."', 'A','');return false;\"><i class='fas fa-plus-circle'></i> เพิ่มข้อมูล</a>");
//	}
	echo("</div>");
	echo("</div>");
	echo("</div>");
	echo("</div>");
	
	echo("</div>");
	
	echo("</div>");
	echo("</div>");	
?>