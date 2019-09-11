<?
	require ('connection.php');
	
	try {
		$currentyear = date("Y");
		
		$sql = "	SELECT WEEKEND_ID, WEEKEND_NAME, WEEKEND_DATE
					FROM WEEKEND
					WHERE YEAR(WEEKEND_DATE) = ?
					ORDER BY WEEKEND_DATE";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($currentyear));	
		
		echo("<table class = 'table display' cellspacing='0' cellspadding = '0' width='100%' >");
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			echo("<tr>");
			echo("<td>".$arr['WEEKEND_NAME']."</td>");
			$weekend_date = substr($arr['WEEKEND_DATE'],8,2)."/".substr($arr['WEEKEND_DATE'],5,2)."/".((int)substr($arr['WEEKEND_DATE'],0,4) + 543);
			echo("<td class = 'text-right'>".$weekend_date."</td>");
			echo("</tr>");			
		}
		echo("</table>");	
	}	
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	
	$conn = null;	
?>