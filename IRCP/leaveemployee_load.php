<?
	try {
		require ('connection.php');
			
		$sql = "	SELECT ELD.LEAVE_DATE, ELD.LEAVE_ID, WD.EMPLOYEE_ID
					FROM EMPLOYEE_LEAVE_DETAIL ELD
					LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
					AND ELH.LEAVE_ID = ELD.LEAVE_ID AND ELH.LEAVE_STATUS IN (2,3)
					LEFT JOIN WORKING_DAY WD ON SYSDATE() BETWEEN WD.EFFECTIVE_START_DATE AND WD.EFFECTIVE_END_DATE
					AND WD.EMPLOYEE_ID = ELH.EMPLOYEE_ID AND WD.WORKING_DAY = ELD.LEAVE_DATE
					WHERE WORKING_STATUS = 1";

		$stmt = $conn->prepare($sql);
		$stmt->execute();		
		
		UNSET ($LEAVE_EMPLOYEE_LOAD);
		
		$i = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i + 1;
			$LEAVE_EMPLOYEE_LOAD[$i]['EMPLOYEE_ID'] = $arr['EMPLOYEE_ID'];
			$LEAVE_EMPLOYEE_LOAD[$i]['LEAVE_ID'] = $arr['LEAVE_ID'];
			$LEAVE_EMPLOYEE_LOAD[$i]['LEAVE_DATE'] = $arr['LEAVE_DATE'];
		}
		
		require ('connection.php');
		
		foreach ($LEAVE_EMPLOYEE_LOAD as $mid => $result) {
			$employeeid = $result['EMPLOYEE_ID'];
			$leavedate = $result['LEAVE_DATE'];
			$leaveid = $result['LEAVE_ID'];
			
			$sql = "	UPDATE WORKING_DAY
						SET WORKING_STATUS = 3, LEAVE_ID = ?
						WHERE EMPLOYEE_ID = ? AND WORKING_DAY = ? AND SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leaveid, $employeeid, $leavedate));			
		}
		
		$conn->commit();
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;
?>