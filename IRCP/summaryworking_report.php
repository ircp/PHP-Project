<?	
	try {
		
		$currentyear = date('Y');
		$currentdate = date("d");
		$currentmonth = date("m");
		
		$endyear = 0;
		$currentday = $currentyear."-".$currentmonth."-".$currentdate;
		
		if (($currentmonth == 1 || $currentmonth == '01') && $currentdate <= 7) {
			$currentyear = $currentyear -1 ;
		}
		
		$date = date_create($currentday);
		date_sub($date,date_interval_create_from_date_string("7 day"));
		$sdate =  date_format($date,"Y-m-d");
		$startdate =  $sdate." 00:00:00";
		$date = date_create($currentday);
		date_sub($date,date_interval_create_from_date_string("3 day"));
		$edate =  date_format($date,"Y-m-d");
		$enddate =  $edate." 00:00:00";
		
		$sql = "	SELECT WD.EMPLOYEE_ID, WD.WORKING_DAY, EM.TITLE, EM.FIRST_NAME, EM.LAST_NAME, EM.WORKING_TIME, DE.DEPARTMENT_NAME,
					WD.WORKING_IN, WD.WORKING_OUT, WD.INTERVAL_IN, WD.INTERVAL_OUT, EM.DEPARTMENT_ID
					FROM WORKING_DAY WD
					LEFT JOIN EMPLOYEE EM ON EM.EMPLOYEE_ID = WD.EMPLOYEE_ID AND SYSDATE() BETWEEN EM.EFFECTIVE_START_DATE AND EM.EFFECTIVE_END_DATE 
					AND EM.EMPLOYEE_STATUS = 1 
					LEFT JOIN DEPARTMENT DE ON DE.DEPARTMENT_ID = EM.DEPARTMENT_ID
					WHERE SYSDATE() BETWEEN WD.EFFECTIVE_START_DATE AND WD.EFFECTIVE_END_DATE
					AND WD.WORKING_STATUS IN (1,4) AND  EM.WORKING_TIME = 1
					AND EM.EMPLOYEE_ID <> 1 AND WD.WORKING_DAY <= ?
					ORDER BY DE.DEPARTMENT_NAME, EM.FIRST_NAME, EM.LAST_NAME, WD.WORKING_DAY";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($enddate));		
		
		UNSET ($WORKING_LIST);
		UNSET ($DEPARTMENT);
		UNSET ($EMPLOYEE);
		
		$i = 0;
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$DEPARTMENT[$arr['DEPARTMENT_ID']]['NAME'] = $arr['DEPARTMENT_NAME'];
			$EMPLOYEE[$arr['DEPARTMENT_ID']][$arr['EMPLOYEE_ID']]['NAME'] = $arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME'];
			$WORKING_LIST[$arr['DEPARTMENT_ID']][$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['WORKING_IN'] = $arr['WORKING_IN'];
			$WORKING_LIST[$arr['DEPARTMENT_ID']][$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['WORKING_OUT'] = $arr['WORKING_OUT'];
			$WORKING_LIST[$arr['DEPARTMENT_ID']][$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['INTERVAL_IN'] = $arr['INTERVAL_IN'];
			$WORKING_LIST[$arr['DEPARTMENT_ID']][$arr['EMPLOYEE_ID']][$arr['WORKING_DAY']]['INTERVAL_OUT'] = $arr['INTERVAL_OUT'];
		}		
		
		if (isset($DEPARTMENT)) {
			$runningdate = date("YmdHis");
					
			$directory = dirname(__FILE__)."/FILES/";
			if (!file_exists($directory)) {
				mkdir($directory);
			}
			$directory = $directory.'REPORT/';
			if (!file_exists($directory)) {
				mkdir($directory);
			}

			$file_name = "WEEKLY_WORKING_REPORT_".$edate."_".$runningdate.".xls";
			$filename = $directory."WEEKLY_WORKING_REPORT_".$edate."_".$runningdate.".xls";
				
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
			
			$i = 0;
			
			foreach ($DEPARTMENT as $departmentid => $dresult) {
				if ($i >= 1) {
					$objPHPExcel->createSheet($i);
					$objPHPExcel->setActiveSheetIndex($i);
				}
				$objPHPExcel->getDefaultStyle()->applyFromArray($styleInfo);
				
				$currentstrr = 'A';
				
				$line = 4;
				$startstrr = $currentstrr."".$line;
				$endstrr = $currentstrr."".$line;
				$startoffset = $startstrr;
				$endoffset = $endstrr;
				$dstrr = $startstrr.":".$endstrr;				
				
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'ชื่อ-นามสกุล');
				$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
				$currentstrr = ++$currentstrr;		

				$line = 4;
				$startstrr = $currentstrr."".$line;
				$endstrr = $currentstrr."".$line;
				$endoffset = $endstrr;
				$dstrr = $startstrr.":".$endstrr;
				
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'วันที่');
				$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
				$currentstrr = ++$currentstrr;		

				$line = 4;
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

				$line = 4;
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

				$line = 4;
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
				
				$line = ++$line;
				foreach ($EMPLOYEE[$departmentid] as $employeeid => $eresult) {
					foreach ($WORKING_LIST[$departmentid][$employeeid] as $workingdate => $wresult) {
						$currentstrr = 'A';
						
						$startstrr = $currentstrr."".$line;
						$endstrr = $currentstrr."".$line;
						$endoffset = $endstrr;
						$dstrr = $startstrr.":".$endstrr;		

						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $eresult['NAME']);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
						$currentstrr = ++$currentstrr;		
						
						$startstrr = $currentstrr."".$line;
						$endstrr = $currentstrr."".$line;
						$endoffset = $endstrr;
						$dstrr = $startstrr.":".$endstrr;		
						
						$wdate = substr($workingdate,8,2)."/".substr($workingdate,5,2)."/".((int)substr($workingdate,0,4) + 543);
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $wdate);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
						$currentstrr = ++$currentstrr;	
						
						$working_in = '';
						$working_out = '';
						$timew = '';
						if ($wresult['WORKING_IN'] != '') {
							$working_in = substr($wresult['WORKING_IN'],11,5);
						}
						if ($wresult['WORKING_OUT'] != '') {
							$working_out = substr($wresult['WORKING_OUT'],11,5);	
						}
						
						if ($working_in != '') {
							$timew = $working_in." - ".$working_out;
						}

						$startstrr = $currentstrr."".$line;
						$endstrr = $currentstrr."".$line;
						$endoffset = $endstrr;
						$dstrr = $startstrr.":".$endstrr;		
						
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $timew);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
						$currentstrr = ++$currentstrr;	
						
						$startstrr = $currentstrr."".$line;
						$endstrr = $currentstrr."".$line;
						$endoffset = $endstrr;
						$dstrr = $startstrr.":".$endstrr;		
						
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $wresult['INTERVAL_IN']);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
						$currentstrr = ++$currentstrr;	

						$startstrr = $currentstrr."".$line;
						$endstrr = $currentstrr."".$line;
						$endoffset = $endstrr;
						$dstrr = $startstrr.":".$endstrr;		
						$endposition = $currentstrr;
						
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $wresult['INTERVAL_OUT']);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
						$currentstrr = ++$currentstrr;	
						
						$line = ++$line;
					}
				
				}
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth("30");
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth("21");
				$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth("21");
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
				
				$header = "รายงานสรุปเวลาเข้า-ออกประจำสัปดาห์ของพนักงาน";
				
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $header);
				$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));		
					
				$objPHPExcel->getActiveSheet()->setTitle($dresult['NAME']);	
				$i = $i + 1;
			}
			
			$objPHPExcel->setActiveSheetIndex(0);
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save($filename);				
		}
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	
?>