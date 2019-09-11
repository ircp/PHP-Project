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
		
		$currentdate = date("Y-m-");
		$currentmonth = date("m");
		$currentyear = date("Y");
		
		$d=strtotime("+1 Months");
		$startdate = date("Y-m-d", $d);	
		
		$enddate = date("Y-m-t", strtotime($startdate));
		
		$cdate = substr($startdate, 0, 8);
		$sdate = (int) substr($startdate, 8, 2);
		$edate = (int) substr($enddate, 8, 2);
		
		for ($x = 1; $x <= $edate; $x++) {
			$nday = (string) $x;
			$len = strlen($nday);
			
			if ($len  == 1) {
				$nday = "0".$nday;
			}
			$ndate = $cdate.$nday." 00:00:00";
			
			require ('connection.php');
			
			$sql = "	SELECT COUNT(*) COUNTN FROM WEEKEND
						WHERE WEEKEND_DATE = ?";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($ndate));
			
			$countn = 0;
			
			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$countn = $arr['COUNTN'];
			}
			
			$chdate = $cdate.$nday;
			
			$newdate = date_create($chdate);
			$nformat = date_format($newdate, "l");
			
			if ($nformat == 'Saturday' || $nformat == 'Sunday') {
				$countn = 1;
			}
			
			if ($countn == 0) {
				foreach ($EMPLOYEE_RESULT as $employeeid => $result) {
					require ('connection.php');
					
					$sql = "	SELECT COUNT(*) COUNTN
								FROM WORKING_DAY
								WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
								AND EMPLOYEE_ID = ? AND WORKING_DAY = ?";

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($employeeid, $ndate));		

					$countr = 0;
					
					while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$countr = $arr['COUNTN'];
					}
								
					if ($countr == 0) {
						$sql = "	INSERT INTO WORKING_DAY (EMPLOYEE_ID, WORKING_DAY, EFFECTIVE_START_DATE, EFFECTIVE_END_DATE, CREATED_DATE, LAST_MODIFIED, USER_ID, WORKING_STATUS)
									VALUES (?, ?, NOW(), '9999-12-31 23:59:59', NOW(), NOW(), 1, 1)";

						$stmt = $conn->prepare($sql);
						$stmt->execute(array($employeeid, $ndate));								
						
						$conn->commit();
					}
				}
			}
		}
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}

?>