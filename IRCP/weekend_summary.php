<?
	require ('connection.php');
	
	try {
		
		if (isset($_POST['menuid'])) {
			$menuid = $_POST['menuid'];
		}
		
		$currentyear = date("Y") + 1;
		$previousyear = date("Y") - 1;
		
		$sql = "	SELECT WEEKEND_ID, WEEKEND_NAME, WEEKEND_DATE
					FROM IRCP.WEEKEND
					WHERE YEAR(WEEKEND_DATE) >= ? AND YEAR(WEEKEND_DATE) <= ?
					ORDER BY WEEKEND_DATE";
		
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($previousyear, $currentyear));	
		
		$n = 0;
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$n = $n + 1;
			
			$event_array[] = array(
				'id' => $n,
				'title' => $arr['WEEKEND_NAME'],
				'start' => $arr['WEEKEND_DATE'],
				'end' => $arr['WEEKEND_DATE'],
				'allDay' => true,
				'overlap'  =>false,
				'color'  => '#31b0d5'

			);			
		}
		
		echo json_encode($event_array);
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;
?>