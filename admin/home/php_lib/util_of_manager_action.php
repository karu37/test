<?

function manager_user_log($admin_id, $user_id, $action_code, $action)
{
	global $conn;
	$db_admin_id = mysql_real_escape_string($admin_id);
	$db_user_id = mysql_real_escape_string($user_id);
	$db_action_code = mysql_real_escape_string($action_code);
	$db_action = mysql_real_escape_string($action);
	$sql = "INSERT INTO al_admin_action_user_t (admin_id, user_id, action, memo)
			VALUES ('{$db_admin_id}', '{$db_user_id}', '{$db_action_code}', '{$db_action}')";
	mysql_query($sql, $conn);
}


?>