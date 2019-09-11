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
		
		$sql = "	SELECT REFERENCE_CODE, LABEL FROM REFERENCE_CODE WHERE REFERENCE_TYPE_ID = 2 AND REFERENCE_CODE IN (1,2,3,4) 
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
		
		$sql = "	SELECT COUNT(*) NCOUNT, SUM(DURATION) NSUMMARY, SUM(DURATION_MINUTE) MSUMMARY, SUM(TOTAL_VOLUME) TSUMMARY, 
					C_EVENT_TYPE, C_EVENT_SUB_TYPE,  DATE_START_CHARGING, 
					C_ROAMING_NETWORK, C_ROAMING_NETWORK_NAME
					FROM md_report.CDR
					WHERE C_ROAMING_NETWORK = ? AND MVNO_CODE = ? AND SUBSTRING(DATE_START_CHARGING, 1, 4) = ? AND SUBSTRING(DATE_START_CHARGING, 6, 2) = ? 
					GROUP BY C_EVENT_TYPE, C_EVENT_SUB_TYPE,  DATE_START_CHARGING, C_ROAMING_NETWORK, C_ROAMING_NETWORK_NAME
					ORDER BY DATE_START_CHARGING, C_EVENT_TYPE, C_EVENT_SUB_TYPE";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($network, $mvno, $selectyear, $selectmonth));
		
		$n = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$n = $n + 1;
			
			$EVENT_TYPE = $arr['C_EVENT_TYPE'];
			$EVENT_SUB_TYPE = $arr['C_EVENT_SUB_TYPE'];
						
			if ($EVENT_TYPE == 4 || $EVENT_TYPE == 3) {				
				$summarytotal = $arr['TSUMMARY'];
			}
			else  {
				if ($EVENT_SUB_TYPE == 1) {
					$summarytotal = $arr['NSUMMARY'];
				}
				else if ($EVENT_SUB_TYPE == 2){
					$summarytotal = $arr['MSUMMARY'];
				}
			}
			
			if (! isset($RESULT[$arr['DATE_START_CHARGING']][$EVENT_TYPE][$EVENT_SUB_TYPE]['SUMMARY'])) {
				$RESULT[$arr['DATE_START_CHARGING']][$EVENT_TYPE][$EVENT_SUB_TYPE]['SUMMARY'] = $summarytotal;			
			}
			else {
				$RESULT[$arr['DATE_START_CHARGING']][$EVENT_TYPE][$EVENT_SUB_TYPE]['SUMMARY'] = $summarytotal + $RESULT[$arr['DATE_START_CHARGING']][$EVENT_TYPE][$EVENT_SUB_TYPE]['SUMMARY'];
			}
			
			if (! isset($RESULT[$arr['DATE_START_CHARGING']][$EVENT_TYPE][$EVENT_SUB_TYPE]['COUNT'])) {
				$RESULT[$arr['DATE_START_CHARGING']][$EVENT_TYPE][$EVENT_SUB_TYPE]['COUNT'] = $arr['NCOUNT'];				
			}
			else {
				$RESULT[$arr['DATE_START_CHARGING']][$EVENT_TYPE][$EVENT_SUB_TYPE]['COUNT'] = $arr['NCOUNT'] + $RESULT[$arr['DATE_START_CHARGING']][$EVENT_TYPE][$EVENT_SUB_TYPE]['COUNT'];
			}
			
			$NAME['NETWORK'][$arr['C_ROAMING_NETWORK']]['NAME'] = $arr['C_ROAMING_NETWORK_NAME'];
			
			if (! isset($SUM[$EVENT_TYPE][$EVENT_SUB_TYPE]['SUMMARY'])) {
				$SUM[$EVENT_TYPE][$EVENT_SUB_TYPE]['SUMMARY'] = $summarytotal;			
			}
			else {
				$SUM[$EVENT_TYPE][$EVENT_SUB_TYPE]['SUMMARY'] = $summarytotal + $SUM[$EVENT_TYPE][$EVENT_SUB_TYPE]['SUMMARY'];
			}
			if (! isset($SUM[$EVENT_TYPE][$EVENT_SUB_TYPE]['COUNT'])) {
				$SUM[$EVENT_TYPE][$EVENT_SUB_TYPE]['COUNT'] = $arr['NCOUNT'];				
			}
			else {
				$SUM[$EVENT_TYPE][$EVENT_SUB_TYPE]['COUNT'] = $arr['NCOUNT'] + $SUM[$EVENT_TYPE][$EVENT_SUB_TYPE]['COUNT'];
			}
		}		

	
	$header1 = "รายละเอียดการใช้บริการ Roaming ของบริษัท ".$NAME['MVNO'][$mvno]['NAME'];
	$header2 = "ประจำเดือน ".$monthdisplay." ".$selectyear;
	$title = "MVNO_Traffic_Usage_Roaming";
	$filename = "MVNO_Traffic_Usage_Roaming_".$NAME['MVNO'][$mvno]['NAME']."_".$selectyear.$selectmonth.".xls";
		

	
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

	$currentstrr = 'B';
	$line = 6;
	foreach ($EVENT as $eventid => $mresult) {
		
		$startstrr = $currentstrr."".$line;
		$num = 3;
		if ($eventid == 4) {
			$num = 2;
		}
		for ($i = 1; $i <= $num; $i++) {
			$currentstrr = ++$currentstrr;
		}
		$endstrr =  $currentstrr."".$line;
		$dstrr = $startstrr.":".$endstrr;
		$laststr = $currentstrr;
		
		$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $mresult['NAME']);
		$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
		$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
		$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
			array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
		
		$currentstrr = ++$currentstrr;
	}

	$line = 2;
	$currentstrr = 'A';
	$startstrr = $currentstrr."".$line;
	$endstrr =  $laststr."".$line;
	$dstrr = $startstrr.":".$endstrr;
		
	// Title			
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $header1);
	$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleHeader);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

	$line = 3;
	$currentstrr = 'A';
	$startstrr = $currentstrr."".$line;
	$endstrr =  $laststr."".$line;
	$dstrr = $startstrr.":".$endstrr;

	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $header2);
	$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleHeader);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
	 
	$line = 5;
	$currentstrr = 'A';
	$startstrr = $currentstrr."".$line;
	$line = $line + 3;
	$endstrr =  $currentstrr."".$line;
	$dstrr = $startstrr.":".$endstrr;

	// Table Header 
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "เดือน");
	$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleHeader);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

	$line = 5;
	$currentstrr = 'B';
	$startstrr = $currentstrr."".$line;
	$endstrr =  $laststr."".$line;
	$dstrr = $startstrr.":".$endstrr;

	$startoffset = 'A'."".$line;

	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $NAME['NETWORK'][$network]['NAME']);
	$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleHeader);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
		
	$currentstrr = 'B';
	$line = 7;
	foreach ($EVENT as $eventid => $mresult) {	
		if ($eventid == 4) {
			$startstrr = $currentstrr."".$line;
			$nextline = ++$line;
			$endstrr =  $currentstrr."".$nextline;
			$dstrr = $startstrr.":".$endstrr;
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "จำนวน Record");
			$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
			$currentstrr = ++$currentstrr;
			
			$line = 7;
			$startstrr = $currentstrr."".$line;
			$nextline = ++$line;
			$endstrr =  $currentstrr."".$nextline;
			$dstrr = $startstrr.":".$endstrr;
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "จำนวน Byte");
			$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
			$currentstrr = ++$currentstrr;
			
			$line = 7;
			$startstrr = $currentstrr."".$line;
			$nextline = ++$line;
			$endstrr =  $currentstrr."".$nextline;
			$dstrr = $startstrr.":".$endstrr;
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "Total Data (MB)");
			$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));			
		}
		else {
			foreach ($EVENT_SUB as $subid => $sresult) {
				$startstrr = $currentstrr."".$line;
				$num = 1;
				for ($i = 1; $i <= $num; $i++) {
					$currentstrr = ++$currentstrr;
				}
				$endstrr =  $currentstrr."".$line;
				$dstrr = $startstrr.":".$endstrr;
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $sresult['NAME']);
				$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
				$currentstrr = ++$currentstrr;
			}
		}	
	}

	$currentstrr = 'B';
	$line = 8;
	foreach ($EVENT as $eventid => $mresult) {	
		if ($eventid != 4) {
			foreach ($EVENT_SUB as $subid => $sresult) {
				$startstrr = $currentstrr."".$line;
				$dstrr = $startstrr.":".$endstrr;
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'จำนวน');
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));		
					
				$currentstrr = ++$currentstrr;
				$startstrr = $currentstrr."".$line;
				$header3 = 'จำนวนนาที';
				if ($eventid == 1 || $eventid == 2) {
					if ($subid == 1) {
						$header3 = 'จำนวนวินาที';
					}
				}
				else {
					$header3 = 'จำนวนครั้ง';
				}
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $header3);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
				$currentstrr = ++$currentstrr;
			}
		}	
	}
	$currentstrr = 'A';
	$line = 9;
	
	if (! isset($RESULT)) {
	}
	else {
		foreach ($RESULT as $date => $mresult) {
			$currentstrr = 'A';
			$startstrr = $currentstrr."".$line;
			
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $date);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
				
			$currentstrr = ++$currentstrr;
			
			foreach ($EVENT as $eventid => $nresult) {
				if ($eventid == 4) {
						if ( ! isset($mresult[$eventid][1]['COUNT'])) {
						   $count = 0;
						}
						else {
							$count = $mresult[$eventid][1]['COUNT'];
						}
						if ( ! isset($mresult[$eventid][1]['SUMMARY'])) {
						   $summary = 0;
						}
						else {
							$summary = $mresult[$eventid][1]['SUMMARY'];
						}
					$startstrr = $currentstrr."".$line;
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($count,0));
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
					$currentstrr = ++$currentstrr;
					
					$startstrr = $currentstrr."".$line;
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($summary,0));
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
					$currentstrr = ++$currentstrr;
					
					$totaldata = $summary/1024/1024;
					$startstrr = $currentstrr."".$line;
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($totaldata,2));
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
					$currentstrr = ++$currentstrr;			
				}
				else {
					foreach ($EVENT_SUB as $subid => $presult) {
						if ( ! isset($mresult[$eventid][$subid]['COUNT'])) {
						   $count = 0;
						}
						else {
							$count = $mresult[$eventid][$subid]['COUNT'];
						}
						if ( ! isset($mresult[$eventid][$subid]['SUMMARY'])) {
						   $summary = 0;
						}
						else {
							$summary = $mresult[$eventid][$subid]['SUMMARY'];
						}
						$startstrr = $currentstrr."".$line;
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($count,0));
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
						$currentstrr = ++$currentstrr;
						
						$startstrr = $currentstrr."".$line;
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($summary,0));
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
						$currentstrr = ++$currentstrr;
					}			
				}
			}
			$line = ++$line;
		}
	}

	$currentstrr = 'A';

	$startstrr = $currentstrr ."".$line;
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'Grand Total');
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
	$currentstrr = ++$currentstrr;

	foreach ($EVENT as $eventid => $nresult) {
		if ($eventid == 4) {
			if ( ! isset($SUM[$eventid][1]['COUNT'])) {
			   $count = 0;
			}
			else {
				$count = $SUM[$eventid][1]['COUNT'];
			}
			if ( ! isset($SUM[$eventid][1]['SUMMARY'])) {
			   $summary = 0;
			}
			else {
				$summary = $SUM[$eventid][1]['SUMMARY'];
			}
			$startstrr = $currentstrr."".$line;
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($count,0));
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
			$currentstrr = ++$currentstrr;
			
			$startstrr = $currentstrr."".$line;
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($summary,0));
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
			$currentstrr = ++$currentstrr;
			
			$totaldata = $summary/1024/1024;
			$startstrr = $currentstrr."".$line;
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($totaldata,2));
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
			
			$endoff = $currentstrr;
			
			$currentstrr = ++$currentstrr;			
		}
		else {
			foreach ($EVENT_SUB as $subid => $presult) {
				$endoff = $currentstrr;
				if ( ! isset($SUM[$eventid][$subid]['COUNT'])) {
				   $count = 0;
				}
				else {
					$count = $SUM[$eventid][$subid]['COUNT'];
				}
				if ( ! isset($SUM[$eventid][$subid]['SUMMARY'])) {
				   $summary = 0;
				}
				else {
					$summary = $SUM[$eventid][$subid]['SUMMARY'];
				}
				$startstrr = $currentstrr."".$line;
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($count,0));
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
				$currentstrr = ++$currentstrr;
				
				$startstrr = $currentstrr."".$line;
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($summary,0));
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
				$currentstrr = ++$currentstrr;
			}		
		}
	}

	$endoffset = $endoff."".$line;

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
