<?	
	try {
		require ('connection.php');
		
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
		date_sub($date,date_interval_create_from_date_string("1 day"));
		$edate =  date_format($date,"Y-m-d");
		$enddate =  $edate." 00:00:00";
		
		$sql = "	SELECT WD.EMPLOYEE_ID, WD.WORKING_DAY, WD.WORKING_STATUS, WD.WORKING_IN, WD.WORKING_OUT, WD.DESCRIPTION, WD.IN_OUT_ID, WD.LEAVE_ID, 
					RC1.LABEL WORKINGSTATUS, WD.INTERVAL_IN, WD.INTERVAL_OUT, DEPARTMENT.DEPARTMENT_NAME, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, EMPLOYEE.TITLE, DEPARTMENT.DEPARTMENT_ID
					FROM WORKING_DAY WD
					LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 14 AND RC1.REFERENCE_CODE = WD.WORKING_STATUS
					LEFT JOIN EMPLOYEE EMPLOYEE ON WD.EMPLOYEE_ID = EMPLOYEE.EMPLOYEE_ID AND SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
					LEFT JOIN DEPARTMENT DEPARTMENT ON DEPARTMENT.DEPARTMENT_ID = EMPLOYEE.DEPARTMENT_ID
					WHERE SYSDATE() BETWEEN WD.EFFECTIVE_START_DATE AND WD.EFFECTIVE_END_DATE
					AND WD.WORKING_DAY <= ? AND WD.WORKING_STATUS = 1
					ORDER BY DEPARTMENT.DEPARTMENT_NAME, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, WD.WORKING_DAY";
					
		$stmt = $conn->prepare($sql);
		$stmt->execute(array($enddate));	

		UNSET($WORKINGSUMMARY);
		UNSET($DEPARTMENT_ARRAY);
		UNSET($EMPLOYEE_ARRAY);
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$employeeid = $arr['EMPLOYEE_ID'];
			$workingday = substr($arr['WORKING_DAY'], 0,10);
			$workingin = substr($arr['WORKING_IN'], 11);
			$workingout = substr($arr['WORKING_OUT'], 11);
			$intervalin = $arr['INTERVAL_IN'];
			$intervalout = $arr['INTERVAL_OUT'];
			$department = $arr['DEPARTMENT_NAME'];
			$employeename = $arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME'];
			$departmentid = $arr['DEPARTMENT_ID'];
			
			$DEPARTMENT_ARRAY[$departmentid]['NAME'] = $department;
			$EMPLOYEE_ARRAY[$departmentid][$employeeid]['NAME'] = $employeename;
			
			$WORKINGSUMMARY[$departmentid][$employeeid][$workingday]['WORKING_DAY'] = $workingday;
			$WORKINGSUMMARY[$departmentid][$employeeid][$workingday]['WORKING_IN'] = $workingin;
			$WORKINGSUMMARY[$departmentid][$employeeid][$workingday]['WORKING_OUT'] = $workingout;
			$WORKINGSUMMARY[$departmentid][$employeeid][$workingday]['INTERVAL_IN'] = $intervalin;
			$WORKINGSUMMARY[$departmentid][$employeeid][$workingday]['INTERVAL_OUT'] = $intervalout;
		}
		
		$file_name = '';
		$filename = '';
		
		if (isset($WORKINGSUMMARY)){
			$runningdate = date("Ymdhis");
			$directory = dirname(__FILE__)."/FILES/";
			if (!file_exists($directory)) {
				mkdir($directory);
			}
			$directory = $directory.'REPORT/';
			if (!file_exists($directory)) {
				mkdir($directory);
			}	
			$file_name = "WORKINGSUMMARY_REPORT_".$currentyear.$currentmonth."_".$runningdate.".xls";
			$filename = $directory."WORKINGSUMMARY_REPORT_".$currentyear.$currentmonth."_".$runningdate.".xls";

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
			
			foreach ($DEPARTMENT_ARRAY as $departmentid => $dresult) {
				if ($i > 0) {
					$objPHPExcel->createSheet($i);
					$objPHPExcel->setActiveSheetIndex($i);					
				}
				$objPHPExcel->getDefaultStyle()->applyFromArray($styleInfo);
				$objPHPExcel->getActiveSheet()->setTitle($dresult['NAME']);

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
				
				$line = $line + 1;
				foreach ($EMPLOYEE_ARRAY[$departmentid] as $employeeid => $eresult) {
					foreach ($WORKINGSUMMARY[$departmentid][$employeeid] as $workingday => $wresult) {
						$currentstrr = 'A';
						$startstrr = $currentstrr."".$line;
						$endstrr = $currentstrr."".$line;
						$endoffset = $endstrr;
						$dstrr = $startstrr.":".$endstrr;
						
						$value = $eresult['NAME'];
						
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $value);
						$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
						$currentstrr = ++$currentstrr;

						$startstrr = $currentstrr."".$line;
						$endstrr = $currentstrr."".$line;
						$endoffset = $endstrr;
						$dstrr = $startstrr.":".$endstrr;
						
						$value = $wresult['WORKING_DAY'];
						
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $value);
						$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
						$currentstrr = ++$currentstrr;				

						$startstrr = $currentstrr."".$line;
						$endstrr = $currentstrr."".$line;
						$endoffset = $endstrr;
						$dstrr = $startstrr.":".$endstrr;
						
						$value = $wresult['WORKING_IN']." - ".$wresult['WORKING_OUT'];
						
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr,$value);
						$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
						$currentstrr = ++$currentstrr;	
						
						$startstrr = $currentstrr."".$line;
						$endstrr = $currentstrr."".$line;
						$endoffset = $endstrr;
						$dstrr = $startstrr.":".$endstrr;
						
						$value = $wresult['INTERVAL_IN'];
						
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $value);
						$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
						$currentstrr = ++$currentstrr;	
						
						$startstrr = $currentstrr."".$line;
						$endstrr = $currentstrr."".$line;
						$endoffset = $endstrr;
						$dstrr = $startstrr.":".$endstrr;
						
						$endposition = $currentstrr;
						
						$value = $wresult['INTERVAL_OUT'];
						
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $value);
						$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
						$currentstrr = ++$currentstrr;						
						$line = $line + 1;
					}
				}
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth("30");
				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth("20");
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth("20");
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
				
				$header = "รายละเอียดเวลาเข้า - ออกที่ยังไม่ได้ส่งอนุมัติของแผนก ".$dresult['NAME'];
				
				$line = 2;
				$startposition = 'A';
				

				$startstrr = $startposition."".$line;
				$endstrr = $endposition."".$line;
				$dstrr = $startstrr.":".$endstrr;		
				
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $header);
				$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleHeader);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
					
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