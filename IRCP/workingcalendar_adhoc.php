<?
	try {
		require ('connection.php');
		
		$sql = "	SELECT EMPLOYEE_ID FROM EMPLOYEE
					WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
					AND EMPLOYEE_STATUS = 1 AND EMPLOYEE_ID <> 1
					ORDER BY EMPLOYEE_ID";

		$stmt = $conn->prepare($sql);
		$stmt->execute();		
		
		UNSET ($EMPLOYEE_RESULT);
		
		$i = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$EMPLOYEE_RESULT[$arr['EMPLOYEE_ID']]['EMPLOYEE_ID'] = $arr['EMPLOYEE_ID'];
		}
		
		$currentyear = date('Y');
//		$currentmonth = (int)date('m') + 1;
		$currentmonth = 3;
		$currentmonth = str_pad($currentmonth, 2, '0', STR_PAD_LEFT);
		$currentdate = $currentyear."-".$currentmonth."-01";
		$enddate = date("Y-m-t", strtotime($currentdate));
		$edate = (int) substr($enddate, 8, 2);
		
		for ($x = 1; $x <= $edate; $x++) {
			$cday = str_pad($x, 2, '0', STR_PAD_LEFT);
			$cdate = $currentyear."-".$currentmonth."-".$cday." 00:00:00";
			
			$weeken_id = '';
			
			$sql = "	SELECT WEEKEND_ID 
						FROM WEEKEND 
						WHERE WEEKEND_DATE = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($cdate));

			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$weeken_id = $arr['WEEKEND_ID'];
			}
			$weekend = date("l", strtotime($currentyear."-".$currentmonth."-".$cday));
			
			if ($weekend != 'Sunday' && $weekend != 'Saturday' && $weeken_id == '' && isset($EMPLOYEE_RESULT)) {
				foreach ($EMPLOYEE_RESULT as $employeeid => $em_result) {
					$sql = "	SELECT EMPLOYEE_ID, WORKING_DAY
								FROM WORKING_DAY
								WHERE EMPLOYEE_ID = ? AND WORKING_DAY = ? AND SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE";
					
					$stmt = $conn->prepare($sql);
					$stmt->execute(array($employeeid, $cdate));	
					
					$found = 0;
					if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$found = 1;
					}					
					
					if ($found === 0) {
						$sql = "	INSERT INTO WORKING_DAY (EMPLOYEE_ID, WORKING_DAY, EFFECTIVE_START_DATE, EFFECTIVE_END_DATE, CREATED_DATE, LAST_MODIFIED, USER_ID, WORKING_STATUS)
									VALUES (?, ?, NOW(), '9999-12-31 23:59:59', NOW(), NOW(), 1, 1)";

						$stmt = $conn->prepare($sql);
						$stmt->execute(array($employeeid, $cdate));	
					}
				}
			}
		}
		
		$conn->commit();
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}

?>