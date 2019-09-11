<?
	require_once('connection.php');
	
	$network = 1;
	$mvno = 11;
	$selectyear = '2017';
	$selectmonth = '05';
	
	require_once('util.php');
	
	$monthdisplay = monthname($selectmonth);
	
	$objectid = 1;
	$processtype = 1;
	$processstatus = 1;
	$processid = 1;
	
	$totalamount = 0;
	$totalsummary = 0;
	
//	$processid = updateProcessLog($objectid, $processtype, $processstatus, $processid);
	
	try {		
		$sql = "SELECT MVNO_CODE, MVNO_NAME FROM MVNO";
		$stmt = $conn->prepare($sql);
		$stmt->execute();
			
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$NAME['MVNO'][$arr['MVNO_CODE']]['NAME'] = $arr['MVNO_NAME'];
		}
		
		$sql = "	SELECT REFERENCE_CODE, LABEL FROM REFERENCE_CODE 
					WHERE REFERENCE_TYPE_ID = 2 AND REFERENCE_CODE IN (1,2) AND VALID_IND_CODE = 1
					ORDER BY REFERENCE_CODE";
		$stmt = $conn->prepare($sql);
		$stmt->execute();
			
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$EVENT[$arr['REFERENCE_CODE']]['NAME'] = $arr['LABEL'];
		}
		
		$sql = "	SELECT REFERENCE_CODE, LABEL FROM REFERENCE_CODE 
					WHERE REFERENCE_TYPE_ID = 3 AND VALID_IND_CODE = 1
					ORDER BY REFERENCE_CODE";
		$stmt = $conn->prepare($sql);
		$stmt->execute();
			
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$NETWORK[$arr['REFERENCE_CODE']]['NAME'] = $arr['LABEL'];
		}
		
		$sql = "	SELECT REFERENCE_CODE, LABEL FROM REFERENCE_CODE WHERE REFERENCE_TYPE_ID = 4 AND VALID_IND_CODE = 1 AND REFERENCE_CODE IN (1,2) 
					ORDER BY REFERENCE_CODE";
		$stmt = $conn->prepare($sql);
		$stmt->execute();
			
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$EVENT_SUB[$arr['REFERENCE_CODE']]['NAME'] = $arr['LABEL'];
		}
		
		$sql = "	SELECT REFERENCE_CODE, LABEL FROM REFERENCE_CODE WHERE REFERENCE_TYPE_ID = 5 AND VALID_IND_CODE = 1
					ORDER BY REFERENCE_CODE";
		$stmt = $conn->prepare($sql);
		$stmt->execute();
			
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$SUB[$arr['REFERENCE_CODE']]['NAME'] = $arr['LABEL'];
		}
		
		$sql = "	SELECT SUM(DURATION_MINUTE) SUM_DURATION, C_EVENT_TYPE, C_EVENT_SUB_TYPE, C_SUB_TYPE, C_COUNTRY, C_RATE
					FROM MD_REPORT.CDR
					WHERE C_ROAMING_NETWORK = ? AND MVNO_CODE = ? AND SUBSTRING(DATE_START_CHARGING, 1, 4) = ? AND SUBSTRING(DATE_START_CHARGING, 6, 2) = ? AND C_EVENT_TYPE IN (1,2) AND C_EVENT_SUB_TYPE = 2 
					GROUP BY C_COUNTRY, C_EVENT_TYPE, C_SUB_TYPE, C_RATE
					ORDER BY C_COUNTRY, C_EVENT_TYPE, C_EVENT_SUB_TYPE, C_SUB_TYPE";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($network, $mvno, $selectyear, $selectmonth));
		
		$n = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$n = $n + 1;
			
			$sumduration = $arr['SUM_DURATION'];
			$EVENT_TYPE = $arr['C_EVENT_TYPE'];
			$EVENT_SUB_TYPE = $arr['C_EVENT_SUB_TYPE'];
			$SUB_TYPE = $arr['C_SUB_TYPE'];
			$COUNTRY = $arr['C_COUNTRY'];
			$rate = $arr['C_RATE'];
			
			$amount = $sumduration*$rate;
			
			if (! isset($RESULT[$COUNTRY][$EVENT_TYPE][$SUB_TYPE]['SUMMARY'])) {
				$RESULT[$COUNTRY][$EVENT_TYPE][$SUB_TYPE]['SUMMARY'] = $sumduration;		
			}
			else {
				$RESULT[$COUNTRY][$EVENT_TYPE][$SUB_TYPE]['SUMMARY'] = $sumduration + $RESULT[$COUNTRY][$EVENT_TYPE][$SUB_TYPE]['SUMMARY'];		
			}
			
			if (! isset($RESULT[$COUNTRY][$EVENT_TYPE][$SUB_TYPE]['AMOUNT'])) {
				$RESULT[$COUNTRY][$EVENT_TYPE][$SUB_TYPE]['AMOUNT'] = $amount;		
			}
			else {
				$RESULT[$COUNTRY][$EVENT_TYPE][$SUB_TYPE]['AMOUNT'] = $amount + $RESULT[$COUNTRY][$EVENT_TYPE][$SUB_TYPE]['AMOUNT'];		
			}

			if (! isset($SUM_COUNTRY[$COUNTRY]['SUMMARY'])) {
				$SUM_COUNTRY[$COUNTRY]['SUMMARY'] = $sumduration;		
			}
			else {
				$SUM_COUNTRY[$COUNTRY]['SUMMARY'] = $sumduration + $SUM_COUNTRY[$COUNTRY]['SUMMARY'];		
			}
			
			if (! isset($SUM_COUNTRY[$COUNTRY]['AMOUNT'])) {
				$SUM_COUNTRY[$COUNTRY]['AMOUNT'] = $amount;		
			}
			else {
				$SUM_COUNTRY[$COUNTRY]['AMOUNT'] = $amount + $SUM_COUNTRY[$COUNTRY]['AMOUNT'];		
			}		
			if (! isset($SUM_EVENT[$EVENT_TYPE][$SUB_TYPE]['SUMMARY'])) {
				$SUM_EVENT[$EVENT_TYPE][$SUB_TYPE]['SUMMARY'] = $sumduration;		
			}
			else {
				$SUM_EVENT[$EVENT_TYPE][$SUB_TYPE]['SUMMARY'] = $sumduration + $SUM_EVENT[$EVENT_TYPE][$SUB_TYPE]['SUMMARY'];		
			}
			
			if (! isset($SUM_EVENT[$EVENT_TYPE][$SUB_TYPE]['AMOUNT'])) {
				$SUM_EVENT[$EVENT_TYPE][$SUB_TYPE]['AMOUNT'] = $amount;		
			}
			else {
				$SUM_EVENT[$EVENT_TYPE][$SUB_TYPE]['AMOUNT'] = $amount + $SUM_EVENT[$EVENT_TYPE][$SUB_TYPE]['AMOUNT'];		
			}
		}		


	$header1 = "รายละเอียดการใช้บริการโทรทางไกลต่างประเทศ 007 และ 008 บนโครงข่าย ".$NETWORK[$network]['NAME']." ประจำเดือน ".$monthdisplay." ".$selectyear;

	$header2 = "บริษัท ".$NAME['MVNO'][$mvno]['NAME'];
	$title = "MVNO_Voice_inter";
	$filename = "MVNO_Voice_inter_".$NAME['MVNO'][$mvno]['NAME']."_".$selectyear.$selectmonth.".xls";
		

	
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
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "ชื่อประเทศ");
	$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleHeader);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	

	
	
	$currentstrr = 'B';
	$line = 5;
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

	$line = 5;
	$startstrr = $currentstrr."".$line;
	$line = $line + 2;
	$endstrr =  $currentstrr."".$line;
	$dstrr = $startstrr.":".$endstrr;

	// Table Header 
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "รวมจำนวนการใช้ (นาที)");
	$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleHeader);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
	$currentstrr = ++$currentstrr;

	$line = 5;
	$startstrr = $currentstrr."".$line;
	$line = $line + 2;
	$endstrr =  $currentstrr."".$line;
	$dstrr = $startstrr.":".$endstrr;

	// Table Header 
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "รวมจำนวนเงิน (บาท)");
	$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleHeader);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
	
	$laststr = $currentstrr;
	
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
	 
	$currentstrr = 'B';
	$line = 6;
	foreach ($EVENT as $eventid => $mresult) {	
		foreach ($SUB as $subid => $sresult) {
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
	
	$currentstrr = 'B';
	$line = 7;
	foreach ($EVENT as $eventid => $mresult) {	
		foreach ($SUB as $subid => $sresult) {
			$startstrr = $currentstrr."".$line;
			$dstrr = $startstrr.":".$endstrr;
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'จำนวนนาที');
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));		
				
			$currentstrr = ++$currentstrr;
			$startstrr = $currentstrr."".$line;
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'จำนวนเงิน (บาท)');
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
			$currentstrr = ++$currentstrr;
		}		
	}
	
	$currentstrr = 'A';
	$line = 8;
	foreach ($RESULT as $country => $mresult) {
		$currentstrr = 'A';
		$startstrr = $currentstrr."".$line;
		
		$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $country);
		$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
		$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
			array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,));
			
		$currentstrr = ++$currentstrr;
		
		foreach ($EVENT as $eventid => $nresult) {
			foreach ($SUB as $subid => $presult) {
				if ( ! isset($mresult[$eventid][$subid]['SUMMARY'])) {
				   $count = 0;
				}
				else {
					$count = $mresult[$eventid][$subid]['SUMMARY'];
				}
				if ( ! isset($mresult[$eventid][$subid]['AMOUNT'])) {
				   $summary = 0;
				}
				else {
					$summary = $mresult[$eventid][$subid]['AMOUNT'];
				}
				$startstrr = $currentstrr."".$line;
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($count,0));
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
				$currentstrr = ++$currentstrr;
				
				$startstrr = $currentstrr."".$line;
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($summary,2));
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
				$currentstrr = ++$currentstrr;
			}			
		}
		$startstrr = $currentstrr."".$line;
		$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($SUM_COUNTRY[$country]['SUMMARY'],0));
		$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
			array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
		$currentstrr = ++$currentstrr;
		
		$startstrr = $currentstrr."".$line;
		$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($SUM_COUNTRY[$country]['AMOUNT'],2));
		$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
			array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
		$currentstrr = ++$currentstrr;		
		$line = ++$line;
		
		$totalsummary = $totalsummary + $SUM_COUNTRY[$country]['SUMMARY'];
		$totalamount = $totalamount + $SUM_COUNTRY[$country]['AMOUNT'];
	}	

	$currentstrr = 'A';

	$startstrr = $currentstrr ."".$line;
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'Total');
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
	$currentstrr = ++$currentstrr;

	foreach ($EVENT as $eventid => $nresult) {
		foreach ($SUB as $subid => $presult) {
			$endoff = $currentstrr;
			if ( ! isset($SUM_EVENT[$eventid][$subid]['SUMMARY'])) {
			   $count = 0;
			}
			else {
				$count = $SUM_EVENT[$eventid][$subid]['SUMMARY'];
			}
			if ( ! isset($SUM_EVENT[$eventid][$subid]['AMOUNT'])) {
			   $summary = 0;
			}
			else {
				$summary = $SUM_EVENT[$eventid][$subid]['AMOUNT'];
			}
			$startstrr = $currentstrr."".$line;
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($count,0));
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
			$currentstrr = ++$currentstrr;
			
			$startstrr = $currentstrr."".$line;
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($summary,2));
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
			$currentstrr = ++$currentstrr;
		}			
	}

	$startstrr = $currentstrr."".$line;
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($totalsummary,0));
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	$currentstrr = ++$currentstrr;
	
	$startstrr = $currentstrr."".$line;
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($totalamount,2));
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
	
	$endoff = $currentstrr;
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
