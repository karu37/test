<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$publisher_code = trim($_REQUEST['pcode']);
	$callback_url = trim($_REQUEST['callbackurl']);
	$reward_percent = trim($_REQUEST['rewardpercent']);
	
	if (!$publisher_code || !$callback_url || !$reward_percent) return_die(false, null, '필요한 정보가 없습니다.');

	$db_publisher_code = mysql_real_escape_string($publisher_code);
	$db_callback_url = mysql_real_escape_string($callback_url);
	$db_reward_percent = mysql_real_escape_string($reward_percent);

	$sql = "UPDATE al_publisher_t 
			SET
				callback_url = '{$db_callback_url}',
				reward_percent = '{$db_reward_percent}'
			WHERE pcode = '{$db_publisher_code}'";
	mysql_execute($sql, $conn);
	
	return_die(true);

?>