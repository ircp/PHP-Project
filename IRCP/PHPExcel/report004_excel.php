<?
	require_once('connection.php');
	
	$network = 1;
	$mvno = 11;
	$selectyear = '2017';
	$selectmonth = '04';
	
	require_once('util.php');
	
	$monthdisplay = monthname($selectmonth);
	
	$objectid = 1;
	$processtype = 1;
	$processstatus = 1;
	$processid = 1;
	
//	$processid = updateProcessLog($objectid, $processtype, $processstatus, $processid);
	
	try {		
		$sql = "SELECT MVNO_CODE, MVNO_NAME FROM MVNO";
		$stmt = $conn->prepare($sql);
		$stmt->execute();
			
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$NAME['MVNO'][$arr['MVNO_CODE']]['NAME'] = $arr['MVNO_NAME'];
		}
		
		$sql = "	SELECT REFERENCE_CODE, LABEL FROM REFERENCE_CODE WHERE REFERENCE_TYPE_ID = 2 AND REFERENCE_CODE IN (1,2,3,4,5) 
					ORDER BY REFERENCE_CODE";
		$stmt = $conn->prepare($sql);
		$stmt->execute();
			
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$EVENT[$arr['REFERENCE_CODE']]['NAME'] = $arr['LABEL'];
		}
		
		$sql = "	SELECT REFERENCE_CODE, LABEL FROM REFERENCE_CODE WHERE REFERENCE_TYPE_ID = 4 AND REFERENCE_CODE IN (1,2) 
					ORDER BY REFERENCE_CODE";
		$stmt = $conn->prepare($sql);
		$stmt->execute();
			
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$EVENT_SUB[$arr['REFERENCE_CODE']]['NAME'] = $arr['LABEL'];
		}
		
		$sql = "	SELECT COUNT(*) NCOUNT, SUM(DURATION) DURATION, SUM(DURATION_MINUTE) DURATION_MINUTE, SUM(TOTAL_VOLUME) TOTAL_VOLUME, 
					C_EVENT_TYPE, C_EVENT_SUB_TYPE,  DATE_START_CHARGING
					FROM md_report.CDR
					WHERE C_ROAMING_NETWORK = 1 AND MVNO_CODE = ? AND SUBSTRING(DATE_START_CHARGING, 1, 4) = ? AND SUBSTRING(DATE_START_CHARGING, 6, 2) = ? 
					GROUP BY C_EVENT_TYPE, C_EVENT_SUB_TYPE,  DATE_START_CHARGING
					ORDER BY DATE_START_CHARGING, C_EVENT_SUB_TYPE, C_EVENT_TYPE ";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($mvno, $selectyear, $selectmonth));
		
		$n = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$n = $n + 1;
			
			$EVENT_TYPE = $arr['C_EVENT_TYPE'];
			$EVENT_SUB_TYPE = $arr['C_EVENT_SUB_TYPE'];
			$DURATION = $arr['DURATION'];
			$DURATION_MINUTE = $arr['DURATION_MINUTE'];
			$TOTAL_VOLUME = $arr['TOTAL_VOLUME'];
			$DATE_START_CHARGING = $arr['DATE_START_CHARGING'];
			$RECORD_COUNT = $arr['NCOUNT'];
			
			if (! isset($RESULT[$DATE_START_CHARGING][$EVENT_SUB_TYPE][$EVENT_TYPE]['RECORD_COUNT'])) {
				$RESULT[$DATE_START_CHARGING][$EVENT_SUB_TYPE][$EVENT_TYPE]['RECORD_COUNT'] = $RECORD_COUNT;
			}
			else {
				$RESULT[$DATE_START_CHARGING][$EVENT_SUB_TYPE][$EVENT_TYPE]['RECORD_COUNT'] = $RECORD_COUNT + $RESULT[$DATE_START_CHARGING][$EVENT_SUB_TYPE][$EVENT_TYPE]['RECORD_COUNT'];
			}
			if (! isset($RESULT[$DATE_START_CHARGING][$EVENT_SUB_TYPE][$EVENT_TYPE]['DURATION'])) {
				$RESULT[$DATE_START_CHARGING][$EVENT_SUB_TYPE][$EVENT_TYPE]['DURATION'] = $DURATION;
			}
			else {
				$RESULT[$DATE_START_CHARGING][$EVENT_SUB_TYPE][$EVENT_TYPE]['DURATION'] = $DURATION + $RESULT[$DATE_START_CHARGING][$EVENT_SUB_TYPE][$EVENT_TYPE]['DURATION'];
			}
			if (! isset($RESULT[$DATE_START_CHARGING][$EVENT_SUB_TYPE][$EVENT_TYPE]['DURATION_MINUTE'])) {
				$RESULT[$DATE_START_CHARGING][$EVENT_SUB_TYPE][$EVENT_TYPE]['DURATION_MINUTE'] = $DURATION_MINUTE;
			}
			else {
				$RESULT[$DATE_START_CHARGING][$EVENT_SUB_TYPE][$EVENT_TYPE]['DURATION_MINUTE'] = $DURATION_MINUTE + $RESULT[$DATE_START_CHARGING][$EVENT_SUB_TYPE][$EVENT_TYPE]['DURATION_MINUTE'];
			}
			if (! isset($RESULT[$DATE_START_CHARGING][$EVENT_SUB_TYPE][$EVENT_TYPE]['TOTAL_VOLUME'])) {
				$RESULT[$DATE_START_CHARGING][$EVENT_SUB_TYPE][$EVENT_TYPE]['TOTAL_VOLUME'] = $TOTAL_VOLUME;
			}
			else {
				$RESULT[$DATE_START_CHARGING][$EVENT_SUB_TYPE][$EVENT_TYPE]['TOTAL_VOLUME'] = $TOTAL_VOLUME + $RESULT[$DATE_START_CHARGING][$EVENT_SUB_TYPE][$EVENT_TYPE]['TOTAL_VOLUME'];
			}
			
			if (! isset($SUM_EVENT[$EVENT_SUB_TYPE][$EVENT_TYPE]['RECORD_COUNT'])) {
				$SUM_EVENT[$EVENT_SUB_TYPE][$EVENT_TYPE]['RECORD_COUNT'] = $RECORD_COUNT;
			}
			else {
				$SUM_EVENT[$EVENT_SUB_TYPE][$EVENT_TYPE]['RECORD_COUNT'] = $RECORD_COUNT + $SUM_EVENT[$EVENT_SUB_TYPE][$EVENT_TYPE]['RECORD_COUNT'];
			}	
			if (! isset($SUM_EVENT[$EVENT_SUB_TYPE][$EVENT_TYPE]['DURATION'])) {
				$SUM_EVENT[$EVENT_SUB_TYPE][$EVENT_TYPE]['DURATION'] = $DURATION;
			}
			else {
				$SUM_EVENT[$EVENT_SUB_TYPE][$EVENT_TYPE]['DURATION'] = $DURATION + $SUM_EVENT[$EVENT_SUB_TYPE][$EVENT_TYPE]['DURATION'];
			}
			if (! isset($SUM_EVENT[$EVENT_SUB_TYPE][$EVENT_TYPE]['DURATION_MINUTE'])) {
				$SUM_EVENT[$EVENT_SUB_TYPE][$EVENT_TYPE]['DURATION_MINUTE'] = $DURATION_MINUTE;
			}
			else {
				$SUM_EVENT[$EVENT_SUB_TYPE][$EVENT_TYPE]['DURATION_MINUTE'] = $DURATION_MINUTE + $SUM_EVENT[$EVENT_SUB_TYPE][$EVENT_TYPE]['DURATION_MINUTE'];
			}
			if (! isset($SUM_EVENT[$EVENT_SUB_TYPE][$EVENT_TYPE]['TOTAL_VOLUME'])) {
				$SUM_EVENT[$EVENT_SUB_TYPE][$EVENT_TYPE]['TOTAL_VOLUME'] = $TOTAL_VOLUME;
			}
			else {
				$SUM_EVENT[$EVENT_SUB_TYPE][$EVENT_TYPE]['TOTAL_VOLUME'] = $TOTAL_VOLUME + $SUM_EVENT[$EVENT_SUB_TYPE][$EVENT_TYPE]['TOTAL_VOLUME'];
			}				
		}		


	$header1 = "รายละเอียดการใช้บริการโทรศัพท์เคลื่อนที่ 3G ของบริษัท ".$NAME['MVNO'][$mvno]['NAME'];
	$header2 = "ประจำเดือน ".$monthdisplay." ".$selectyear;
	$title = "Traffic_Usage_TOT3G";
	$filename = "Traffic_Usage_TOT3G_".$NAME['MVNO'][$mvno]['NAME']."_".$selectyear.$selectmonth.".xls";
		
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	date_default_timezone_set('Europe/London');

	define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

	/** Include PHPExcel */
	require_once dirname(__FILE__) . '\PHPExcel.php';

	$objPHPExcel = new PHPExcel();

	$styleHeader = array(
			'font'  => array(
			'bold'  => true,
			'color' => array('rgb' => '000000'),
			'size'  => 18,
			'name'  => 'TH SarabunPSK',
		)
	);

	$stylesubHeader = array(
			'font'  => array(
			'bold'  => true,
			'color' => array('rgb' => '000000'),
			'size'  => 16,
			'name'  => 'TH SarabunPSK',
		)
	);

	$styleInfo = array(
			'font'  => array(
			'bold'  => false,
			'color' => array('rgb' => '000000'),
			'size'  => 16,
			'name'  => 'TH SarabunPSK'
		)
	);

	$objPHPExcel->getDefaultStyle()->applyFromArray($styleInfo);	

	$line = 5;
	$currentstrr = 'A';

	$startoffset = 'A'."".$line;
	$startstrr = $currentstrr."".$line;
	$line = $line + 2;
	$endstrr =  $currentstrr."".$line;
	$dstrr = $startstrr.":".$endstrr;

	
	
	// Table Header 
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "เดือน");
	$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
	
	$line = 7; 
	$currentstrr = 'B';
	
	foreach ($EVENT_SUB as $subid => $nresult) {
		$starteventsub = $currentstrr;
		foreach ($EVENT as $eventid => $mresult) {
			$startstrr = $currentstrr."".$line;
			if ($eventid == 4) {
				if ($subid == 1) {
					$stt = $currentstrr;
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "จำนวน");
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
					$currentstrr = ++$currentstrr;
					
					$startstrr = $currentstrr."".$line;
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "จำนวน Byte");
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
					$currentstrr = ++$currentstrr;
					
					$laststrr = $currentstrr;
					$startstrr = $currentstrr."".$line;
					
					$datastrr = $currentstrr;
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "Total data (MB)");
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
					
					$nline = 6;
					$ent = $currentstrr;
					$st = $stt.$nline;
					$en = $ent.$nline;
					$dt = $st.":".$en;
					$objPHPExcel->getActiveSheet()->setCellValue($st, $mresult['NAME']);
					$objPHPExcel->getActiveSheet()->mergeCells($dt);
					$objPHPExcel->getActiveSheet()->getStyle($st)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($st)->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getStyle($st)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
						
					$endeventsub = $currentstrr;
				}
			}
			else if ($eventid == 3 || $eventid == 5) {
				if (($subid == 1) || ($subid == 2 && $eventid == 3)) {
					
					$stt = $currentstrr;
					
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "จำนวน");
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
					$currentstrr = ++$currentstrr;
					
					$laststrr = $currentstrr;
					$startstrr = $currentstrr."".$line;
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "จำนวนครั้ง");
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	

					$nline = 6;
					$ent = $currentstrr;
					$st = $stt.$nline;
					$en = $ent.$nline;
					$dt = $st.":".$en;
					$objPHPExcel->getActiveSheet()->setCellValue($st, $mresult['NAME']);
					$objPHPExcel->getActiveSheet()->mergeCells($dt);
					$objPHPExcel->getActiveSheet()->getStyle($st)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($st)->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getStyle($st)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
					
					$endeventsub = $currentstrr;
				}
			}
			else {
				$stt = $currentstrr;
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "จำนวน");
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
				$currentstrr = ++$currentstrr;
				
				if ($subid == 1) {
					$startstrr = $currentstrr."".$line;
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "จำนวนวินาที");
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
					$currentstrr = ++$currentstrr;
				}
				
				$laststrr = $currentstrr;
				$startstrr = $currentstrr."".$line;
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "จำนวนนาที");
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
					
				$nline = 6;
				$ent = $currentstrr;
				$st = $stt.$nline;
				$en = $ent.$nline;
				$dt = $st.":".$en;
				$objPHPExcel->getActiveSheet()->setCellValue($st, $mresult['NAME']);
				$objPHPExcel->getActiveSheet()->mergeCells($dt);
				$objPHPExcel->getActiveSheet()->getStyle($st)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($st)->getAlignment()->setWrapText(true); 
				$objPHPExcel->getActiveSheet()->getStyle($st)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
				$endeventsub = $currentstrr;
			}
			
			$currentstrr = ++$currentstrr;
		}
		$nline = 5;
		$st = $starteventsub.$nline;
		$en = $endeventsub.$nline;
		$dt = $st.":".$en;
		$objPHPExcel->getActiveSheet()->setCellValue($st, $nresult['NAME']);
		$objPHPExcel->getActiveSheet()->mergeCells($dt);
		$objPHPExcel->getActiveSheet()->getStyle($st)->applyFromArray($stylesubHeader);
		$objPHPExcel->getActiveSheet()->getStyle($st)->getAlignment()->setWrapText(true); 
		$objPHPExcel->getActiveSheet()->getStyle($st)->getAlignment()->applyFromArray(
			array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));			
	}

	$line = 2;
	$currentstrr = 'A';
	$startstrr = $currentstrr."".$line;
	$endstrr =  $endeventsub."".$line;
	$dstrr = $startstrr.":".$endstrr;
		
	// Title			
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $header1);
	$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleHeader);
	$objPHPExcel->getActiveSheet()->getStyle($st)->getAlignment()->setWrapText(true); 
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

	$line = 3;
	$currentstrr = 'A';
	$startstrr = $currentstrr."".$line;
	$endstrr =  $endeventsub."".$line;
	$dstrr = $startstrr.":".$endstrr;

	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $header2);
	$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleHeader);
	$objPHPExcel->getActiveSheet()->getStyle($st)->getAlignment()->setWrapText(true); 
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
		
		
	$currentstrr = 'A';
	$line = 8;
	
	if (! isset($RESULT)) {

	}
	else {
		foreach ($RESULT as $date => $mresult) {
			$currentstrr = 'A';
			$startstrr = $currentstrr."".$line;
			
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $date);
			$objPHPExcel->getActiveSheet()->getStyle($st)->getAlignment()->setWrapText(true); 
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
			
			$currentstrr = ++$currentstrr;
			
			$dataline = $line;
			foreach ($EVENT_SUB as $subid => $nresult) {
				foreach ($EVENT as $eventid => $presult) {
					if ($eventid == 4) {
						if ($subid == 1) {
							$startstrr = $currentstrr."".$line;
							
							if ( ! isset($mresult[$subid][$eventid]['RECORD_COUNT'])) {
								$recordcount = 0;
							}
							else {
								$recordcount = $mresult[$subid][$eventid]['RECORD_COUNT'];
							}
							if ( ! isset($mresult[$subid][$eventid]['TOTAL_VOLUME'])) {
								$totalvolume = 0;
							}
							else {
								$totalvolume = $mresult[$subid][$eventid]['TOTAL_VOLUME'];
							}
							
							$startstrr = $currentstrr."".$line;
							$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($recordcount,0));
							$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
								array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
							$currentstrr = ++$currentstrr;
							
							$startstrr = $currentstrr."".$line;
							$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($totalvolume,0));
							$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
								array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));	
							$currentstrr = ++$currentstrr;
							
						}
					}
					else if ($eventid == 3 || $eventid == 5) {
						if ($subid == 1 || ($subid == 2 && $eventid == 3)) {
							$startstrr = $currentstrr."".$line;
							
							if ( ! isset($mresult[$subid][$eventid]['RECORD_COUNT'])) {
								$recordcount = 0;
							}
							else {
								$recordcount = $mresult[$subid][$eventid]['RECORD_COUNT'];
							}
							if ( ! isset($mresult[$subid][$eventid]['TOTAL_VOLUME'])) {
								$totalvolume = 0;
							}
							else {
								$totalvolume = $mresult[$subid][$eventid]['TOTAL_VOLUME'];
							}
							$startstrr = $currentstrr."".$line;
							$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($recordcount,0));
							$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
								array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
							$currentstrr = ++$currentstrr;
							
							$startstrr = $currentstrr."".$line;
							$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($totalvolume,0));
							$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
								array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
						}
					}
					else {
						if ( ! isset($mresult[$subid][$eventid]['RECORD_COUNT'])) {
							$recordcount = 0;
						}
						else {
							$recordcount = $mresult[$subid][$eventid]['RECORD_COUNT'];
						}
						if ( ! isset($mresult[$subid][$eventid]['DURATION'])) {
							$duration = 0;
						}
						else {
							$duration = $mresult[$subid][$eventid]['DURATION'];
						}
						if ( ! isset($mresult[$subid][$eventid]['DURATION_MINUTE'])) {
							$durationminute = 0;
						}
						else {
							$durationminute = $mresult[$subid][$eventid]['DURATION_MINUTE'];
						}
						$startstrr = $currentstrr."".$line;
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($recordcount,0));
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
						$currentstrr = ++$currentstrr;	
						
						if ($subid == 1) {
							$startstrr = $currentstrr."".$line;
							$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($duration,0));
							$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
								array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
							$currentstrr = ++$currentstrr;								
						}
						$startstrr = $currentstrr."".$line;
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($durationminute,0));
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));						
					}
					$currentstrr = ++$currentstrr;
				}
			}
			$line = ++$line;
		}		
	}
	
	$nline = 8;
	$st = $datastrr."".$nline;
	$en = $datastrr."".$dataline;
	$dt = $st.":".$en;
	$objPHPExcel->getActiveSheet()->mergeCells($dt);
	
	$currentstrr = 'A';

	$startstrr = $currentstrr ."".$line;
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'Grand Total');
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
	$objPHPExcel->getActiveSheet()->getStyle($st)->getAlignment()->setWrapText(true); 
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
	$currentstrr = ++$currentstrr;

	foreach ($EVENT_SUB as $subid => $nresult) {
		foreach ($EVENT as $eventid => $presult) {
			if ($eventid == 4) {
				if ($subid == 1) {
					$startstrr = $currentstrr."".$line;
					
					if ( ! isset($SUM_EVENT[$subid][$eventid]['RECORD_COUNT'])) {
						$recordcount = 0;
					}
					else {
						$recordcount = $SUM_EVENT[$subid][$eventid]['RECORD_COUNT'];
					}
					if ( ! isset($SUM_EVENT[$subid][$eventid]['TOTAL_VOLUME'])) {
						$totalvolume = 0;
					}
					else {
						$totalvolume = $SUM_EVENT[$subid][$eventid]['TOTAL_VOLUME'];
					}
					
					$startstrr = $currentstrr."".$line;
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($recordcount,0));
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
					$currentstrr = ++$currentstrr;
					
					$lastoff = $currentstrr;
					$startstrr = $currentstrr."".$line;
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($totalvolume,0));
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));	
					$currentstrr = ++$currentstrr;
					
					$totalmb = $totalvolume/1024/1024;
					$lastoff = $currentstrr;
					$startstrr = $currentstrr."".$line;
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($totalmb,2));
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));	
				}				
			}
			else if ($eventid == 3 || $eventid == 5) {
				if ($subid == 1 || ($subid == 2 && $eventid == 3)) {
					$startstrr = $currentstrr."".$line;
					
					if ( ! isset($SUM_EVENT[$subid][$eventid]['RECORD_COUNT'])) {
						$recordcount = 0;
					}
					else {
						$recordcount = $SUM_EVENT[$subid][$eventid]['RECORD_COUNT'];
					}
					if ( ! isset($SUM_EVENT[$subid][$eventid]['TOTAL_VOLUME'])) {
						$totalvolume = 0;
					}
					else {
						$totalvolume = $SUM_EVENT[$subid][$eventid]['TOTAL_VOLUME'];
					}
					$startstrr = $currentstrr."".$line;
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($recordcount,0));
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
					$currentstrr = ++$currentstrr;
					
					$lastoff = $currentstrr;
					
					$startstrr = $currentstrr."".$line;
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($totalvolume,0));
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
				}			
			}
			else {
				if ( ! isset($SUM_EVENT[$subid][$eventid]['RECORD_COUNT'])) {
					$recordcount = 0;
				}
				else {
					$recordcount = $SUM_EVENT[$subid][$eventid]['RECORD_COUNT'];
				}
				if ( ! isset($SUM_EVENT[$subid][$eventid]['DURATION'])) {
					$duration = 0;
				}
				else {
					$duration = $SUM_EVENT[$subid][$eventid]['DURATION'];
				}
				if ( ! isset($SUM_EVENT[$subid][$eventid]['DURATION_MINUTE'])) {
					$durationminute = 0;
				}
				else {
					$durationminute = $SUM_EVENT[$subid][$eventid]['DURATION_MINUTE'];
				}
				$startstrr = $currentstrr."".$line;
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($recordcount,0));
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
				$currentstrr = ++$currentstrr;	
				
				
				if ($subid == 1) {
					$startstrr = $currentstrr."".$line;
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($duration,0));
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
					$currentstrr = ++$currentstrr;								
				}
				
				$startstrr = $currentstrr."".$line;
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($durationminute,0));
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));						
			}
			$currentstrr = ++$currentstrr;
		}	
	}

	$endoffset = $endeventsub."".$line;

	$offset = $startoffset.":".$endoffset;

	 $objPHPExcel->getActiveSheet()->getStyle($offset)->applyFromArray(array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
				)
			)
		)); 
	
	$objPHPExcel->getActiveSheet()->setTitle($title);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save($filename);
	
	$processstatus = 2;
//	$processid = updateProcessLog($objectid, $processtype, $processstatus, $processid);
	
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'SQL:' .$query;
		echo 'Error:' .$e;
		$processstatus = 3;
//		$processid = updateProcessLog($objectid, $processtype, $processstatus, $processid);
	}
	
	$conn = null;
?>
