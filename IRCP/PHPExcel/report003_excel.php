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
					WHERE REFERENCE_TYPE_ID = 3 AND VALID_IND_CODE = 1
					ORDER BY REFERENCE_CODE";
		$stmt = $conn->prepare($sql);
		$stmt->execute();
			
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$NETWORK[$arr['REFERENCE_CODE']]['NAME'] = $arr['LABEL'];
		}
		
		$sql = "	SELECT SUM(TOTAL_VOLUME) TOTAL_VOLUME, C_ROAMING_NETWORK, C_COUNTRY
					FROM MD_REPORT.CDR
					WHERE C_ROAMING_NETWORK = ? AND MVNO_CODE = ? AND SUBSTRING(DATE_START_CHARGING, 1, 4) = ? AND SUBSTRING(DATE_START_CHARGING, 6, 2) = ? AND C_EVENT_TYPE IN (3,13) AND C_EVENT_SUB_TYPE = 2 
					GROUP BY C_ROAMING_NETWORK, C_COUNTRY
					ORDER BY C_COUNTRY, C_ROAMING_NETWORK ";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($network, $mvno, $selectyear, $selectmonth));
		
		$n = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$n = $n + 1;
			
			$volume = $arr['TOTAL_VOLUME'];
			$ROAMING_NETWORK = $arr['C_ROAMING_NETWORK'];
			$COUNTRY = $arr['C_COUNTRY'];
			
			if (! isset($RESULT[$COUNTRY][$ROAMING_NETWORK]['SUMMARY'])) {
				$RESULT[$COUNTRY][$ROAMING_NETWORK]['SUMMARY'] = $volume;		
			}
			else {
				$RESULT[$COUNTRY][$ROAMING_NETWORK]['SUMMARY'] = $volume + $RESULT[$COUNTRY][$ROAMING_NETWORK]['SUMMARY'];		
			}
		
			if (! isset($SUM_COUNTRY[$COUNTRY]['SUMMARY'])) {
				$SUM_COUNTRY[$COUNTRY]['SUMMARY'] = $volume;		
			}
			else {
				$SUM_COUNTRY[$COUNTRY]['SUMMARY'] = $volume + $SUM_COUNTRY[$COUNTRY]['SUMMARY'];		
			}
			
			if (! isset($SUM_ROAMING[$ROAMING_NETWORK]['SUMMARY'])) {
				$SUM_ROAMING[$ROAMING_NETWORK]['SUMMARY'] = $volume;		
			}
			else {
				$$SUM_ROAMING[$ROAMING_NETWORK]['SUMMARY'] = $volume + $SUM_ROAMING[$ROAMING_NETWORK]['SUMMARY'];		
			}		
		}		

	
	$header1 = "รายละเอียดการใช้บริการ International SMS ในโครงข่าย ".$NETWORK[$network]['NAME']." ประจำเดือน ".$monthdisplay." ".$selectyear;
	$header2 = "บริษัท ".$NAME['MVNO'][$mvno]['NAME'];
	$title = "MVNO_SMS_inter";
	$filename = "MVNO_SMS_inter_".$NAME['MVNO'][$mvno]['NAME']."_".$selectyear.$selectmonth.".xls";
		
	
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
	$line = $line + 1;
	$endstrr =  $currentstrr."".$line;
	$dstrr = $startstrr.":".$endstrr;

	// Table Header 
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "ชื่อประเทศ");
	$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	

	$currentstrr = 'B';
	$line = 6;
	foreach ($NETWORK as $networkid => $mresult) {
		
		$startstrr = $currentstrr."".$line;
		$endst = $currentstrr;
		
		$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $mresult['NAME']);
		$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
		$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
		$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
			array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
		
		$currentstrr = ++$currentstrr;
	}

	$line = 5;
	$startstrr = $currentstrr."".$line;
	$line = $line + 1;
	$endstrr =  $currentstrr."".$line;
	$dstrr = $startstrr.":".$endstrr;

	// Table Header 
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "รวมจำนวนการใช้ (ข้อความ)");
	$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
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
	
	$line = 5;
	$currentstrr = 'B';
	$startstrr = $currentstrr."".$line;
	$endstrr =  $endst."".$line;
	$dstrr = $startstrr.":".$endstrr;

	// Table Header 
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, "SMS (ข้อความ)");
	$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
		
	
	$currentstrr = 'A';
	$line = 7;
	foreach ($RESULT as $country => $mresult) {
		$currentstrr = 'A';
		$startstrr = $currentstrr."".$line;
		
		$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $country);
		$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
		$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
			array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,));
			
		$currentstrr = ++$currentstrr;
		
		foreach ($NETWORK as $networkid => $nresult) {
			if ( ! isset($mresult[$networkid]['SUMMARY'])) {
			   $count = 0;
			}
			else {
				$count = $mresult[$networkid]['SUMMARY'];
			}

			$startstrr = $currentstrr."".$line;
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($count,0));
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
			$currentstrr = ++$currentstrr;

		}
		
		$startstrr = $currentstrr."".$line;
		$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($SUM_COUNTRY[$country]['SUMMARY'],0));
		$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
			array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
		$currentstrr = ++$currentstrr;	
		$line = ++$line;
		
		$totalsummary = $totalsummary + $SUM_COUNTRY[$country]['SUMMARY'];
	}	

	$currentstrr = 'A';

	$startstrr = $currentstrr ."".$line;
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'Total');
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
	$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
		array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
	$currentstrr = ++$currentstrr;

	foreach ($NETWORK as $eventid => $nresult) {
		$endoff = $currentstrr;
		if ( ! isset($SUM_ROAMING[$eventid]['SUMMARY'])) {
		   $count = 0;
		}
		else {
			$count = $SUM_ROAMING[$eventid]['SUMMARY'];
		}

		$startstrr = $currentstrr."".$line;
		$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($count,0));
		$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
		$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
			array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));
		$currentstrr = ++$currentstrr;
	}

	$startstrr = $currentstrr."".$line;
	$objPHPExcel->getActiveSheet()->setCellValue($startstrr, number_format($totalsummary,0));
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
