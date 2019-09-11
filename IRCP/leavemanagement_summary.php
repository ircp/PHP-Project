<?
	require ('connection.php');
	
	try {
		
		include ("window_parameter.php");

		$sql = "	SELECT USER.EMPLOYEE_ID, EMPLOYEE.START_DATE, YEAR(NOW()) - YEAR(EMPLOYEE.START_DATE) - (DATE_FORMAT(NOW(), '%m%d') < DATE_FORMAT(EMPLOYEE.START_DATE, '%m%d')) AS DIFF_YEAR,
					DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m'), PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m')) -12 as MONTHDIFF, EMPLOYEE.CUMULATIVE_VACATION
					FROM USER USER
					LEFT JOIN EMPLOYEE EMPLOYEE ON USER.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID AND SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
					WHERE USER.USER_ID = ?";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($userid));	
		
		$employeeid = 0;
		$diff_year = 0;
		$month_diff = 0;
		$cumulative_vacation = 0;
		
		if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$employeeid = $arr['EMPLOYEE_ID'];
			$diff_year = $arr['DIFF_YEAR'];
			$month_diff = $arr['MONTHDIFF'];
			$cumulative_vacation = $arr['CUMULATIVE_VACATION'];
		}
		
		UNSET ($LEAVE_SUMMARY);
		
		$sql = "	SELECT	LEAVE_TYPE_ID, LEAVE_NAME, ALLOWANCE
					FROM LEAVE_POLICY
					WHERE VALID_IND_CODE = 1
					ORDER BY ORDERING";

		$stmt = $conn->prepare($sql);
		$stmt->execute();	
		
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$LEAVE_SUMMARY[$arr['LEAVE_TYPE_ID']]['LEAVE_NAME'] = $arr['LEAVE_NAME'];
			
			if ($arr['LEAVE_TYPE_ID'] == 5) {
				if ($diff_year == 0) {
					$allowance = 0 + $cumulative_vacation;
				}
				else {
					if ($diff_year == 1) {
						$allowance = $arr['ALLOWANCE'] + $cumulative_vacation;
					}
					else {
						$allowance = $arr['ALLOWANCE'] + $cumulative_vacation;
					}
				}
				
				if ($allowance >= 12) {
					$allowance = 12;
				}
			}
			else {
				$allowance = $arr['ALLOWANCE'];
			}
			
			$LEAVE_SUMMARY[$arr['LEAVE_TYPE_ID']]['ALLOWANCE'] = $allowance;
		}					
		
		$currentyear = date("Y");
		
		$sql = "	SELECT YEAR(ELD.LEAVE_DATE) LEAVE_YEAR, SUM(ELD.SUMMARY_LEAVE) SUM_LEAVE, ELH.LEAVE_TYPE_ID, LP.LEAVE_NAME
					FROM EMPLOYEE_LEAVE_DETAIL ELD
					LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON ELH.LEAVE_ID = ELD.LEAVE_ID AND SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
					LEFT JOIN LEAVE_POLICY LP ON LP.LEAVE_TYPE_ID = ELH.LEAVE_TYPE_ID 
					WHERE ELH.EMPLOYEE_ID = ? AND YEAR(ELD.LEAVE_DATE) = ?
					GROUP BY YEAR(ELD.LEAVE_DATE), ELH.LEAVE_TYPE_ID, LP.LEAVE_NAME
					ORDER BY YEAR(ELD.LEAVE_DATE), LP.ORDERING";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($employeeid, $currentyear));	
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$LEAVE_SUMMARY[$arr['LEAVE_TYPE_ID']]['USED'] = $arr['SUM_LEAVE'];
		}
		
		echo("<table class = 'table display' cellspacing='0' cellspadding = '0' width='100%' >");
		foreach ($LEAVE_SUMMARY as $leavetypeid => $mresult) {
			echo("<tr>");
			echo("<td>".$mresult['LEAVE_NAME']."</td>");
			if (isset($mresult['USED'])) {
				$used = $mresult['USED'];				
			}
			else {
				$used = 0;
			}
			$remain = $mresult['ALLOWANCE'] - $used;
			echo("<td class = 'text-right'>".$used." วัน</td>");
			echo("<td>คงเหลือ</td>");
			echo("<td class = 'text-right'>".$remain." วัน</td>");
			echo("</tr>");
		}
		echo("</table>");		
		
		$conn->commit();
	}	
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	
	$conn = null;	
?>