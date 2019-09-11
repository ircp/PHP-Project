<?
	
	$operatorid = $_SESSION['USER_ID'];
	$menuid = $_SESSION['CURRENT_MENU'];
	$MENU = $_SESSION['MENU'];
	
	echo("<div class = 'card border-info'>");
		echo("<div class = 'card-header text-white bg-info'>");
		if ($MENU[$menuid]['ADD'] == 1) {
			echo("<button type='button' class='btn btn-outline-light btn-sm' data-toggle='modal' data-target='#myModal' onclick = \"windowOpen('3', '".$menuid."', '', 'A','','".$menuid."') ;\"><span class='fa fa-plus-circle' aria-hidden='true' ></span></button> ".$MENU[$menuid]['MENU_NAME']);
		}
		else {
			echo($MENU[$menuid]['MENU_NAME']);
		}
		echo("</div>");
		echo("<div class = 'card-body'>");
			echo("<div class='form-group row'>");
				echo("<div class='col-xs-12 col-sm-12 col-md-4'>");
					echo("<div class='input-group'>");
						echo("<input type='text' class='form-control'	placeholder='กรุณาใส่ข้อมูล...' aria-label='กรุณาใส่ข้อมูล...' id='searchValue'>");
						echo("<span class='input-group-btn'>");
//						echo("<button class='btn btn-info' type='button'  onclick = \"searchList('".$menuid."');\"><span class='fa fa-search' aria-hidden='true' ></span></button>");
						echo("<button class='btn btn-info' type='button'  onclick = \"windowOpen('2','".$menuid."','".$MENU[$menuid]['MENU_NAME']."','".$menuid."');\">ค้นหา</button>");
						echo("</span>");
					echo("</div>");
				echo("</div>");
			echo("</div>");	
		echo("</div>");	
	echo("</div>");
	echo("<br>");
	echo("<div class = 'card border-info' id ='searchHeader' style='display:none'>");
		echo("<div class = 'card-body' id='searchBody'>");
		
		echo("</div>");
	echo("</div>");
	
?>
