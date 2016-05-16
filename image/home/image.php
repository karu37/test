<?
	include dirname(__FILE__)."/php_lib/util_of_db_for_image.php";
	
	$conn = dbConn();

	$file = $_REQUEST['file'];
	$db_file = mysql_real_escape_string($file);

	$sql = "SELECT id, data FROM al_image_data_t WHERE id = '{$db_file}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if (!$row['id']) {
		header("HTTP 404 Not Found");
		die();
	}
	
	// http response
	header("Content-Type: image/jpeg");
	// header("Cache-Control: max-age=290304000, public");
	header("Cache-Control: max-age=86400, public");
	echo $row['data'];
	die();
?>