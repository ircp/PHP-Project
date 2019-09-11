<?
	require ('connection.php');
	
	try {
		include ('menu_process.php');
		
		echo("<nav class='navbar navbar-expand-md navbar-dark fixed-top bg-dark justify-content-between' id='mainNav'>");
		echo("<a class='navbar-brand' href='#'><img src= 'img/logo.png' width='150'></a>");
		echo("<button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarCollapse' aria-controls='navbarCollapse' aria-expanded='false' aria-label='Toggle navigation'>");
		echo("<span class='navbar-toggler-icon'></span>");
		echo("</button>");
		echo("<div class='collapse navbar-collapse' id='navbarCollapse'>");
		echo("<ul class='navbar-nav navbar-right'>");
		$childcount = 0;		

		foreach ($PARENT as $menuid => $arr) {
			if ($arr['CHILD_COUNT'] == 0) {
				if ($_SESSION[session_id()]['FIRST_MENU'] == $menuid) {
					echo("<li class='nav-item active'>");
				}
				else {
					echo("<li class='nav-item'>");
				}
					
				if ($MENU[$menuid]['MENU_IMAGE'] == '') {
					if ($menuid == 11) {
						echo("<a class='nav-link' href='' onclick=\"windowOpen('1','".$menuid."','','');return false;\">".$arr['MENU_NAME']." <span class='badge badge-pill badge-danger' id='approved_count'>".$approved_count."</span></a>");
					}
					else {
						echo("<a class='nav-link' href='' onclick=\"windowOpen('1','".$menuid."','','');return false;\">".$arr['MENU_NAME']."</a>");	
					}
				}
				else {
					if ($menuid == 11) {
						echo("<a class='nav-link' href='' onclick=\"windowOpen('1','".$menuid."','','');return false;\"><span class='".$arr['MENU_IMAGE']."'></span> ".$arr['MENU_NAME']." <span class='badge badge-pill badge-danger' id='approved_count'>".$approved_count."</span></a>");
					}
					else {
						echo("<a class='nav-link' href='' onclick=\"windowOpen('1','".$menuid."','','');return false;\"><span class='".$arr['MENU_IMAGE']."'></span> ".$arr['MENU_NAME']."</a>");
					}
				}
				echo("</li>");
			}
			else {
				if ($_SESSION[session_id()]['FIRST_MENU'] == $menuid) {
					echo("<li class='nav-item dropdown active'>");
				}
				else {
					echo("<li class='nav-item dropdown'>");
				}
				echo("<a class='nav-link dropdown-toggle' href='#' id='navbarDropdownMenuLink' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>".$arr['MENU_NAME']."</a>");
				echo("<div class='dropdown-menu' aria-labelledby='navbarDropdownMenuLink'>");
				$childcount = 0;		
				foreach ($CHILD[$menuid] as $childid => $CRESULT) {
					$childcount = $childcount + 1;
					
					if ($MENU[$childid]['MENU_IMAGE'] == '') {
						if ($childid == 11) {
							echo("<a class='dropdown-item' href='' onclick=\"windowOpen('1', '".$childid."','','');return false;\">".$CRESULT['MENU_NAME']." <span class='badge badge-pill badge-danger' id='approved_count'>".$approved_count."</span></a>");		
						}
						else {
							echo("<a class='dropdown-item' href='' onclick=\"windowOpen('1', '".$childid."','','');return false;\">".$CRESULT['MENU_NAME']."</a>");		
						}
					}
					else {
						if ($childid == 11) {
							echo("<a class='dropdown-item' href='' onclick=\"windowOpen('1', '".$childid."','','');return false;\"><span class='".$MENU[$childid]['MENU_IMAGE']."'></span> ".$CRESULT['MENU_NAME']." <span class='badge badge-pill badge-danger' id='approved_count'>".$approved_count."</span></a>");
						}
						else {
							echo("<a class='dropdown-item' href='' onclick=\"windowOpen('1', '".$childid."','','');return false;\"><span class='".$MENU[$childid]['MENU_IMAGE']."'></span> ".$CRESULT['MENU_NAME']."</a>");
						}
					}
					
					if ($childcount < $arr['CHILD_COUNT']) {
						echo("<div class='dropdown-divider'></div>");
					}
				}
				echo("</div>");
				echo("</li>");
			}
		}
		echo("</ul>");
		
		echo("<ul class='navbar-nav flex-row ml-md-auto d-none d-md-flex'>");

		echo("<li class='nav-item dropdown'>");
		echo("<a class='nav-link dropdown-toggle' href='#' id='navbarDropdownUserLink' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'> ".$_SESSION[session_id()]['USER_NAME']." </a>");
		echo("<div class='dropdown-menu dropdown-menu-right' aria-labelledby='navbarDropdownUserLink'>");
		echo("<a class='dropdown-item' href='#' data-toggle='modal' data-target='#myModal' onclick = \"windowOpen('3', '0', '', '".$_SESSION[session_id()]['USER']."');\">เปลี่ยนรหัสผ่าน</a>");
		echo("<div class='dropdown-divider'></div>");
		echo("<a class='dropdown-item' href='' onclick = \"windowProcess ('2', '0', '', '');\">ออกจากระบบ</a>");
		echo("</div>");
		echo("</li>");
		
		echo("</ul>");
		
		echo("</div>");
		echo("</nav>");

		echo("<div class='container-fluid'>");
		echo("<div class = 'row'>");
		echo("<nav class='col-md-2 d-none d-md-block bg-light sidebar'>");
		echo("<div class='sidebar-sticky' id='mainDisplay'>");
			$current_url = $MENU[$_SESSION[session_id()]['CURRENT_MENU']]['MENU_URL'].".php";
			
			if ($current_url != '') {
				$menuid = $_SESSION[session_id()]['CURRENT_MENU'];
				$curren_menuid = $_SESSION[session_id()]['CURRENT_MENU'];
				include ($current_url);
			}		
		echo("</div>");
		echo("</nav>");
		
		echo("<main role='main' class='col-md-9 ml-sm-auto col-lg-10 px-4'>");
		echo("<div  id='listDisplay' >");
			$current_url = $MENU[$_SESSION[session_id()]['CURRENT_MENU']]['MENU_URL']."_list.php";
			$parameter = '';
			if ($current_url != '') {
				include ($current_url);
			}

		echo("</div>");		
		echo("</main>");
		echo("</div>");
		echo("</div>");
		
		echo("<input type = 'hidden' id='current_menu_id' value = '".$curren_menuid."'>");
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;
?>