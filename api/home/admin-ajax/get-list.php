<?
	$pcode = trim($_REQUEST['pcode']);
	if (!$pcode) return_die(false, null, '잘못된 요청입니다.');

	$db_pcode = mysql_real_escape_string($pcode);
	
	$sql = "SELECT pcode, name FROM al_publisher_t WHERE pcode = '{$db_pcode}';";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));

	var_dump($row);
		
	return_die(true, $ar_data);

?>