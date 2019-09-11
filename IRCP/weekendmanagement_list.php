<?
	require ('connection.php');
	
	try {

		echo("<div id = 'mcalendar'>");
		echo("</div>");
	}
	catch (exception $e) {
		header("Status: 500");
		header("Content-Type: text/plain" );
		echo 'Message: ' .$e->getMessage();
	}
	$conn = null;
?>