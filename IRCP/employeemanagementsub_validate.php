<?
	require ('connection.php');
	
	try {
		
		if ($key == 1) {
			$explode = explode("|", $parameter);
			$leavedate = $explode[0];
			$employeeid = $explode[1];
			
			$sql = "	SELECT WEEKEND_ID FROM WEEKEND
						WHERE WEEKEND_DATE = ?";


			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leavedate));
			
			$weekendid = 0;
			
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$weekendid = $arr['WEEKEND_ID'];
			}
			
			if ($weekendid != 0) {
				$error_code = 1015;
				include ('window_errormessage.php');	
				
				exit();
			}
		}
		else if ($key == 3) {
			$explode = explode("|", $parameter);
			$leavedate = $explode[0];
			$employeeid = $explode[1];
			$leaveid = $explode[2];
			
			$sql = "	SELECT COUNT(*) COUNTN
						FROM EMPLOYEE_LEAVE_DETAIL ELD
						LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE AND ELD.LEAVE_ID = ELH.LEAVE_ID
						LEFT JOIN USER USER ON USER.EMPLOYEE_ID = ELH.EMPLOYEE_ID
						WHERE ELD.LEAVE_DATE = ? AND ELH.EMPLOYEE_ID = ? AND ELH.LEAVE_ID <> ?";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leavedate, $employeeid, $leaveid));
			
			$count = 0;
			
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$count = $arr['COUNTN'];
			}
			
			if ($count != 0) {
				$error_code = 1015;
				include ('window_errormessage.php');	
				
				exit();
			}
		}
		else if ($key == 2) {
			$explode = explode("|", $parameter);
			$startdate = $explode[0];
			$endate = $explode[1];
			$summaryleave = $explode[2];
			$leavetypeid = $explode[3];
			
			$currentyear = date("Y");
			
			$syear = substr($startdate,0, 4);
			$eyear = substr($endate, 0, 4);
			
			$countn = count($explode) - 1;

			$sql = "	SELECT ALLOWANCE
						FROM LEAVE_POLICY
						WHERE LEAVE_TYPE_ID = ?";
			
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leavetypeid));
			
			$leave_allowance = 0;
			
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$leave_allowance = $arr['ALLOWANCE'];
			}			
			
			if ($syear == $eyear) {				
				if ($syear == $currentyear) {
					$sql = "	SELECT USER.EMPLOYEE_ID, EMPLOYEE.START_DATE, YEAR(?) - YEAR(EMPLOYEE.START_DATE) - (DATE_FORMAT(?, '%m%d') < DATE_FORMAT(EMPLOYEE.START_DATE, '%m%d')) AS DIFF_YEAR,
								DATE_FORMAT(?, '%Y%m'), DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m'), PERIOD_DIFF(DATE_FORMAT(?, '%Y%m'),DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m')) -12 as MONTHDIFF, EMPLOYEE.CUMULATIVE_VACATION
								FROM USER USER
								LEFT JOIN EMPLOYEE EMPLOYEE ON USER.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID AND SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
								WHERE EMPLOYEE.EMPLOYEE_ID = ?";

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($explode[$countn], $explode[$countn] , $explode[$countn] ,$explode[$countn] , $employeeid));	
					
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
					
					if ($leavetypeid == 5) {
						if ($diff_year == 0) {
							$allowance = 0 + $cumulative_vacation;
						}
						else {
							if ($diff_year == 1) {
								$allowance = $leave_allowance + $cumulative_vacation;
							}
							else {
								$allowance = $leave_allowance + $cumulative_vacation;
							}
						}
						
						if ($allowance >= 12) {
							$allowance = 12;
						}						
					}
					else {
						$allowance = $leave_allowance;
					}
					
					$sql = "	SELECT SUM(ELD.SUMMARY_LEAVE) SUM_LEAVE
								FROM EMPLOYEE_LEAVE_DETAIL ELD
								LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE AND ELH.LEAVE_ID = ELD.LEAVE_ID
								WHERE ELH.EMPLOYEE_ID = ? AND ELH.LEAVE_TYPE_ID = ? AND YEAR(ELD.LEAVE_DATE) = ?";
					$used = 0;

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($employeeid, $leavetypeid , $syear));
					
					if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$used = $arr['SUM_LEAVE'];
					}
					
					$remain = $allowance - $used;
					
					if ($remain < $summaryleave) {
						$error_code = 1016;
						include ('window_errormessage.php');
						
						exit();						
					}
				}
				else {
					$end = $currentyear."-12-31";

					$sql = "	SELECT USER.EMPLOYEE_ID, EMPLOYEE.START_DATE, YEAR(?) - YEAR(EMPLOYEE.START_DATE) - (DATE_FORMAT(?, '%m%d') < DATE_FORMAT(EMPLOYEE.START_DATE, '%m%d')) AS DIFF_YEAR,
								DATE_FORMAT(?, '%Y%m'), DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m'), PERIOD_DIFF(DATE_FORMAT(?, '%Y%m'),DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m')) -12 as MONTHDIFF, EMPLOYEE.CUMULATIVE_VACATION
								FROM USER USER
								LEFT JOIN EMPLOYEE EMPLOYEE ON USER.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID AND SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
								WHERE EMPLOYEE.EMPLOYEE_ID = ?";

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($end, $end , $end ,$end , $employeeid));	
					
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
					
					if ($leavetypeid == 5) {
						if ($diff_year == 0) {
							$allowance = 0 + $cumulative_vacation;
						}
						else {
							if ($diff_year == 1 ) {
								$allowance = $leave_allowance + $cumulative_vacation;
							}
							else {
								$allowance = $leave_allowance + $cumulative_vacation;
							}
						}
						
						if ($allowance >= 12) {
							$allowance = 12;
						}

						$sql = "	SELECT SUM(ELD.SUMMARY_LEAVE) SUM_LEAVE
									FROM EMPLOYEE_LEAVE_DETAIL ELD
									LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE AND ELH.LEAVE_ID = ELD.LEAVE_ID
									WHERE ELH.EMPLOYEE_ID = ? AND ELH.LEAVE_TYPE_ID = ? AND YEAR(ELD.LEAVE_DATE) = ?";
						$used = 0;

						$stmt = $conn->prepare($sql);
						$stmt->execute(array($employeeid, $leavetypeid , $currentyear));
						
						if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
							$used = $arr['SUM_LEAVE'];
						}

						$lastyear = $allowance - $used;
						
						$sql = "	SELECT USER.EMPLOYEE_ID, EMPLOYEE.START_DATE, YEAR(?) - YEAR(EMPLOYEE.START_DATE) - (DATE_FORMAT(?, '%m%d') < DATE_FORMAT(EMPLOYEE.START_DATE, '%m%d')) AS DIFF_YEAR,
									DATE_FORMAT(?, '%Y%m'), DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m'), PERIOD_DIFF(DATE_FORMAT(?, '%Y%m'),DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m')) -12 as MONTHDIFF
									FROM USER USER
									LEFT JOIN EMPLOYEE EMPLOYEE ON USER.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID AND SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
									WHERE EMPLOYEE.EMPLOYEE_ID = ?";

						$stmt = $conn->prepare($sql);
						$stmt->execute(array($explode[$countn], $explode[$countn] , $explode[$countn] ,$explode[$countn] , $employeeid));	
						
						$employeeid = 0;
						$diff_year = 0;
						$month_diff = 0;
						
						if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
							$employeeid = $arr['EMPLOYEE_ID'];
							$diff_year = $arr['DIFF_YEAR'];
							$month_diff = $arr['MONTHDIFF'];
						}
						
						$allowance = 0;
						if ($diff_year == 0) {
							$allowance = 0 ;
						}
						else {
							$allowance = $leave_allowance;
						}
						
						$allowance = $allowance + $lastyear;
						
						if ($allowance >= 12) {
							$allowance = 12;
						}						
					}
					else {
						$allowance = $leave_allowance;
					}

					$sql = "	SELECT SUM(ELD.SUMMARY_LEAVE) SUM_LEAVE
								FROM EMPLOYEE_LEAVE_DETAIL ELD
								LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE AND ELH.LEAVE_ID = ELD.LEAVE_ID
								WHERE ELH.EMPLOYEE_ID = ? AND ELH.LEAVE_TYPE_ID = ? AND YEAR(ELD.LEAVE_DATE) = ?";
					$used = 0;

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($employeeid, $leavetypeid , $syear));
					
					if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$used = $arr['SUM_LEAVE'];
					}

					$remain = $allowance - $used;
					
					if ($remain < $summaryleave) {
						$error_code = 1016;
						include ('window_errormessage.php');
						
						exit();						
					}
				}
			}
			else {
				$scount = 0;
				$ecount = 0;
				
				for ($x = 4; $x <= $countn; $x++) {
					$nyear = substr($explode[$x], 0, 4);
					if ($nyear == $syear) {
						$send = $explode[$x];
						$scount = $scount + 1;
					}
					if ($nyear == $eyear) {
						$eend = $explode[$x];
						$ecount = $ecount + 1;
					}
				}
				
				$hour1 = (int) substr($startdate, 11, 2);
				$hour2 = (int) substr($endate, 11, 2);

				if ($hour1 > 8) {
					$sumleave = 17 - $hour1;
					
					if ($hour1 <= 12) {
						$sumleave = $sumleave - 1;
					}
					
					$scount = ($scount - 1) + ($sumleave/8);
				}
				if ($hour2 < 17) {
					$sumleave = 17 - $hour2;
					if ($hour2 >= 13) {
						$sumleave = $sumleave - 1;
					}
					$ecount = ($ecount - 1) + ($sumleave/8);
				}
/*				
				if ($hour1 >= 13) {
					$scount = $scount - 0.5;
				}
				if ($hour2 <= 12) {
					$ecount = $ecount - 0.5;
				}
*/				
				$sql = "	SELECT USER.EMPLOYEE_ID, EMPLOYEE.START_DATE, YEAR(?) - YEAR(EMPLOYEE.START_DATE) - (DATE_FORMAT(?, '%m%d') < DATE_FORMAT(EMPLOYEE.START_DATE, '%m%d')) AS DIFF_YEAR,
							DATE_FORMAT(?, '%Y%m'), DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m'), PERIOD_DIFF(DATE_FORMAT(?, '%Y%m'),DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m')) -12 as MONTHDIFF, EMPLOYEE.CUMULATIVE_VACATION
							FROM USER USER
							LEFT JOIN EMPLOYEE EMPLOYEE ON USER.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID AND SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
							WHERE EMPLOYEE.EMPLOYEE_ID = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($send, $send , $send ,$send , $employeeid));	
				
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

				if ($leavetypeid == 5) {
					if ($diff_year == 0) {
						$allowance = 0 + $cumulative_vacation;
					}
					else {
						if ($diff_year == 1) {
							$allowance = $leave_allowance + $cumulative_vacation;
						}
						else {
							$allowance = $leave_allowance + $cumulative_vacation;
						}
					}
					
					if ($allowance >= 12) {
						$allowance = 12;
					}						
				}
				else {
					$allowance = $leave_allowance;
				}
				
				$sql = "	SELECT SUM(ELD.SUMMARY_LEAVE) SUM_LEAVE
							FROM EMPLOYEE_LEAVE_DETAIL ELD
							LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE AND ELH.LEAVE_ID = ELD.LEAVE_ID
							WHERE ELH.EMPLOYEE_ID = ? AND ELH.LEAVE_TYPE_ID = ? AND YEAR(ELD.LEAVE_DATE) = ?";
				
				$used = 0;

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($employeeid, $leavetypeid , $syear));
				
				if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$used = $arr['SUM_LEAVE'];
				}
				
				$remain = $allowance - $used;
				
				if ($remain < $scount) {
					$error_code = 1016;
					include ('window_errormessage.php');
					
					exit();						
				}				
				
				if ($leavetypeid == 5) {
					$lastyear = $remain - $scount;
				}
				else {
					$lastyear = 0;
				}
				
				$sql = "	SELECT USER.EMPLOYEE_ID, EMPLOYEE.START_DATE, YEAR(?) - YEAR(EMPLOYEE.START_DATE) - (DATE_FORMAT(?, '%m%d') < DATE_FORMAT(EMPLOYEE.START_DATE, '%m%d')) AS DIFF_YEAR,
							DATE_FORMAT(?, '%Y%m'), DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m'), PERIOD_DIFF(DATE_FORMAT(?, '%Y%m'),DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m')) -12 as MONTHDIFF, EMPLOYEE.CUMULATIVE_VACATION
							FROM USER USER
							LEFT JOIN EMPLOYEE EMPLOYEE ON USER.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID AND SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
							WHERE EMPLOYEE.EMPLOYEE_ID = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($eend, $eend , $eend ,$eend , $employeeid));	
				
				$employeeid = 0;
				$diff_year = 0;
				$month_diff = 0;
				
				if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$employeeid = $arr['EMPLOYEE_ID'];
					$diff_year = $arr['DIFF_YEAR'];
					$month_diff = $arr['MONTHDIFF'];
				}

				if ($leavetypeid == 5) {
					if ($diff_year == 0) {
						$allowance = 0 ;
					}
					else {
						$allowance = $leave_allowance + $lastyear;
					}
					
					if ($allowance >= 12) {
						$allowance = 12;
					}						
				}
				else {
					$allowance = $leave_allowance;
				}
				
				
				$sql = "	SELECT SUM(ELD.SUMMARY_LEAVE) SUM_LEAVE
							FROM EMPLOYEE_LEAVE_DETAIL ELD
							LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE AND ELH.LEAVE_ID = ELD.LEAVE_ID
							WHERE ELH.EMPLOYEE_ID = ? AND ELH.LEAVE_TYPE_ID = ? AND YEAR(ELD.LEAVE_DATE) = ?";
				$used = 0;

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($employeeid, $leavetypeid , $eyear));
				
				if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$used = $arr['SUM_LEAVE'];
				}
				
				$remain = $allowance - $used;
				
				if ($remain < $ecount) {
					$error_code = 1016;
					include ('window_errormessage.php');
					
					exit();						
				}					
			}
		}
		else if ($key == 4) {
			$explode = explode("|", $parameter);
			$employeeid = $explode[0];
			$leavetypeid = $explode[1];
			$startdateu = $explode[2];
			$enddateu = $explode[3];
			$summaryleave = $explode[4];
			$description = $explode[5];
			$leavestatus = $explode[6];
			$startdate = $explode[7];
			$enddate = $explode[8];
			
			
			$description_len = strlen($description);
			
			if ($description_len >= 200) {
				$error_code = 1017;
				include ('window_errormessage.php');
				
				exit();				
			}
			
			$currentyear = date("Y");
			
			$syear = substr($startdate,0, 4);
			$eyear = substr($enddate, 0, 4);
			
			$countn = count($explode) - 1;

			$sql = "	SELECT ALLOWANCE
						FROM LEAVE_POLICY
						WHERE LEAVE_TYPE_ID = ?";
			
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($leavetypeid));
			
			$leave_allowance = 0;
			
			if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$leave_allowance = $arr['ALLOWANCE'];
			}			
			
			if ($syear == $eyear) {				
				if ($syear == $currentyear) {
					$sql = "	SELECT USER.EMPLOYEE_ID, EMPLOYEE.START_DATE, YEAR(?) - YEAR(EMPLOYEE.START_DATE) - (DATE_FORMAT(?, '%m%d') < DATE_FORMAT(EMPLOYEE.START_DATE, '%m%d')) AS DIFF_YEAR,
								DATE_FORMAT(?, '%Y%m'), DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m'), PERIOD_DIFF(DATE_FORMAT(?, '%Y%m'),DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m')) -12 as MONTHDIFF, EMPLOYEE.CUMULATIVE_VACATION
								FROM USER USER
								LEFT JOIN EMPLOYEE EMPLOYEE ON USER.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID AND SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
								WHERE EMPLOYEE.EMPLOYEE_ID = ?";

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($explode[$countn-1], $explode[$countn-1] , $explode[$countn-1] ,$explode[$countn-1] , $employeeid));	
					
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
										
					if ($leavetypeid == 5) {
						if ($diff_year == 0) {
							$allowance = 0 + $cumulative_vacation;
						}
						else {
							if ($diff_year == 1 ) {
								$allowance = $leave_allowance + $cumulative_vacation;
							}
							else {
								$allowance = $leave_allowance + $cumulative_vacation;
							}
						}
						
						if ($allowance >= 12) {
							$allowance = 12;
						}						
					}
					else {
						$allowance = $leave_allowance;
					}
					
					$sql = "	SELECT SUM(ELD.SUMMARY_LEAVE) SUM_LEAVE
								FROM EMPLOYEE_LEAVE_DETAIL ELD
								LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE AND ELH.LEAVE_ID = ELD.LEAVE_ID
								WHERE ELH.EMPLOYEE_ID = ? AND ELH.LEAVE_TYPE_ID = ? AND YEAR(ELD.LEAVE_DATE) = ?";
					$used = 0;

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($employeeid, $leavetypeid , $syear));
					
					if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$used = $arr['SUM_LEAVE'];
					}
					
					$remain = $allowance - $used;
					
					if ($remain < $summaryleave) {
						$error_code = 1016;
						include ('window_errormessage.php');
						
						exit();						
					}
				}
				else {
					$end = $currentyear."-12-31";

					$sql = "	SELECT USER.EMPLOYEE_ID, EMPLOYEE.START_DATE, YEAR(?) - YEAR(EMPLOYEE.START_DATE) - (DATE_FORMAT(?, '%m%d') < DATE_FORMAT(EMPLOYEE.START_DATE, '%m%d')) AS DIFF_YEAR,
								DATE_FORMAT(?, '%Y%m'), DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m'), PERIOD_DIFF(DATE_FORMAT(?, '%Y%m'),DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m')) -12 as MONTHDIFF, EMPLOYEE.CUMULATIVE_VACATION
								FROM USER USER
								LEFT JOIN EMPLOYEE EMPLOYEE ON USER.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID AND SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
								WHERE EMPLOYEE.EMPLOYEE_ID = ?";

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($end, $end , $end ,$end , $employeeid));	
					
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
					
					if ($leavetypeid == 5) {
						if ($diff_year == 0) {
							$allowance = 0 + $cumulative_vacation;
						}
						else {
							if ($diff_year == 1 ) {
								$allowance = $leave_allowance + $cumulative_vacation;
							}
							else {
								$allowance = $leave_allowance + $cumulative_vacation;
							}
						}
						
						if ($allowance >= 12) {
							$allowance = 12;
						}

						$sql = "	SELECT SUM(ELD.SUMMARY_LEAVE) SUM_LEAVE
									FROM EMPLOYEE_LEAVE_DETAIL ELD
									LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE AND ELH.LEAVE_ID = ELD.LEAVE_ID
									WHERE ELH.EMPLOYEE_ID = ? AND ELH.LEAVE_TYPE_ID = ? AND YEAR(ELD.LEAVE_DATE) = ?";
						$used = 0;

						$stmt = $conn->prepare($sql);
						$stmt->execute(array($employeeid, $leavetypeid , $currentyear));
						
						if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
							$used = $arr['SUM_LEAVE'];
						}

						$lastyear = $allowance - $used;
						
						$sql = "	SELECT USER.EMPLOYEE_ID, EMPLOYEE.START_DATE, YEAR(?) - YEAR(EMPLOYEE.START_DATE) - (DATE_FORMAT(?, '%m%d') < DATE_FORMAT(EMPLOYEE.START_DATE, '%m%d')) AS DIFF_YEAR,
									DATE_FORMAT(?, '%Y%m'), DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m'), PERIOD_DIFF(DATE_FORMAT(?, '%Y%m'),DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m')) -12 as MONTHDIFF
									FROM USER USER
									LEFT JOIN EMPLOYEE EMPLOYEE ON USER.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID AND SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
									WHERE EMPLOYEE.EMPLOYEE_ID = ?";

						$stmt = $conn->prepare($sql);
						$stmt->execute(array($explode[$countn-1], $explode[$countn-1] , $explode[$countn-1] ,$explode[$countn-1] , $employeeid));	
						
						$employeeid = 0;
						$diff_year = 0;
						$month_diff = 0;
						
						if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
							$employeeid = $arr['EMPLOYEE_ID'];
							$diff_year = $arr['DIFF_YEAR'];
							$month_diff = $arr['MONTHDIFF'];
						}
						
						$allowance = 0;
						if ($diff_year == 0) {
							$allowance = 0 ;
						}
						else {
							$allowance = $leave_allowance;
						}
						
						$allowance = $allowance + $lastyear;
						
						if ($allowance >= 12) {
							$allowance = 12;
						}						
					}
					else {
						$allowance = $leave_allowance;
					}

					$sql = "	SELECT SUM(ELD.SUMMARY_LEAVE) SUM_LEAVE
								FROM EMPLOYEE_LEAVE_DETAIL ELD
								LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE AND ELH.LEAVE_ID = ELD.LEAVE_ID
								WHERE ELH.EMPLOYEE_ID = ? AND ELH.LEAVE_TYPE_ID = ? AND YEAR(ELD.LEAVE_DATE) = ?";
					$used = 0;

					$stmt = $conn->prepare($sql);
					$stmt->execute(array($employeeid, $leavetypeid , $syear));
					
					if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$used = $arr['SUM_LEAVE'];
					}
					$remain = $allowance - $used;
					
					if ($remain < $summaryleave) {
						$error_code = 1016;
						include ('window_errormessage.php');
						
						exit();						
					}
				}
			}
			else {
				$scount = 0;
				$ecount = 0;
				
				for ($x = 9; $x <= $countn; $x++) {
					$nyear = substr($explode[$x], 0, 4);
					if ($nyear == $syear) {
						$send = $explode[$x];
						$scount = $scount + 1;
					}
					if ($nyear == $eyear) {
						$eend = $explode[$x];
						$ecount = $ecount + 1;
					}
				}
				
				$hour1 = (int) substr($startdate, 11, 2);
				$hour2 = (int) substr($enddate, 11, 2);
				
				if ($hour1 > 8) {
					$sumleave = 17 - $hour1;
					
					if ($hour1 <= 12) {
						$sumleave = $sumleave - 1;
					}
					
					$scount = ($scount - 1) + ($sumleave/8);
				}
				if ($hour2 < 17) {
					$sumleave = 17 - $hour2;
					if ($hour2 > 13) {
						$sumleave = $sumleave - 1;
					}
					$ecount = ($ecount - 1) + ($sumleave/8);
				}
/*				
				if ($hour1 >= 13) {
					$scount = $scount - 0.5;
				}
				if ($hour2 <= 12) {
					$ecount = $ecount - 0.5;
				}
*/	
				
				$sql = "	SELECT USER.EMPLOYEE_ID, EMPLOYEE.START_DATE, YEAR(?) - YEAR(EMPLOYEE.START_DATE) - (DATE_FORMAT(?, '%m%d') < DATE_FORMAT(EMPLOYEE.START_DATE, '%m%d')) AS DIFF_YEAR,
							DATE_FORMAT(?, '%Y%m'), DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m'), PERIOD_DIFF(DATE_FORMAT(?, '%Y%m'),DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m')) -12 as MONTHDIFF, EMPLOYEE.CUMULATIVE_VACATION
							FROM USER USER
							LEFT JOIN EMPLOYEE EMPLOYEE ON USER.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID AND SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
							WHERE EMPLOYEE.EMPLOYEE_ID = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($send, $send , $send ,$send , $employeeid));	
				
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

				if ($leavetypeid == 5) {
					if ($diff_year == 0) {
						$allowance = 0 + $cumulative_vacation;
					}
					else {
						if ($diff_year == 1 ) {
							$allowance = $leave_allowance + $cumulative_vacation;
						}
						else {
							$allowance = $leave_allowance + $cumulative_vacation;
						}
					}
					
					if ($allowance >= 12) {
						$allowance = 12;
					}						
				}
				else {
					$allowance = $leave_allowance;
				}
				
				$sql = "	SELECT SUM(ELD.SUMMARY_LEAVE) SUM_LEAVE
							FROM EMPLOYEE_LEAVE_DETAIL ELD
							LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE AND ELH.LEAVE_ID = ELD.LEAVE_ID
							WHERE ELH.EMPLOYEE_ID = ? AND ELH.LEAVE_TYPE_ID = ? AND YEAR(ELD.LEAVE_DATE) = ?";
				
				$used = 0;

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($employeeid, $leavetypeid , $syear));
				
				if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$used = $arr['SUM_LEAVE'];
				}
				
				$remain = $allowance - $used;
				
				if ($remain < $scount) {
					$error_code = 1016;
					include ('window_errormessage.php');
					
					exit();						
				}				
				
				if ($leavetypeid == 5) {
					$lastyear = $remain - $scount;
				}
				else {
					$lastyear = 0;
				}
				
				$sql = "	SELECT USER.EMPLOYEE_ID, EMPLOYEE.START_DATE, YEAR(?) - YEAR(EMPLOYEE.START_DATE) - (DATE_FORMAT(?, '%m%d') < DATE_FORMAT(EMPLOYEE.START_DATE, '%m%d')) AS DIFF_YEAR,
							DATE_FORMAT(?, '%Y%m'), DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m'), PERIOD_DIFF(DATE_FORMAT(?, '%Y%m'),DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m')) -12 as MONTHDIFF, EMPLOYEE.CUMULATIVE_VACATION
							FROM USER USER
							LEFT JOIN EMPLOYEE EMPLOYEE ON USER.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID AND SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
							WHERE EMPLOYEE.EMPLOYEE_ID = ?";

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($eend, $eend , $eend ,$eend , $employeeid));	
				
				$employeeid = 0;
				$diff_year = 0;
				$month_diff = 0;
				
				if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$employeeid = $arr['EMPLOYEE_ID'];
					$diff_year = $arr['DIFF_YEAR'];
					$month_diff = $arr['MONTHDIFF'];
				}

				if ($leavetypeid == 5) {
					if ($diff_year == 0) {
						$allowance = 0 ;
					}
					else {
						$allowance = $leave_allowance + $lastyear;
					}
					
					if ($allowance >= 12) {
						$allowance = 12;
					}						
				}
				else {
					$allowance = $leave_allowance;
				}
				
				
				$sql = "	SELECT SUM(ELD.SUMMARY_LEAVE) SUM_LEAVE
							FROM EMPLOYEE_LEAVE_DETAIL ELD
							LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE AND ELH.LEAVE_ID = ELD.LEAVE_ID
							WHERE ELH.EMPLOYEE_ID = ? AND ELH.LEAVE_TYPE_ID = ? AND YEAR(ELD.LEAVE_DATE) = ?";
				$used = 0;

				$stmt = $conn->prepare($sql);
				$stmt->execute(array($employeeid, $leavetypeid , $eyear));
				
				if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$used = $arr['SUM_LEAVE'];
				}
				
				$remain = $allowance - $used;
				
				if ($remain < $ecount) {
					$error_code = 1016;
					include ('window_errormessage.php');
					
					exit();						
				}					
			}			
		}
		
		$error_code = 0;
		include ('window_errormessage.php');	
	}	
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;	
?>