<?
	$pcode = $_REQUEST['pcode'];
	$adid = $_REQUEST['adid'];
	$appkey = $_REQUEST['appkey'];
	
	if (!$pcode || !$adid || !$appkey) return_die('N', null, '파라미터 없음');
	
	$db_pcode = mysql_real_escape_string($pcode);
	$db_adid = mysql_real_escape_string($adid);
	$db_appkey = mysql_real_escape_string($appkey);

	// 재 참여가 가능하도록 함.
	mysql_query($sql = "DELETE FROM al_user_app_t WHERE app_key = '{$db_appkey}' AND pcode = '{$db_pcode}' AND adid = '{$db_adid}';", $conn);
	// mysql_query($sql = "DELETE FROM al_user_app_saving_t WHERE app_key = '{$db_appkey}' AND pcode = '{$db_pcode}' AND adid = '{$db_adid}';", $conn);
	
	return_die('Y');
?>