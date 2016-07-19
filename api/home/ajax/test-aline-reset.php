<?
/*	
	if (!$_REQUEST['adid']) $_REQUEST['adid'] = '0000000000000000-0000-0000-0000-0000';

	$pcode = $_REQUEST['pcode'];
	$db_pcode = mysql_real_escape_string($pcode);

	mysql_query("DELETE FROM al_user_app_saving_t WHERE pcode = '{$db_pcode}'", $conn);
	mysql_query("DELETE FROM al_user_app_t WHERE pcode = '{$db_pcode}'", $conn);
	mysql_query("DELETE FROM al_app_exec_stat_t", $conn);
	mysql_query("DELETE FROM al_app_exec_pub_stat_t", $conn);
	mysql_query("DELETE FROM al_app_start_stat_t", $conn);
	mysql_query("DELETE FROM al_summary_user_sales_h_t WHERE pcode = '{$db_pcode}'", $conn);
	
	return_die('Y');
*/	
?>
