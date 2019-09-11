<?
	try {
		set_time_limit(0);
		
		$inputdirectory = dirname(__FILE__)."/FILES/IN_OUT/INPUT/";
		$processdirectory = dirname(__FILE__)."/FILES/IN_OUT/PROCESSED/";
		$backupdirectory = dirname(__FILE__)."/FILES/IN_OUT/BACKUP/";

		$process = 1;
		$fi = new FilesystemIterator($inputdirectory, FilesystemIterator::SKIP_DOTS);
		$countfile =  iterator_count($fi);
		if ($countfile == 0) {
			$process = 0;
		}
		
		$fi = new FilesystemIterator($processdirectory, FilesystemIterator::SKIP_DOTS);
		$countfile =  iterator_count($fi);
		
		if ($countfile > 0) {
			$process = 0;
		}
		
		if ($process == 1) {
			if ($dh = opendir($inputdirectory)) {
				while (($filen = readdir($dh)) !== false ) {
					if ($filen != "." && $filen != "..") {
						rename($inputdirectory.$filen, $processdirectory.$filen);
					}
				}
				closedir($dh);
			}
			
			$sql = "	SELECT REFERENCE_CODE, LABEL 
						FROM REFERENCE_CODE
						WHERE REFERENCE_TYPE_ID = 13
						ORDER BY REFERENCE_CODE";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute();
		
			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				if ($arr['REFERENCE_CODE'] == 1) {
					$startwtime = $arr['LABEL'];
				}
				if ($arr['REFERENCE_CODE'] == 2) {
					$endwtime = $arr['LABEL'];
				}
				if ($arr['REFERENCE_CODE'] == 3) {
					$allowance = (int) $arr['LABEL'];
				}
			}
					
			if ($dh = opendir($processdirectory)) {
				while (($filen = readdir($dh)) !== false ) {
					if ($filen != "." && $filen != "..") {
						$RECORD = explode("\n", file_get_contents($processdirectory.$filen));
						$total_record = count($RECORD) - 1;					
						for ($x = 0; $x < $total_record; $x++) {
							$SUB_RECORD =  $RECORD[$x];
							
							$employeecode = trim(substr($SUB_RECORD, 0, 7));
							$recordtype = trim(substr($SUB_RECORD, 7, 1));
							$recorddate = '20'.trim(substr($SUB_RECORD, 9, 6));
							$recordtime = trim(substr($SUB_RECORD, 16, 4));
							$machinetype = trim(substr($SUB_RECORD, 21, 2));					
							$recorddatetime = substr($recorddate,0, 4)."-".substr($recorddate,4,2)."-".substr($recorddate,6,2)." ".substr($recordtime,0,2).":".substr($recordtime, 2,2).":00";
							$rec_date = new DateTime($recorddatetime);
			
							$starttime = substr($recorddate,0, 4)."-".substr($recorddate,4,2)."-".substr($recorddate,6,2)." ".$startwtime;
							$endtime = substr($recorddate,0, 4)."-".substr($recorddate,4,2)."-".substr($recorddate,6,2)." ".$endwtime;
							$workingdate = substr($recorddate,0, 4)."-".substr($recorddate,4,2)."-".substr($recorddate,6,2)." 00:00:00";
							
							$start_time = new DateTime($starttime);
							
							if ($allowance != 0) {
								$allowlate = "PT".$allowance."M";
								$start_time->add(new DateInterval($allowlate));
							}
							
							$end_time = new DateTime($endtime);
							
							$employeeid = 0;
							
							$sql = "	SELECT EMPLOYEE_ID 
										FROM EMPLOYEE
										WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
										AND EMPLOYEE_CODE = ?";
										
							$stmt = $conn->prepare($sql);
							$stmt->execute(array($employeecode));
							
							if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
								$employeeid = $arr['EMPLOYEE_ID'];
							}
							
							if ($employeeid !=0 && ($machinetype == '01' || $machinetype == '02')) {					
								$inoutid = 0;
								$indate = '';
								$outdate = '';
								
								$sql = "	SELECT IN_OUT_ID, IN_DATE, OUT_DATE
											FROM IN_OUT_RECORD
											WHERE  EMPLOYEE_ID = ? AND DATE(IN_DATE) = ?";
								$stmt = $conn->prepare($sql);
								$stmt->execute(array($employeeid, $recorddate));
								
								if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
									$inoutid = $arr['IN_OUT_ID'];
									$indate = $arr['IN_DATE'];
									$outdate = $arr['OUT_DATE'];
								}
						
								if ($inoutid == 0) {	
																
									$sql = "	INSERT INTO SEQUENCE_IN_OUT_ID (EMPLOYEE_CODE) VALUES (?)";
									
									$stmt = $conn->prepare($sql);
									$stmt->execute(array($employeecode));
									
									$sql = "	SELECT LAST_INSERT_ID() IN_OUT_ID";
									
									$stmt = $conn->prepare($sql);
									$stmt->execute();
									
									if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
										$inoutid = $arr['IN_OUT_ID'];
									}
									
									$inoutstatus = 1;
									
									$sql = "	INSERT INTO IN_OUT_RECORD (IN_OUT_ID, CREATED_DATE, LAST_MODIFIED, USER_ID, EMPLOYEE_ID, IN_DATE, OUT_DATE, IN_OUT_STATUS, FILENAME)
												VALUES (?, NOW(), NOW(), 1,  ?, ?, ?, ?, ?)";
									$stmt = $conn->prepare($sql);
									$stmt->execute(array($inoutid, $employeeid, $recorddatetime, $recorddatetime, $inoutstatus,$filen));
								}
								else {
									$in_date = new DateTime($indate);
									$out_date = new DateTime($outdate);
									
									if ($rec_date < $in_date) {
										$sql  = " 	UPDATE IN_OUT_RECORD
														SET IN_DATE = ?
														WHERE  IN_OUT_ID = ?";
										$stmt = $conn->prepare($sql);
										$stmt->execute(array($recorddatetime,$inoutid));
									}
									if ($rec_date > $out_date) {
										$sql  = " 	UPDATE IN_OUT_RECORD
														SET OUT_DATE = ?
														WHERE  IN_OUT_ID = ?";
										$stmt = $conn->prepare($sql);
										$stmt->execute(array($recorddatetime, $inoutid));									
									}
								}
								
								if ($inoutid != 0) {										
									$sql = "	SELECT IN_OUT_ID, IN_DATE, OUT_DATE, EMPLOYEE_ID
												FROM IN_OUT_RECORD
												WHERE IN_OUT_ID = ?";
									$stmt = $conn->prepare($sql);
									$stmt->execute(array($inoutid));
									
									if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
										$indate = $arr['IN_DATE'];
										$outdate = $arr['OUT_DATE'];
										$employee = $arr['EMPLOYEE_ID'];
									}
									$in_date = new DateTime($indate);
									$out_date = new DateTime($outdate);
									
									$interval_in  = 0;
									$interval_out = 0;
									$inoutstatus = 3;
									
									if (($in_date > $start_time) || ($out_date < $end_time) || ($in_date > $start_time && $out_date < $end_time)) {									
										$inoutstatus = 1;
										
										if ($in_date > $start_time) {
											$interval = $start_time->diff($in_date);
											$inter = (string) ($interval->format('%H:%I:%S'));
											
											$interval_in = (((int) (substr($inter, 0, 2))) * 60) + ((int) (substr($inter, 3, 2)));
										}
										if ($out_date < $end_time) {
											$interval = $end_time->diff($out_date);
											$inter = (string) ($interval->format('%H:%I:%S'));
											
											$interval_out = (((int) (substr($inter, 0, 2))) * 60) + ((int) (substr($inter, 3, 2)));										
										}
									}
									
									$sql = "	UPDATE IN_OUT_RECORD
												SET IN_OUT_STATUS = ?
												WHERE  IN_OUT_ID = ?";

									$stmt = $conn->prepare($sql);
									$stmt->execute(array($inoutstatus, $inoutid));
									
									$sql = "	SELECT WORKING_STATUS 
												FROM WORKING_DAY
												WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
												AND EMPLOYEE_ID = ? AND WORKING_DAY = ?";
									$stmt = $conn->prepare($sql);
									$stmt->execute(array($employee, $workingdate));	
									
									if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
										$workingstatus = $arr['WORKING_STATUS'];
									}
									
									if ($workingstatus != 1) {
										$inoutstatus = $workingstatus;
									}
									
									$sql = 	"	UPDATE WORKING_DAY
													SET WORKING_IN = ?, WORKING_OUT = ?, WORKING_STATUS = ?, IN_OUT_ID =?, INTERVAL_IN = ?, INTERVAL_OUT = ?
													WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
													AND EMPLOYEE_ID = ? AND WORKING_DAY = ?";

									$stmt = $conn->prepare($sql);
									$stmt->execute(array($indate, $outdate, $inoutstatus, $inoutid, $interval_in, $interval_out ,$employee, $workingdate));												
									
								}
							}
						}
						rename($processdirectory.$filen, $backupdirectory.$filen);
					}
				}
				closedir($dh);
			}	
		}
		
		$sql = "	SELECT EMPLOYEE_ID, WORKING_DAY
					FROM WORKING_DAY
					WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
					AND WORKING_STATUS = 1";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute();						
		
		UNSET ($WORKING_LIST);
		
		$i = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$i = $i + 1;
			$WORKING_LIST[$i]['EMPLOYEE_ID'] = $arr['EMPLOYEE_ID'];
			$WORKING_LIST[$i]['WORKING_DAY'] = $arr['WORKING_DAY'];
		}
		
		if (isset($WORKING_LIST)) {
			foreach ($WORKING_LIST as $workid => $w_result) {
				$employee = $w_result['EMPLOYEE_ID'];
				$workingdate = $w_result['WORKING_DAY'];
				
				$sql = "	SELECT ELH.LEAVE_ID
							FROM EMPLOYEE_LEAVE_HISTORY ELH
							LEFT JOIN EMPLOYEE_LEAVE_DETAIL ELD ON ELD.LEAVE_ID = ELH.LEAVE_ID
							WHERE SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
							AND ELH.EMPLOYEE_ID = ? AND ELD.LEAVE_DATE = ? AND ELH.LEAVE_STATUS NOT IN (4,5)";
				$stmt = $conn->prepare($sql);
				$stmt->execute(array($employee, $workingdate));	

				$leaveid = '';
				if ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$leaveid = $arr['LEAVE_ID'];
				}
				
				if ($leaveid != '') {
					$sql = "	UPDATE WORKING_DAY
								SET LEAVE_ID = ?, WORKING_STATUS = 3
								WHERE SYSDATE() BETWEEN EFFECTIVE_START_DATE AND EFFECTIVE_END_DATE
								AND EMPLOYEE_ID = ? AND WORKING_DAY = ?";
					$stmt = $conn->prepare($sql);
					$stmt->execute(array($leaveid, $employee, $workingdate));									
				}
			}
		}
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: IN_OUT_RECORD ' .$e->getMessage();
	}
?>