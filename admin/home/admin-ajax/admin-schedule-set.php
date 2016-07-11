<?
	$appkey = $_REQUEST['appkey'];
	$date = $_REQUEST['date'];
	$cnt = $_REQUEST['cnt'];
	$inc = $_REQUEST['inc'];
	
	$db_appkey = mysql_real_escape_string($appkey);
	$db_date = mysql_real_escape_string($date);
	$db_cnt = mysql_real_escape_string($cnt);
	$db_inc = mysql_real_escape_string($inc);

	$sql = "SELECT count(*) cnt FROM al_app_schedule_t WHERE app_key = '{$db_appkey}' AND schedule_date = '{$db_date}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));	
	if ($row['cnt'] > 0) {
		$sql = "UPDATE al_app_schedule_t SET cnt = '{$db_cnt}', inc = '{$db_inc}' WHERE app_key = '{$db_appkey}' AND schedule_date = '{$db_date}'";	
	} else {
		$sql = "INSERT al_app_schedule_t (app_key, schedule_date, cnt, inc)
				VALUES ('{$db_appkey}', '{$db_date}', '{$db_cnt}', '{$db_inc}')";	
	}
	mysql_query($sql, $conn);
	
	// app_schedule_t의 개수를 app_t 에 갱신한다.
	$sql = "UPDATE al_app_t SET schedule_cnt = (SELECT count(*) FROM app_schedule_t WHERE app_key = '{$db_appkey}') WHERE app_key = '{$db_appkey}'";
	mysql_query($sql, $conn);
	
	include dirname(__FILE__) . '/admin-schedule-get-list.php';
?>