<?	
	try {
		
		$currentyear = date('Y');
		$currentmonth = (int) date('m');
		
		if ($currentmonth == 1) {
			$currentmonth = 12;
			$currentyear = (int) date("Y") - 1;
		}
		else {
			$currentmonth = (int)date('m') -1;
		}
		
		include ("util.php");
		$monthname = monthname($currentmonth);
		$thaiyear = (int) $currentyear + 543;
		
		$sql = "	SELECT WD.EMPLOYEE_ID, WD.WORKING_DAY, WD.WORKING_STATUS, WD.WORKING_IN, WD.WORKING_OUT, WD.DESCRIPTION, WD.IN_OUT_ID, WD.LEAVE_ID, 
						RC1.LABEL WORKINGSTATUS, LP.LEAVE_NAME, WD.INTERVAL_IN, WD.INTERVAL_OUT,
						EMPLOYEE.TITLE, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, EMPLOYEE.FIRST_NAME_EN, EMPLOYEE.LAST_NAME_EN
						FROM WORKING_DAY WD
						LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 14 AND RC1.REFERENCE_CODE = WD.WORKING_STATUS
						LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE AND ELH.LEAVE_STATUS IN (2,3) AND ELH.LEAVE_ID = WD.LEAVE_ID
						LEFT JOIN LEAVE_POLICY LP ON LP.LEAVE_TYPE_ID = ELH.LEAVE_TYPE_ID
						LEFT JOIN EMPLOYEE EMPLOYEE ON WD.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID AND SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
						WHERE SYSDATE() BETWEEN WD.EFFECTIVE_START_DATE AND WD.EFFECTIVE_END_DATE
						AND YEAR(WD.WORKING_DAY) = ? AND MONTH(WD.WORKING_DAY) = ?
						AND EMPLOYEE.WORKING_TIME = 1
						ORDER BY EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, WD.WORKING_DAY";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array($currentyear, $currentmonth));
		
		UNSET ($SUMMARY);
		UNSET ($EMPLOYEE);
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$SUMMARY[$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['WORKING_STATUS'] = $arr['WORKING_STATUS'];
			$SUMMARY[$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['WORKINGSTATUS'] = $arr['WORKINGSTATUS'];
			$SUMMARY[$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['WORKING_IN'] = substr($arr['WORKING_IN'],11,5);
			$SUMMARY[$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['WORKING_OUT'] =substr($arr['WORKING_OUT'],11,5);
			$SUMMARY[$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['DESCRIPTION'] = $arr['DESCRIPTION'];
			$SUMMARY[$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['IN_OUT_ID'] = $arr['IN_OUT_ID'];
			$SUMMARY[$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['LEAVE_ID'] = $arr['LEAVE_ID'];
			$SUMMARY[$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['INTERVAL_IN'] = $arr['INTERVAL_IN'];
			$SUMMARY[$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['INTERVAL_OUT'] = $arr['INTERVAL_OUT'];
			$SUMMARY[$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['WORKING_DAY'] = substr($arr['WORKING_DAY'],8,2)."/".substr($arr['WORKING_DAY'],5,2)."/".((int)substr($arr['WORKING_DAY'],0,4) + 543);
			$EMPLOYEE[$arr['EMPLOYEE_ID']]['TITLE'] = $arr['FIRST_NAME']."_".$arr['LAST_NAME'];
			$EMPLOYEE[$arr['EMPLOYEE_ID']]['NAME'] = $arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME'];
			
			if ($arr['IN_OUT_ID'] == '' && $arr['LEAVE_ID'] == '') {
				$SUMMARY[$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['WORKING_TIME'] = '';
			}
			else {
				if ($arr['IN_OUT_ID'] != '') {
					$SUMMARY[$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['WORKING_TIME'] = substr($arr['WORKING_IN'],11,5)." - ".substr($arr['WORKING_OUT'],11,5);
				}
				if ($arr['LEAVE_ID'] != '') {
					$SUMMARY[$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['WORKING_TIME'] = $arr['LEAVE_NAME'];
				}
			}
		}

		$runningdate = date("YmdHis");
			
		$directory = dirname(__FILE__)."/FILES/";
		if (!file_exists($directory)) {
			mkdir($directory);
		}
		$directory = $directory.'REPORT/';
		if (!file_exists($directory)) {
			mkdir($directory);
		}		
		
		$cmonth = str_pad($currentmonth, 2, '0', STR_PAD_LEFT);
		
		$file_name = "MONTHLY_WORKINGTIME_REPORT_".$currentyear.$cmonth."_".$runningdate.".xls";
		$filename = $directory."MONTHLY_WORKINGTIME_REPORT_".$currentyear.$cmonth."_".$runningdate.".xls";

		require_once dirname(__FILE__) . '/PHPExcel.php';

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
		
		if (isset($SUMMARY)) {
			$i = 0;
			foreach ($EMPLOYEE as $employeeid => $dresult) {
				if ($i >= 1) {
					$objPHPExcel->createSheet($i);
					$objPHPExcel->setActiveSheetIndex($i);
				}
				$objPHPExcel->getDefaultStyle()->applyFromArray($styleInfo);	
				
				$line = 4;
				$currentstrr = 'A';
				$startstrr = $currentstrr."".$line;
				$endstrr = $currentstrr."".$line;
				$startoffset = $startstrr;
				$endoffset = $endstrr;
				$dstrr = $startstrr.":".$endstrr;	
				
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'วันที่');
				$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
				$currentstrr = ++$currentstrr;	

				$startstrr = $currentstrr."".$line;
				$endstrr = $currentstrr."".$line;
				$endoffset = $endstrr;
				$dstrr = $startstrr.":".$endstrr;
				
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'เวลาเข้า - ออก');
				$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
				$currentstrr = ++$currentstrr;	

				$startstrr = $currentstrr."".$line;
				$endstrr = $currentstrr."".$line;
				$endoffset = $endstrr;
				$dstrr = $startstrr.":".$endstrr;
				
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'จำนวนเวลาเข้าสาย (นาที)');
				$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
				$currentstrr = ++$currentstrr;		

				$startstrr = $currentstrr."".$line;
				$endstrr = $currentstrr."".$line;
				$endoffset = $endstrr;
				$dstrr = $startstrr.":".$endstrr;
				
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'จำนวนเวลาออกเร็ว (นาที)');
				$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
				$currentstrr = ++$currentstrr;			

				$startstrr = $currentstrr."".$line;
				$endstrr = $currentstrr."".$line;
				$endoffset = $endstrr;
				$dstrr = $startstrr.":".$endstrr;
				
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'รายละเอียด');
				$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
				$currentstrr = ++$currentstrr;		

				$startstrr = $currentstrr."".$line;
				$endstrr = $currentstrr."".$line;
				$endoffset = $endstrr;
				$dstrr = $startstrr.":".$endstrr;
				
				$endposition = $currentstrr;
				
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'สถานะ');
				$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
				$currentstrr = ++$currentstrr;		
				
				if (isset($SUMMARY[$employeeid])) {			
					foreach ($SUMMARY[$employeeid] as $workingdate => $result) {
						$line = $line + 1;
						$currentstrr = 'A';

						$startstrr = $currentstrr."".$line;
						$endstrr = $currentstrr."".$line;
						$endoffset = $endstrr;
						$dstrr = $startstrr.":".$endstrr;
						
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $result['WORKING_DAY']);
						$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
						$currentstrr = ++$currentstrr;	
						
						if ($result['LEAVE_ID'] != '') {
							$startstrr = $currentstrr."".$line;
							$currentstrr = ++$currentstrr;
							$currentstrr = ++$currentstrr;
							$currentstrr = ++$currentstrr;
							$endstrr = $currentstrr."".$line;
							$endoffset = $endstrr;
							$dstrr = $startstrr.":".$endstrr;
							
							$dvalue = $result['WORKING_TIME'];
							$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $dvalue);
							$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
							$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
							$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
								array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
							$currentstrr = ++$currentstrr;
						}
						else {
							$startstrr = $currentstrr."".$line;
							$endstrr = $currentstrr."".$line;
							$endoffset = $endstrr;
							$dstrr = $startstrr.":".$endstrr;
							
							$dvalue = $result['WORKING_TIME'];
							$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $dvalue);
							$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
							$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
							$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
								array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
							$currentstrr = ++$currentstrr;
							
							$startstrr = $currentstrr."".$line;
							$endstrr = $currentstrr."".$line;
							$endoffset = $endstrr;
							$dstrr = $startstrr.":".$endstrr;
							
							$dvalue = $result['INTERVAL_IN'];
							$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $dvalue);
							$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
							$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
							$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
								array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
							$currentstrr = ++$currentstrr;
							
							$startstrr = $currentstrr."".$line;
							$endstrr = $currentstrr."".$line;
							$endoffset = $endstrr;
							$dstrr = $startstrr.":".$endstrr;
							
							$dvalue = $result['INTERVAL_OUT'];
							$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $dvalue);
							$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
							$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
							$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
								array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
							$currentstrr = ++$currentstrr;
							
							$startstrr = $currentstrr."".$line;
							$endstrr = $currentstrr."".$line;
							$endoffset = $endstrr;
							$dstrr = $startstrr.":".$endstrr;
							
							$dvalue = $result['DESCRIPTION'];
							$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $dvalue);
							$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
							$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
							$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
								array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
							$currentstrr = ++$currentstrr;
						}
						$startstrr = $currentstrr."".$line;
						$endstrr = $currentstrr."".$line;
						$endoffset = $endstrr;
						$dstrr = $startstrr.":".$endstrr;
						
						$dvalue = $result['WORKINGSTATUS'];
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $dvalue);
						$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
						$currentstrr = ++$currentstrr;
					}		
				}
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth("30");
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth("30");
				$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth("50");

				$offset = $startoffset.":".$endoffset;
					
				 $objPHPExcel->getActiveSheet()->getStyle($offset)->applyFromArray(array(
						'borders' => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN
							)
						)
				)); 

				$line = 2;
				$startposition = 'A';

				$startstrr = $startposition."".$line;
				$endstrr = $endposition."".$line;
				$dstrr = $startstrr.":".$endstrr;		
				
				$header = "รายละเอียดเวลาเข้า - ออก ประจำเดือน ".$monthname." ".$thaiyear." ของ ".$dresult['NAME'];
				
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $header);
				$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	

				$objPHPExcel->getActiveSheet()->setTitle($dresult['TITLE']);	
				
				$i = $i + 1;
			}
		}

		$objPHPExcel->setActiveSheetIndex(0);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save($filename);				
	}
	catch (exception $e) {
//		header("Status: 500");
//		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	
?>