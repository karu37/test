<?
	$appkey = $_REQUEST['appkey'];
	$text   = $_REQUEST['text'];
	
	if (!$appkey) return_die(false, null, '정보를 모두 입력해 주십시요.');
	if (!$text) return_die(false, null, 'ADID가 없습니다.');
	
	$db_appkey = mysql_real_escape_string($appkey);
	
	$ar_adids = explode("\n", $text);
	$cnt = count($ar_adids);
	
	// 각 모든 앞뒤 공백 제거
	$ar_values = array();
	for ($i = 0; $i < $cnt; $i ++) {
		$adid = trim(strtolower($ar_adids[$i]));
		if (!$adid) continue;
		
		$db_adid = mysql_real_escape_string($adid);
		$ar_values[] = "('{$db_adid}', '{$db_appkey}')";
	}
	
	$n_unit = 100;
	$value_cnt = count($ar_values);
	for ($i = 0; $i <= $value_cnt / $n_unit; $i ++) {
		
		$ar_insert = array_slice($ar_values, $i * $n_unit, $n_unit);
		if (count($ar_insert) == 0) continue;
		
		$sql = "INSERT IGNORE al_app_adid_uploaded_t (adid, app_key)\nVALUES " . implode(",\n", $ar_insert) . ';';
		mysql_query($sql, $conn);
	}
 	return_die(true, null, '요청이 완료되었습니다');

?>