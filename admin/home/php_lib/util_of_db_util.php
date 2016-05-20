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
						AND IFNULL(open_time, '') = ''
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
						AND IFNULL(open_time, '') = ''
						AND IFNULL(exec_day_max_cnt, '') = ''
						AND IFNULL(exec_tot_max_cnt, '') = ''";		
	}
	// echo $sql;					
	mysql_execute($sql, $conn);
}

?>