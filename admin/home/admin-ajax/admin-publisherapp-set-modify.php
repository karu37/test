<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');

	$publisher_code = trim($_REQUEST['pcode']);
	$appkey = trim($_REQUEST['appkey']);
	
	$offerfee = trim($_REQUEST['offerfee']);
	$offerfeerate = trim($_REQUEST['offerfeerate']);
	if (!$publisher_code || !$appkey) return_die(false, null, '필요한 정보가 없습니다.');

	$db_publisher_code = mysql_real_escape_string($publisher_code);
	$db_appkey = mysql_real_escape_string($appkey);
	
	$db_quote_offerfee = "NULL";
	$db_quote_offerfeerate = "NULL";
	if ($offerfee) $db_quote_offerfee = "'" . mysql_real_escape_string($offerfee) . "'";
	if ($offerfeerate) $db_quote_offerfeerate = "'" . mysql_real_escape_string($offerfeerate) . "'";

	begin_trans($conn);
	
		$sql = "SELECT * FROM al_publisher_app_t WHERE pcode = '{$db_publisher_code}' AND app_key = '{$db_appkey}' FOR UPDATE";
		$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
		if ($row['id']) {
			$sql = "UPDATE al_publisher_app_t SET app_offer_fee = {$db_quote_offerfee}, app_offer_fee_rate = {$db_quote_offerfeerate} WHERE id = '{$row['id']}'";
		} else {
			$sql = "INSERT al_publisher_app_t (pcode, app_key, app_offer_fee, app_offer_fee_rate) VALUES ('{$db_publisher_code}', '{$db_appkey}', {$db_quote_offerfee}, {$db_quote_offerfeerate});";
		}
		mysql_execute($sql, $conn);
	
	commit($conn);
	
	admindb_publisher_app_clear($publisher_code, $appkey, $conn);

	return_die(true, $ar_data);
	
?>