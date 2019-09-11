<?	
if (!function_exists('monthname')) {
	function monthname($month) {
		switch ($month) {
			case '01': 
				return 'มกราคม';
			break;
			case '02': 
				return 'กุมภาพันธ์';
			break;
			case '03': 
				return 'มีนาคม';
			break;
			case '04': 
				return 'เมษายน';
			break;
			case '05': 
				return 'พฤษภาคม';
			break;
			case '06': 
				return 'มิถุนายน';
			break;
			case '07': 
				return 'กรกฎาคม';
			break;
			case '08': 
				return 'สิงหาคม';
			break;
			case '09': 
				return 'กันยายน';
			break;
			case '10': 
				return 'ตุลาคม';
			break;
			case '11': 
				return 'พฤศจิกายน';
			break;
			case '12': 
				return 'ธันวาคม';
			break;
		}
	}
}
?>