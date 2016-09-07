<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');

	$merchant_code = trim($_REQUEST['mcode']);
	$pcode = trim($_REQUEST['pcode']);
	$is_active = trim($_REQUEST['isactive']);
	
	if (!$merchant_code || !$pcode || !in_array($is_active, array('Y','N')) ) return_die(false, null, '필요한 정보가 없습니다.');

	$db_merchant_code = mysql_real_escape_string($merchant_code);
	$db_pcode = mysql_real_escape_string($pcode);
	$db_is_active = mysql_real_escape_string($is_active);
	
	begin_trans($conn);

		$sql = "SELECT id FROM al_merchant_publisher_t WHERE mcode = '{$db_merchant_code}' AND pcode = '{$db_pcode}' FOR UPDATE";
		$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
		if ($row['id']) {
			$sql = "UPDATE al_merchant_publisher_t SET is_mactive = '{$db_is_active}' WHERE id = '{$row['id']}'";
		} else {
			$sql = "INSERT al_merchant_publisher_t (mcode, pcode, is_mactive) VALUES ('{$db_merchant_code}', '{$db_pcode}', '{$db_is_active}');";
		}
 		mysql_execute($sql, $conn);
 		
	commit($conn);

	admindb_merchant_publisher_clear($merchant_code, $pcode, $conn);
	
	return_die(true, null);
	
?>