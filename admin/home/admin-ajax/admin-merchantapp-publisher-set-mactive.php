<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');

	$merchant_code = trim($_REQUEST['mcode']);
	$appkey = trim($_REQUEST['appkey']);
	$pcode = trim($_REQUEST['pcode']);
	$publicmode = trim($_REQUEST['publicmode']);
	$is_active = trim($_REQUEST['isactive']);
	
	if (!$merchant_code || !$appkey || !$pcode || !in_array($publicmode, array('Y','N')) || !in_array($is_active, array('Y','N')) ) return_die(false, null, '필요한 정보가 없습니다.');

	$db_merchant_code = mysql_real_escape_string($merchant_code);
	$db_appkey = mysql_real_escape_string($appkey);
	$db_pcode = mysql_real_escape_string($pcode);
	$db_is_active = mysql_real_escape_string($is_active);
	
	// mcode 가 해당 appkey에 대한 권한이 있는지 확인한다.
	$sql = "SELECT id FROM al_app_t WHERE mcode = '{$db_merchant_code}'";
	$row_app = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if (!$row_app['id']) return_die(false, null, '해당 광고에 대한 권한이 없습니다.');

	begin_trans($conn);

		$db_value = 'N';
		if ($is_active == 'N') $db_value = 'Y'; // is_active == 'N' 이면 publicmode == 'Y' 에서 disabled = 'Y', publicmode == 'N' 에서 enabled = 'Y' 임.

		$sql = "SELECT id FROM al_publisher_app_t WHERE pcode = '{$db_pcode}' AND app_key = '{$db_appkey}' FOR UPDATE";
		$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
		if ($row['id']) {
			if ($publicmode == 'Y') {
				$sql = "UPDATE al_publisher_app_t SET merchant_disabled = '{$db_value}' WHERE id = '{$row['id']}'";
			} else {
				$sql = "UPDATE al_publisher_app_t SET merchant_enabled = '{$db_value}' WHERE id = '{$row['id']}'";
			}
		} else {
			if ($publicmode == 'Y') {
				$sql = "INSERT al_publisher_app_t (pcode, app_key, merchant_disabled) VALUES ('{$db_pcode}', '{$db_appkey}', '{$db_value}');";
			} else {
				$sql = "INSERT al_publisher_app_t (pcode, app_key, merchant_enabled) VALUES ('{$db_pcode}', '{$db_appkey}', '{$db_value}');";
			}
		}
 		mysql_execute($sql, $conn);
 		
	commit($conn);

	admindb_publisher_app_clear($pcode, $appkey, $conn);
	
	return_die(true, $ar_data);
	
?>