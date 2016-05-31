<?
	$appkey          	   = $_REQUEST['appkey'];
	
	if ($appkey =="") return_die(false, null, '정보를 모두 입력해 주십시요.');
	
	$db_appkey          		= mysql_real_escape_string($appkey);
	
	$sql = "SELECT * FROM al_app_t WHERE app_key = '{$db_appkey}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	$ar_data['app_title'] = $row['app_title'];
	
	$sql = "SELECT count(*) as cnt FROM al_app_adid_uploaded_t WHERE app_key = '{$db_appkey}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	$ar_data['adid_cnt'] = $row['cnt'];

 	return_die(true, $ar_data);

?>