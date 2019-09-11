<?	
		require ('connection.php');
	try {
		$sql = "	SELECT LEAVE_TYPE_ID, LEAVE_NAME, ALLOWANCE 
					FROM LEAVE_POLICY
					WHERE VALID_IND_CODE = 1
					ORDER BY ORDERING";

		$stmt = $conn->prepare($sql);
		$stmt->execute();
		
		UNSET($LEAVE_POLICY);
		
		while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$LEAVE_POLICY[$arr['LEAVE_TYPE_ID']]['LEAVE_NAME'] = $arr['LEAVE_NAME'];
			$LEAVE_POLICY[$arr['LEAVE_TYPE_ID']]['ALLOWANCE'] = $arr['ALLOWANCE'];
		}
		
		$filename = '';
		$filenamesummary = '';
		
			$currentyear = date('Y');
			$currentdate = date("d");
			$currentmonth = date("m");
			$endyear = 0;
			
			$currentmonth = 3;
			
			$enddate = $currentyear."-".$currentmonth."-01 00:00:00";
			
			if ($currentmonth == 1) {
				$endyear = 1;
				$currentyear = date('Y') -1;
				$currentmonth = 12;
			}
			
			
			$sql = "	SELECT EMPLOYEE.EMPLOYEE_ID, EMPLOYEE.START_DATE, YEAR(?) - YEAR(EMPLOYEE.START_DATE) - (DATE_FORMAT(?, '%m%d') < DATE_FORMAT(EMPLOYEE.START_DATE, '%m%d')) AS DIFF_YEAR,
						DATE_FORMAT(?, '%Y%m'), DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m'), 
						PERIOD_DIFF(DATE_FORMAT(?, '%Y%m'),DATE_FORMAT(EMPLOYEE.START_DATE, '%Y%m')) -12 as MONTHDIFF, EMPLOYEE.CUMULATIVE_VACATION,
						EMPLOYEE.EMPLOYEE_STATUS, EMPLOYEE.END_DATE, EMPLOYEE.TITLE, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, 
						DEPARTMENT.DEPARTMENT_NAME,
						RC1.LABEL EMPLOYEESTATUS
						FROM EMPLOYEE EMPLOYEE
						LEFT JOIN DEPARTMENT DEPARTMENT ON DEPARTMENT.DEPARTMENT_ID = EMPLOYEE.DEPARTMENT_ID
						LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 5 AND RC1.REFERENCE_CODE = EMPLOYEE.EMPLOYEE_STATUS
						WHERE SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE
						AND (EMPLOYEE.EMPLOYEE_STATUS = 1 OR (EMPLOYEE.EMPLOYEE_STATUS = 2 AND YEAR(EMPLOYEE.END_DATE) = ?))
						AND EMPLOYEE.EMPLOYEE_ID <> 1
						ORDER BY DEPARTMENT.DEPARTMENT_NAME, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME";
						
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($enddate, $enddate, $enddate, $enddate, $currentyear));	
			
			UNSET($EMPLOYEE);
			
			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$employeeid = $arr['EMPLOYEE_ID'];
				$diffyear = $arr['DIFF_YEAR'];
				$cumulative = $arr['CUMULATIVE_VACATION'];
				$employeestatus = $arr['EMPLOYEESTATUS'];
				$employeename = $arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME'];
				$department = $arr['DEPARTMENT_NAME'];
				$status = $arr['EMPLOYEE_STATUS'];
				$company_policy = $LEAVE_POLICY[5]['ALLOWANCE'];
				
				$EMPLOYEE[$employeeid]['NAME'] = $employeename;
				$EMPLOYEE[$employeeid]['DEPARTMENT'] = $department;
				$EMPLOYEE[$employeeid]['EMPLOYEESTATUS'] = $employeestatus;
				$EMPLOYEE[$employeeid]['STATUS'] = $status;
				
				
				if ($diffyear == 0) {
					$allowance = 0;
				}
				else {
					if ($cumulative < 0) {
						$cumulative = 0;
					}
					$allowance = (int)$company_policy + (int)($cumulative);
					
					if ($allowance > 12) {
						$allowance = 12;
					}
				}
				
				foreach ($LEAVE_POLICY as $leavetypeid => $result) {
					
					if ($leavetypeid == 5) {
						$EMPLOYEE[$employeeid][$leavetypeid]['ALLOWANCE'] = $allowance;
						$EMPLOYEE[$employeeid][$leavetypeid]['REMAIN'] = $allowance;
						$EMPLOYEE[$employeeid][$leavetypeid]['CUMULATIVE_VACATION'] = $cumulative;
					}
					else {
						$EMPLOYEE[$employeeid][$leavetypeid]['ALLOWANCE'] = $result['ALLOWANCE'];
						$EMPLOYEE[$employeeid][$leavetypeid]['REMAIN'] = $result['ALLOWANCE'];
						$EMPLOYEE[$employeeid][$leavetypeid]['CUMULATIVE_VACATION'] = 0;
					}
					$EMPLOYEE[$employeeid][$leavetypeid]['USED'] = 0;
					$EMPLOYEE[$employeeid][$leavetypeid]['SUM_USED'] = 0;
					$EMPLOYEE[$employeeid][$leavetypeid]['SUM_REMAIN'] = 0;
				} 
			}

			$sql = "	SELECT ELH.LEAVE_TYPE_ID, ELH.EMPLOYEE_ID, SUM(ELD.SUMMARY_LEAVE) SUM_LEAVE
						FROM EMPLOYEE_LEAVE_DETAIL ELD
						LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON ELD.LEAVE_ID = ELH.LEAVE_ID
						LEFT JOIN EMPLOYEE EMPLOYEE ON SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE 
						AND EMPLOYEE.EMPLOYEE_ID = ELH.EMPLOYEE_ID
						LEFT JOIN DEPARTMENT DEPARTMENT ON DEPARTMENT.DEPARTMENT_ID = EMPLOYEE.DEPARTMENT_ID
						LEFT JOIN LEAVE_POLICY LEAVE_POLICY ON LEAVE_POLICY.LEAVE_TYPE_ID = ELH.LEAVE_TYPE_ID
						LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 8 AND RC1.REFERENCE_CODE = ELH.LEAVE_STATUS
						WHERE YEAR(ELD.LEAVE_DATE) = ? 
						AND ELH.LEAVE_STATUS IN (1,2,3)
						AND SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
						GROUP BY ELH.LEAVE_TYPE_ID, ELH.EMPLOYEE_ID
						ORDER BY DEPARTMENT.DEPARTMENT_NAME, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, ELH.LEAVE_TYPE_ID";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($currentyear));
			
			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$EMPLOYEE[$arr['EMPLOYEE_ID']][$arr['LEAVE_TYPE_ID']]['SUM_USED'] = $arr['SUM_LEAVE'];
				$EMPLOYEE[$arr['EMPLOYEE_ID']][$arr['LEAVE_TYPE_ID']]['REMAIN'] = (int)$EMPLOYEE[$arr['EMPLOYEE_ID']][$arr['LEAVE_TYPE_ID']]['ALLOWANCE'] - $arr['SUM_LEAVE'];
			}	

			$sql = "	SELECT ELH.LEAVE_TYPE_ID, ELH.EMPLOYEE_ID, SUM(ELD.SUMMARY_LEAVE) SUM_LEAVE
						FROM EMPLOYEE_LEAVE_DETAIL ELD
						LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON ELD.LEAVE_ID = ELH.LEAVE_ID
						LEFT JOIN EMPLOYEE EMPLOYEE ON SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE 
						AND EMPLOYEE.EMPLOYEE_ID = ELH.EMPLOYEE_ID
						LEFT JOIN DEPARTMENT DEPARTMENT ON DEPARTMENT.DEPARTMENT_ID = EMPLOYEE.DEPARTMENT_ID
						LEFT JOIN LEAVE_POLICY LEAVE_POLICY ON LEAVE_POLICY.LEAVE_TYPE_ID = ELH.LEAVE_TYPE_ID
						LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 8 AND RC1.REFERENCE_CODE = ELH.LEAVE_STATUS
						WHERE YEAR(ELD.LEAVE_DATE) = ? AND MONTH(ELD.LEAVE_DATE) = ?
						AND ELH.LEAVE_STATUS IN (1,2,3)
						AND SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
						GROUP BY ELH.LEAVE_TYPE_ID, ELH.EMPLOYEE_ID
						ORDER BY DEPARTMENT.DEPARTMENT_NAME, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, ELH.LEAVE_TYPE_ID";
			$stmt = $conn->prepare($sql);
			$stmt->execute(array($currentyear, $currentmonth));
			
			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$EMPLOYEE[$arr['EMPLOYEE_ID']][$arr['LEAVE_TYPE_ID']]['USED'] = $arr['SUM_LEAVE'];
			}

			$sql = " SELECT ELH.LEAVE_TYPE_ID, ELH.EMPLOYEE_ID, ELH.LEAVE_START_DATE, ELH.LEAVE_END_DATE, ELH.SUMMARY_LEAVE, ELH.LEAVE_ID, 
						EMPLOYEE.TITLE, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, ELH.LEAVE_DESCRIPTION, LEAVE_POLICY.LEAVE_NAME,  RC1.LABEL,
						DEPARTMENT.DEPARTMENT_NAME
						FROM EMPLOYEE_LEAVE_DETAIL ELD
						LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON ELD.LEAVE_ID = ELH.LEAVE_ID
						LEFT JOIN EMPLOYEE EMPLOYEE ON SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE 
						AND EMPLOYEE.EMPLOYEE_ID = ELH.EMPLOYEE_ID
						LEFT JOIN DEPARTMENT DEPARTMENT ON DEPARTMENT.DEPARTMENT_ID = EMPLOYEE.DEPARTMENT_ID
						LEFT JOIN LEAVE_POLICY LEAVE_POLICY ON LEAVE_POLICY.LEAVE_TYPE_ID = ELH.LEAVE_TYPE_ID
						LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 8 AND RC1.REFERENCE_CODE = ELH.LEAVE_STATUS
						WHERE YEAR(ELD.LEAVE_DATE) = ? 
						AND ELH.LEAVE_STATUS IN (1,2,3)
						AND SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
						GROUP BY ELH.LEAVE_ID
						ORDER BY DEPARTMENT.DEPARTMENT_NAME, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME,  ELD.LEAVE_DATE, ELH.LEAVE_TYPE_ID";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($currentyear));
			
			UNSET($LEAVE_DETAIL);
			
			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$leavetypeid = $arr['LEAVE_TYPE_ID'];
				$employeeid = $arr['EMPLOYEE_ID'];
				$leavestartdate = substr($arr['LEAVE_START_DATE'],8,2)."/".substr($arr['LEAVE_START_DATE'],5,2)."/".((int)substr($arr['LEAVE_START_DATE'],0,4) + 543)." ".substr($arr['LEAVE_START_DATE'],11,5);
				$leaveenddate = substr($arr['LEAVE_END_DATE'],8,2)."/".substr($arr['LEAVE_END_DATE'],5,2)."/".((int)substr($arr['LEAVE_END_DATE'],0,4) + 543)." ".substr($arr['LEAVE_END_DATE'],11,5);
				$summaryleave = $arr['SUMMARY_LEAVE'];
				$employeename = $arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME'];
				$department = $arr['DEPARTMENT_NAME'];
				$leaveid = $arr['LEAVE_ID'];
				$description = $arr['LEAVE_DESCRIPTION'];
				$leavename = $arr['LEAVE_NAME'];
				$leavestatus = $arr['LABEL'];

				$LEAVE_DETAIL[$leaveid]['NAME'] = $employeename;
				$LEAVE_DETAIL[$leaveid]['DEPARTMENT'] = $department;
				$LEAVE_DETAIL[$leaveid]['LEAVE_START_DATE'] = $leavestartdate;
				$LEAVE_DETAIL[$leaveid]['LEAVE_END_DATE'] = $leaveenddate;
				$LEAVE_DETAIL[$leaveid]['SUM_LEAVE'] = $summaryleave;
				$LEAVE_DETAIL[$leaveid]['DESCRIPTION'] = $description;
				$LEAVE_DETAIL[$leaveid]['LEAVE_NAME'] = $leavename; 
				$LEAVE_DETAIL[$leaveid]['LEAVESTATUS'] = $leavestatus; 
			}

			$sql = " SELECT ELH.LEAVE_TYPE_ID, ELH.EMPLOYEE_ID, ELH.LEAVE_START_DATE, ELH.LEAVE_END_DATE, ELH.SUMMARY_LEAVE, ELH.LEAVE_ID, 
						EMPLOYEE.TITLE, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, ELH.LEAVE_DESCRIPTION, LEAVE_POLICY.LEAVE_NAME, RC1.LABEL,
						DEPARTMENT.DEPARTMENT_NAME
						FROM EMPLOYEE_LEAVE_DETAIL ELD
						LEFT JOIN EMPLOYEE_LEAVE_HISTORY ELH ON ELD.LEAVE_ID = ELH.LEAVE_ID
						LEFT JOIN EMPLOYEE EMPLOYEE ON SYSDATE() BETWEEN EMPLOYEE.EFFECTIVE_START_DATE AND EMPLOYEE.EFFECTIVE_END_DATE 
						AND EMPLOYEE.EMPLOYEE_ID = ELH.EMPLOYEE_ID
						LEFT JOIN DEPARTMENT DEPARTMENT ON DEPARTMENT.DEPARTMENT_ID = EMPLOYEE.DEPARTMENT_ID
						LEFT JOIN LEAVE_POLICY LEAVE_POLICY ON LEAVE_POLICY.LEAVE_TYPE_ID = ELH.LEAVE_TYPE_ID
						LEFT JOIN REFERENCE_CODE RC1 ON RC1.REFERENCE_TYPE_ID = 8 AND RC1.REFERENCE_CODE = ELH.LEAVE_STATUS
						WHERE YEAR(ELD.LEAVE_DATE) = ? 
						AND ELH.LEAVE_STATUS IN (1,2,3)
						AND SYSDATE() BETWEEN ELH.EFFECTIVE_START_DATE AND ELH.EFFECTIVE_END_DATE
						GROUP BY ELH.LEAVE_ID
						ORDER BY DEPARTMENT.DEPARTMENT_NAME, EMPLOYEE.FIRST_NAME, EMPLOYEE.LAST_NAME, ELD.LEAVE_DATE , ELH.LEAVE_TYPE_ID";

			$stmt = $conn->prepare($sql);
			$stmt->execute(array($currentyear));
			
			UNSET($LEAVE_DETAIL_SUMMARY);
			
			while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$leavetypeid = $arr['LEAVE_TYPE_ID'];
				$employeeid = $arr['EMPLOYEE_ID'];
				$leavestartdate = substr($arr['LEAVE_START_DATE'],8,2)."/".substr($arr['LEAVE_START_DATE'],5,2)."/".((int)substr($arr['LEAVE_START_DATE'],0,4) + 543)." ".substr($arr['LEAVE_START_DATE'],11,5);
				$leaveenddate = substr($arr['LEAVE_END_DATE'],8,2)."/".substr($arr['LEAVE_END_DATE'],5,2)."/".((int)substr($arr['LEAVE_END_DATE'],0,4) + 543)." ".substr($arr['LEAVE_END_DATE'],11,5);
				$summaryleave = $arr['SUMMARY_LEAVE'];
				$employeename = $arr['TITLE'].$arr['FIRST_NAME']." ".$arr['LAST_NAME'];
				$department = $arr['DEPARTMENT_NAME'];
				$leaveid = $arr['LEAVE_ID'];
				$description = $arr['LEAVE_DESCRIPTION'];
				$leavename = $arr['LEAVE_NAME'];
				$leavestatus = $arr['LABEL'];

				$LEAVE_DETAIL_SUMMARY[$leaveid]['NAME'] = $employeename;
				$LEAVE_DETAIL_SUMMARY[$leaveid]['DEPARTMENT'] = $department;
				$LEAVE_DETAIL_SUMMARY[$leaveid][$leavetypeid]['USED'] = $summaryleave;
				$LEAVE_DETAIL_SUMMARY[$leaveid]['LEAVE_START_DATE'] = $leavestartdate;
				$LEAVE_DETAIL_SUMMARY[$leaveid]['LEAVE_END_DATE'] = $leaveenddate;
				$LEAVE_DETAIL_SUMMARY[$leaveid]['SUM_LEAVE'] = $summaryleave;
				$LEAVE_DETAIL_SUMMARY[$leaveid]['DESCRIPTION'] = $description;
				$LEAVE_DETAIL_SUMMARY[$leaveid]['LEAVE_NAME'] = $leavename;
				$LEAVE_DETAIL_SUMMARY[$leaveid]['LEAVESTATUS'] = $leavestatus;
			}			

		
		if (isset($EMPLOYEE)) {
			$runningdate = date("Ymdhis");
			
//			require ('util.php');
//			$monthdisplay = monthname($currentmonth);
					
			$directory = dirname(__FILE__)."/FILES/";
			if (!file_exists($directory)) {
				mkdir($directory);
			}
			$directory = $directory.'REPORT/';
			if (!file_exists($directory)) {
				mkdir($directory);
			}

				$file_name = "SUMMARY_LEAVE_REPORT_".$currentyear."_".$runningdate.".xls";
				$filename = $directory."SUMMARY_LEAVE_REPORT_".$currentyear."_".$runningdate.".xls";

			
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

			
			$currentstrr = 'A';
			
			$line = 4;
			$startstrr = $currentstrr."".$line;
			$line =++$line;
			$endstrr = $currentstrr."".$line;
			$startoffset = $startstrr;
			$endoffset = $endstrr;
			$dstrr = $startstrr.":".$endstrr;
			
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'แผนก');
			$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
			$currentstrr = ++$currentstrr;

			$line = 4;
			$startstrr = $currentstrr."".$line;
			$line =++$line;
			$endstrr = $currentstrr."".$line;
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
			$line =++$line;
			$endstrr = $currentstrr."".$line;
			$endoffset = $endstrr;
			$dstrr = $startstrr.":".$endstrr;
			
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'สถานะ');
			$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
			$currentstrr = ++$currentstrr;
			
			if (isset($LEAVE_POLICY)) {
				foreach ($LEAVE_POLICY as $leavetypeid => $result) {
					$startposition = $currentstrr;
					
					$line = 5;

					$startstrr = $currentstrr."".$line;
					$endstrr = $currentstrr."".$line;
					$endoffset = $endstrr;
					$dstrr = $startstrr.":".$endstrr;

					$startstrr = $currentstrr."".$line;
					$endstrr = $currentstrr."".$line;
					$endoffset = $endstrr;
					$dstrr = $startstrr.":".$endstrr;
					
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'จำนวนวันที่ลาทั้งหมด');
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
					$currentstrr = ++$currentstrr;	

					$startstrr = $currentstrr."".$line;
					$endstrr = $currentstrr."".$line;
					$endoffset = $endstrr;
					$dstrr = $startstrr.":".$endstrr;
					
					$endposition = $currentstrr;
					
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'วันลาสะสม');
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
					$currentstrr = ++$currentstrr;					
					
					$line = 4;
					$startstrr = $startposition."".$line;
					$endstrr = $endposition."".$line;
					$dstrr = $startstrr.":".$endstrr;
					
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $result['LEAVE_NAME']);
					$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));		
				}
			}
			
			$line = 6;
			
			foreach ($EMPLOYEE as $employeeid => $eresult) {
				$currentstrr = 'A';
				
				$startstrr = $currentstrr."".$line;
				$endstrr = $currentstrr."".$line;
				$endoffset = $endstrr;
				$dstrr = $startstrr.":".$endstrr;
				
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $eresult['DEPARTMENT']);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
				$currentstrr = ++$currentstrr;	

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
				
				$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $eresult['EMPLOYEESTATUS']);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
				$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
					array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
				$currentstrr = ++$currentstrr;	

				if (isset($LEAVE_POLICY)) {
					foreach ($LEAVE_POLICY as $leavetypeid => $result) {

						$startstrr = $currentstrr."".$line;
						$endstrr = $currentstrr."".$line;
						$endoffset = $endstrr;
						$dstrr = $startstrr.":".$endstrr;
						
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $eresult[$leavetypeid]['SUM_USED']);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
						$currentstrr = ++$currentstrr;		

						$startstrr = $currentstrr."".$line;
						$endstrr = $currentstrr."".$line;
						$endoffset = $endstrr;
						$dstrr = $startstrr.":".$endstrr;
						
						$endposition = $currentstrr;
						
						$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $eresult[$leavetypeid]['CUMULATIVE_VACATION']);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
						$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
							array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
						$currentstrr = ++$currentstrr;							
					}
				}
				$line = ++$line;
			}		
			
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth("30");
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth("30");
			
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
			
			$header = "รายงานสรุปวันลาทั้งหมดของพนักงาน ประจำปี ".((int)($currentyear) + 543);
			$title = "SUMMARY_LEAVE";
			
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $header);
			$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleHeader);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));		
			
			$objPHPExcel->getActiveSheet()->setTitle($title);	

			$objPHPExcel->createSheet(1);
			$objPHPExcel->setActiveSheetIndex(1);
			
			$objPHPExcel->getDefaultStyle()->applyFromArray($styleInfo);

			
			$currentstrr = 'A';
			
			$line = 4;
			$startstrr = $currentstrr."".$line;
			$endstrr = $currentstrr."".$line;
			$startoffset = $startstrr;
			$endoffset = $endstrr;
			$dstrr = $startstrr.":".$endstrr;
			
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'แผนก');
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
			
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'ประเภทวันลา');
			$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
			$currentstrr = ++$currentstrr;		
			
			$line = 4;
			$startstrr = $currentstrr."".$line;
			$currentstrr = ++$currentstrr;	
			$endstrr = $currentstrr."".$line;
			$endoffset = $endstrr;
			$dstrr = $startstrr.":".$endstrr;
			
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'วันที่ลา');
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
			
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'จำนวนวันที่ลา');
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
			
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'สถานะใบลา');
			$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
			$currentstrr = ++$currentstrr;	
			
			$line = 4;
			$startstrr = $currentstrr."".$line;
			$endstrr = $currentstrr."".$line;
			$endoffset = $endstrr;
			$endposition = $currentstrr;
			$dstrr = $startstrr.":".$endstrr;
			
			$objPHPExcel->getActiveSheet()->setCellValue($startstrr, 'รายละเอียด');
			$objPHPExcel->getActiveSheet()->mergeCells($dstrr);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($stylesubHeader);
			$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
				array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
			$currentstrr = ++$currentstrr;	
			
			if (isset($LEAVE_DETAIL)) {
				$line = 5;
				foreach ($LEAVE_DETAIL as $leaveid => $result) {
					$currentstrr = 'A';
					
					$startstrr = $currentstrr."".$line;
					$endstrr = $currentstrr."".$line;
					$endoffset = $endstrr;
					$dstrr = $startstrr.":".$endstrr;
					
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $result['DEPARTMENT']);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
					$currentstrr = ++$currentstrr;	

					$startstrr = $currentstrr."".$line;
					$endstrr = $currentstrr."".$line;
					$endoffset = $endstrr;
					$dstrr = $startstrr.":".$endstrr;
					
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $result['NAME']);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
					$currentstrr = ++$currentstrr;						

					$startstrr = $currentstrr."".$line;
					$endstrr = $currentstrr."".$line;
					$endoffset = $endstrr;
					$dstrr = $startstrr.":".$endstrr;
					
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $result['LEAVE_NAME']);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
					$currentstrr = ++$currentstrr;	

					$startstrr = $currentstrr."".$line;
					$endstrr = $currentstrr."".$line;
					$endoffset = $endstrr;
					$dstrr = $startstrr.":".$endstrr;
					
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $result['LEAVE_START_DATE']);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
					$currentstrr = ++$currentstrr;	
	
					$startstrr = $currentstrr."".$line;
					$endstrr = $currentstrr."".$line;
					$endoffset = $endstrr;
					$dstrr = $startstrr.":".$endstrr;
					
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $result['LEAVE_END_DATE']);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
					$currentstrr = ++$currentstrr;	

					$startstrr = $currentstrr."".$line;
					$endstrr = $currentstrr."".$line;
					$endoffset = $endstrr;
					$dstrr = $startstrr.":".$endstrr;
					
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $result['LEAVESTATUS']);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
					$currentstrr = ++$currentstrr;	
					
					$startstrr = $currentstrr."".$line;
					$endstrr = $currentstrr."".$line;
					$endoffset = $endstrr;
					$dstrr = $startstrr.":".$endstrr;
					
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $result['SUM_LEAVE']);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));	
					$currentstrr = ++$currentstrr;	
					
					$startstrr = $currentstrr."".$line;
					$endstrr = $currentstrr."".$line;
					$endoffset = $endstrr;
					$dstrr = $startstrr.":".$endstrr;
					
					$objPHPExcel->getActiveSheet()->setCellValue($startstrr, $result['DESCRIPTION']);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->applyFromArray($styleInfo);
					$objPHPExcel->getActiveSheet()->getStyle($startstrr)->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,));	
					$currentstrr = ++$currentstrr;	
	
					$line = ++$line;
				}
			}
			
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth("30");
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth("30");
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth("20");
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth("20");
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth("20");
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth("20");
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth("15");
			$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth("40");
			
			$offset = $startoffset.":".$endoffset;
				
			 $objPHPExcel->getActiveSheet()->getStyle($offset)->applyFromArray(array(
					'borders' => array(
						'allborders' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN
						)
					)
			)); 
			require ('util.php');
			
			$displaymonth = monthname($currentmonth);
			
			$header = "รายละเอียดวันลาทั้งหมดของพนักงาน ประจำเดือน ".((int)($currentyear) + 543);
			
			$title = "LEAVE_DETAILS";

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
			
			$objPHPExcel->getActiveSheet()->setTitle($title);	

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