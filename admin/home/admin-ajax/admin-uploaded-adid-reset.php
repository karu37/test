<?
	$appkey = $_REQUEST['appkey'];
	
	if (!$appkey) return_die(false, null, '정보를 모두 입력해 주십시요.');
	$db_appkey = mysql_real_escape_string($appkey);
	
	$sql = "DELETE FROM al_app_adid_uploaded_t WHERE app_key = '{$db_appkey}'";
	mysql_query($sql, $conn);

 	return_die(true, null, '초기화 되었습니다');
?>