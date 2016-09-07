<?
// --------------------------------------------------------------
// DB 관련 함수
// --------------------------------------------------------------

function admindb_publisher_app_clear($publisher_code, $appkey, $conn)
{
	$db_appkey = mysql_real_escape_string($appkey);
	
	if ($publisher_code)
	{
		$db_publisher_code = mysql_real_escape_string($publisher_code);
		$sql = "DELETE FROM al_publisher_app_t 
				WHERE pcode = '{$db_publisher_code}' 
						AND app_key = '{$db_appkey}' 
						AND IFNULL(merchant_disabled, 'N') = 'N'
						AND IFNULL(merchant_enabled, 'N') = 'N'
						AND IFNULL(publisher_disabled, 'N') = 'N'
						AND IFNULL(is_mactive, 'Y') = 'Y'
						AND IFNULL(app_offer_fee, '') = ''
						AND IFNULL(app_offer_fee_rate, '') = ''
						AND IFNULL(active_time, '') = ''
						AND IFNULL(exec_day_max_cnt, '') = ''
						AND IFNULL(exec_tot_max_cnt, '') = ''";
	} else {
		$sql = "DELETE FROM al_publisher_app_t 
				WHERE app_key = '{$db_appkey}' 
						AND IFNULL(merchant_disabled, 'N') = 'N'
						AND IFNULL(merchant_enabled, 'N') = 'N'
						AND IFNULL(publisher_disabled, 'N') = 'N'
						AND IFNULL(is_mactive, 'Y') = 'Y'
						AND IFNULL(app_offer_fee, '') = ''
						AND IFNULL(app_offer_fee_rate, '') = ''
						AND IFNULL(active_time, '') = ''
						AND IFNULL(exec_day_max_cnt, '') = ''
						AND IFNULL(exec_tot_max_cnt, '') = ''";		
	}
	// echo $sql;					
	mysql_execute($sql, $conn);
}

// al_merchant_publisher_t 에 관련 정보가 Y 인 경우 ==> 해당 필드 삭제
function admindb_merchant_publisher_clear($mcode, $pcode, $conn)
{
	$db_mcode = mysql_real_escape_string($mcode);
	$db_pcode = mysql_real_escape_string($pcode);
	
	if (!$mcode || !$pcode) return;
	
	$sql = "DELETE FROM al_merchant_publisher_t 
			WHERE mcode = '{$db_mcode}' 
					AND pcode = '{$db_pcode}' 
					AND is_mactive = 'Y'";
	mysql_execute($sql, $conn);
}
?>