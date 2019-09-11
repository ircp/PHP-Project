<?
	require ('connection.php');
	
	try {
		
		$search = '%'.$parameter."%";
		
		$sql = "	SELECT GROUP_MENU.MENU_ID, GROUP_MENU.PERMISSION, GROUP_MENU.FIRST_MENU, GROUP_MENU.MENU_LEVEL, GROUP_MENU.MENU_ORDER, GROUP_MENU.PARENT_MENU_ID, GROUP_MENU.VALID_IND_CODE,
					MENU.MENU_NAME, MENU_1.MENU_NAME PARENT_MENU, RC.LABEL
					FROM GROUP_MENU GROUP_MENU
					LEFT JOIN MENU MENU ON MENU.MENU_ID = GROUP_MENU.MENU_ID
					LEFT JOIN MENU MENU_1 ON MENU_1.MENU_ID = GROUP_MENU.PARENT_MENU_ID
					LEFT JOIN REFERENCE_CODE RC ON RC.REFERENCE_TYPE_ID = 9 AND RC.REFERENCE_CODE = MENU.OBJECT_TYPE
					WHERE GROUP_MENU.GROUP_ID = ?
					ORDER BY GROUP_MENU.PARENT_MENU_ID, GROUP_MENU.MENU_ORDER, MENU.MENU_NAME ASC";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($key));		
		
		$i = 0;
		
		unset($RESULT);
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$viewflag = 0;
			$updateflag = 0;
			$deleteflag = 0;
			$addflag = 0;
		
			$permission = $arr['PERMISSION'];
			if ($permission >= (pow(2,4))) {
				$viewflag = 1;
				$permission = $permission - (pow(2,4));
			}
			if ($permission >= (pow(2,3))) {
				$updateflag = 1;
				$permission = $permission - (pow(2,3));
			}
			if ($permission >= (pow(2,2))) {
				$deleteflag = 1;
				$permission = $permission - (pow(2,2));
			}
			if ($permission >= (pow(2,1))) {
				$addflag = 1;
				$permission = $permission - (pow(2,1));
			}
			
			$i = $i +1;
			$RESULT[$i]['MENU_ID'] = $arr['MENU_ID'];
			$RESULT[$i]['PERMISSION'] = $arr['PERMISSION'];
			$RESULT[$i]['FIRST_MENU'] = $arr['FIRST_MENU'];
			$RESULT[$i]['MENU_LEVEL'] = $arr['MENU_LEVEL'];
			$RESULT[$i]['MENU_ORDER'] = $arr['MENU_ORDER'];
			$RESULT[$i]['PARENT_MENU_ID'] = $arr['PARENT_MENU_ID'];
			$RESULT[$i]['VALID_IND_CODE'] = $arr['VALID_IND_CODE'];
			$RESULT[$i]['MENU_NAME'] = $arr['MENU_NAME'];
			$RESULT[$i]['PARENT_MENU'] = $arr['PARENT_MENU'];
			$RESULT[$i]['ADD'] = $addflag ;		
			$RESULT[$i]['UPDATE'] = $updateflag;		
			$RESULT[$i]['DELETE'] = $deleteflag;		
			$RESULT[$i]['VIEW'] = $viewflag;	
			$RESULT[$i]['OBJECT_TYPE'] = $arr['LABEL'];			
		}
		
		$total = $i;

		if ($total <= 5) {
			$sumpage = 1;
		}
		else {
			$temp = $total/5;
			$sumpage = ceil($temp);
		}
		
		$recordcount = 0;
		$pagecount = 1;
		
		echo("<div id='carouselExampleControls' class = 'carousel slide'  data-ride= 'carousel'>");
		echo("<div class = 'carousel-inner'>");
		echo("<div class='card border'>");
		echo("<div class='card-body'>");
		if ($total > 0) {
			$n = 0;
			$i = 0;
			
			foreach ($RESULT as $x => $mresult) {
				$i = $i + 1;
				$recordcount = $recordcount + 1;
				$n = $n + 1;
				if ($recordcount == 1) {
					if ($n == 1) {
						echo("<div class='carousel-item active'>");
					}
					else {
						echo("<div class='carousel-item'>");
					}
//					echo("<div class='container'>");
					echo("<table class = 'table table-bordered dt-responsive' cellspacing='0' cellspadding = '0' width='100%'>");
					echo("<thead>");
					echo("<tr>");
					echo("<th class = 'text-center' bgcolor='#868e96'>ลำดับที่</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>ประเภท OBJECT</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>ชื่อ OBJECT</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>แก้ไข</th>");
					echo("<th class = 'text-center' bgcolor='#868e96'>Add</th>");	
					echo("<th class = 'text-center' bgcolor='#868e96'>Update</th>");	
					echo("<th class = 'text-center' bgcolor='#868e96'>Delete</th>");	
					echo("<th class = 'text-center' bgcolor='#868e96'>View</th>");	
					echo("<th class = 'text-center' bgcolor='#868e96'>First Menu</th>");	
					echo("<th class = 'text-center' bgcolor='#868e96'>Menu Order</th>");	
					echo("<th class = 'text-center' bgcolor='#868e96'>Parent Menu</th>");	
					echo("<th class = 'text-center' bgcolor='#868e96'>ใช้งาน?</th>");	
					echo("</tr>");
					echo("</thead>");
					echo("<tbody>");
				}
				
				echo("<tr>");		
				echo("<td class = 'text-center'>".$i."</td>");	
				echo("<td class = 'text-left'>".$mresult['OBJECT_TYPE']."</td>");
				echo("<td class = 'text-left'>".$mresult['MENU_NAME']."</td>");
				echo("<td  class='text-center'>");
				echo("<div class='btn-group' role='group' aria-label='Basic example'>");
				if ($_SESSION[session_id()]['MENU'][$menuid]['UPDATE'] == 1) {
					echo("<button type='button' class='btn  btn-outline-success'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('5', '".$menuid."', 'C', '".$key."|".$mresult['MENU_ID']."');\">แก้ไข</button>");
				}
				if ($_SESSION[session_id()]['MENU'][$menuid]['DELETE'] == 1) {
					echo("<button type='button' class='btn  btn-outline-danger'data-toggle='modal' data-target='#myModal'  onclick = \"windowOpen('5', '".$menuid."',  'D', '".$key."|".$mresult['MENU_ID']."');\">ลบ</button>");
				}			
				echo("</div>");
				echo("</td>");
				if ($mresult['ADD'] == 1) {
					echo("<td class = 'text-center'><font color = '#00C851'><i class='fas fa-check'></i></font></td>");	
				}
				else {
					echo("<td class = 'text-center'><font color = '#CC0000'><i class='fas fa-times'></i></font></td>");	
				}
				if ($mresult['UPDATE'] == 1) {
					echo("<td class = 'text-center'><font color = '#00C851'><i class='fas fa-check'></i></font></td>");	
				}
				else {
					echo("<td class = 'text-center'><font color = '#CC0000'><i class='fas fa-times'></i></font></td>");	
				}
				if ($mresult['DELETE'] == 1) {
					echo("<td class = 'text-center'><font color = '#00C851'><i class='fas fa-check'></i></font></td>");	
				}
				else {
					echo("<td class = 'text-center'><font color = '#CC0000'><i class='fas fa-times'></i></font></td>");	
				}
				if ($mresult['VIEW'] == 1) {
					echo("<td class = 'text-center'><font color = '#00C851'><i class='fas fa-check'></i></font></td>");	
				}
				else {
					echo("<td class = 'text-center'><font color = '#CC0000'><i class='fas fa-times'></i></font></td>");	
				}	
				if ($mresult['FIRST_MENU'] == 1) {
					echo("<td class = 'text-center'><font color = '#00C851'><i class='fas fa-check'></i></font></td>");	
				}
				else {
					echo("<td class = 'text-center'><font color = '#CC0000'><i class='fas fa-times'></i></font></td>");	
				}	
				echo("<td class = 'text-center'>".$mresult['MENU_ORDER']."</td>");
				echo("<td class = 'text-center'>".$mresult['PARENT_MENU']."</td>");
				if ($mresult['VALID_IND_CODE'] == 1) {
					echo("<td class = 'text-center'><font color = '#00C851'><i class='fas fa-check'></i></font></td>");	
				}
				else {
					echo("<td class = 'text-center'><font color = '#CC0000'><i class='fas fa-times'></i></font></td>");	
				}
				echo("</tr>");
				
				if ($recordcount == 5 && $n != $total) {
					$recordcount = 0;
					echo("</tbody>");
					echo("</table>");
//					echo("</div>");
					echo("</div>");
				}			
			};
			echo("</tbody>");
			echo("</table>");
			echo("</div>");
			echo("</div>");
			
			if ($sumpage > 1) {
				echo("<a class='carousel-control-prev' href='#carouselExampleControls' role='button' data-slide='prev'>");
				echo("<span class='carousel-control-prev-icon' aria-hidden='true'></span>");
				echo("<span class='sr-only'>Previous</span>");
				echo("</a>");
				echo("<a class='carousel-control-next' href='#carouselExampleControls' role='button' data-slide='next'>");
				echo("<span class='carousel-control-next-icon' aria-hidden='true'></span>");
				echo("<span class='sr-only'>Next</span>");
				echo("</a>");			
			}
		}
		else {
//			echo("<div class='container'>");
			echo("<table class = 'table table-bordered dt-responsive' cellspacing='0' cellspadding = '0' width='100%' >");
			echo("<thead>");
			echo("<tr>");
			echo("<th class = 'text-center' bgcolor='#868e96'>ลำดับที่</th>");
			echo("<th class = 'text-center' bgcolor='#868e96'>ประเภท OBJECT</th>");
			echo("<th class = 'text-center' bgcolor='#868e96'>ชื่อ OBJECT</th>");
			echo("<th class = 'text-center' bgcolor='#868e96'>Add</th>");	
			echo("<th class = 'text-center' bgcolor='#868e96'>Update</th>");	
			echo("<th class = 'text-center' bgcolor='#868e96'>Delete</th>");	
			echo("<th class = 'text-center' bgcolor='#868e96'>View</th>");	
			echo("<th class = 'text-center' bgcolor='#868e96'>First Menu</th>");	
			echo("<th class = 'text-center' bgcolor='#868e96'>Menu Level</th>");	
			echo("<th class = 'text-center' bgcolor='#868e96'>Menu Order</th>");	
			echo("<th class = 'text-center' bgcolor='#868e96'>Parent Menu</th>");	
			echo("<th class = 'text-center' bgcolor='#868e96'>ใช้งาน?</th>");	
			echo("</tr>");
			echo("</thead>");
			echo("<tbody>");
			
			echo("<tr>");		
			echo("<td class = 'text-center' colspan='12'>ไม่มีข้อมูลที่ต้องการค้นหา</td>");	
			echo("</tr>");
			echo("</tbody>");
			echo("</table>");
//			echo("</div>");
		}
		
		echo("</div>");
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