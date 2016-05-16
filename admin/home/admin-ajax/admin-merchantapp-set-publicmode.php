<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');

	$merchant_code = trim($_REQUEST['mcode']);
	$appkey = trim($_REQUEST['appkey']);
	$is_publicmode = trim($_REQUEST['publicmode']);
	if (!$merchant_code || !$appkey || !$is_publicmode) return_die(false, null, '필요한 정보가 없습니다.');

	$db_merchant_code = mysql_real_escape_string($merchant_code);
	$db_appkey = mysql_real_escape_string($appkey);
	$db_is_publicmode = mysql_real_escape_string($is_publicmode);

	begin_trans($conn);
	
		$sql = "SELECT id, is_public_mode FROM al_app_t WHERE mcode = '{$db_merchant_code}' AND app_key = '{$db_appkey}' FOR UPDATE";
		$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
		if ($row['id']) {
			
			// 이미 동일한 상태인 경우
			if ( $row['is_public_mode'] == $is_publicmode ) {
				rollback($conn);
				return_die(false, null, "이미 설정 중인 상태입니다.\n\n(변경사항이 없습니다.)");
			}
			
			$sql = "UPDATE al_app_t SET is_public_mode = '{$db_is_publicmode}' WHERE id = '{$row['id']}'";
			mysql_execute($sql, $conn);
			
			// 해당 모든 앱들의al_public_app_t의 설정을 초기화 한다. 차단/설정
			$sql = "UPDATE al_publisher_app_t SET merchant_disabled = NULL, merchant_enabled = NULL WHERE app_key = '{$db_appkey}'";
			mysql_execute($sql, $conn);
			
			// 불필요한 값들 모두 초기화
			admindb_publisher_app_clear('', $appkey, $conn);
			
		} else {
			rollback($conn);
			return_die(false, null, '대상 광고가 존재하지 않습니다.');		
		}
	
	commit($conn);
	
	return_die(true, $ar_data);
	
?>