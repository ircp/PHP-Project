<?
	require ('connection.php');
	
	try {

		if (isset($_POST['menuid'])) {
			$menuid = $_POST['menuid'];
		}
		
		$currentyear = date("Y") + 1;
		$previousyear = date("Y") - 1;	
		
		$n = 0;
		if (session_id() == '') {
			session_start();
		}
		
		if (!empty($_SESSION[session_id()]['USER'])) {
			$userid = $_SESSION[session_id()]['USER'];
		}
		else {
			$userid = '';
		}
		
		if ($menuid == 10) {
			$sql = "	SELECT ELD.LEAVE_ID, ELD.LEAVE_DATE, ELD.SUMMARY_LEAVE SUM_LEAVE, ELH.LEAVE_TYPE_ID, ELH.LEAVE_STATUS, ELH.LEAVE_START_DATE, ELH.LEAVE_END_DATE,
						ELH.SUMMARY_LEAVE, RC1.LABEL LEAVESTATUS, LP.LEAVE_NAME
						FROM EMPLOYEE_LEAVE_DETAIL ELD
						LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE AND ELH.LEAVE_ID = ELD.LEAVE_ID
						LEFT JOIN USER USER ON USER.EMPLOYEE_ID = ELH.EMPLOYEE_ID
						LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 8 AND RC1.REFERENCE_CODE = ELH.LEAVE_STATUS
						LEFT JOIN LEAVE_POLICY LP ON LP.LEAVE_TYPE_ID = ELH.LEAVE_TYPE_ID
						WHERE USER.USER_ID = ? AND YEAR(LEAVE_DATE) >= ? AND YEAR(LEAVE_DATE) <= ? AND  ELH.LEAVE_STATUS IN (2,3)";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($userid, $previousyear, $currentyear));
			
			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$n = $n + 1;
				
				if ($arr['SUM_LEAVE'] == 1) {
					$event_array[] = array(
						'id' => $n,
						'title' => $arr['LEAVE_NAME']."-".$arr['LEAVESTATUS'],
						'start' => $arr['LEAVE_DATE'],
						'end' => $arr['LEAVE_DATE'],
						'allDay' => true,
						'overlap'  =>false,
						'color'  => '#28a745'
					);	
				}
				else {
					$event_array[] = array(
						'id' => $n,
						'title' => $arr['LEAVE_NAME']."-".$arr['LEAVESTATUS'],
						'start' => $arr['LEAVE_DATE'],
						'end' => $arr['LEAVE_DATE'],
						'allDay' => false,
						'overlap'  =>false,
						'color'  => '#28a745'
					);	
					
				}
			}
			
			if ($n == 0) {
				$event_array[] = array();				
			}
		}
		else {
			$event_array[] = array();	
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