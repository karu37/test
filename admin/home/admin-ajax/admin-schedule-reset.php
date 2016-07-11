<?
	$appkey = $_REQUEST['appkey'];
	
	$db_appkey = mysql_real_escape_string($appkey);

	$sql = "DELETE FROM al_app_schedule_t WHERE app_key = '{$db_appkey}'";
	mysql_query($sql, $conn);
	
	// app_schedule_t의 개수를 app_t 에 갱신한다.
	$sql = "UPDATE al_app_t SET schedule_cnt = 0 WHERE app_key = '{$db_appkey}'";
	mysql_query($sql, $conn);
	
	include dirname(__FILE__) . '/admin-schedule-get-list.php';
?>