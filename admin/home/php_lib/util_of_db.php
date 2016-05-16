<?
function dbConn() {
	
	$db = "aline";
	// $db_host = "localhost";
	$db_host = "111.67.221.119";
	$db_user = "keywords";
	$db_passwd = "dhxhfld!@#";

	$connect = @mysql_connect($db_host,$db_user,$db_passwd, null, MYSQL_CLIENT_COMPRESS);
	if (!$connect) return null;
	
	@mysql_select_db($db, $connect);
	@mysql_query("SET NAMES 'utf8';");
	return $connect;	
}

function begin_trans($conn) {
	mysql_query("START TRANSACTION", $conn);	
}

function commit($conn) {
	mysql_query("COMMIT;", $conn);	
}

function rollback($conn) {
	mysql_query("ROLLBACK;", $conn);	
}

function mysql_query_row($sql, $conn) {
	return @mysql_fetch_assoc(mysql_query($sql, $conn));	
}

function mysql_execute($sql, $conn) {
	global $dev_mode;
	global $conn_log;
	
	mysql_query($sql, $conn);
	if (mysql_errno($conn)) {
		if ($dbv_mode) {
/*			
			if (!$conn_log) $conn_log = dbConn();
			$db_error = mysql_real_escape_string(mysql_error());
			$db_sql = mysql_real_escape_string($sql);
			mysql_query("INSERT _error_sql_log (sql, error) VALUES ('{$db_sql}', '{$db_error}');", $conn_log);
*/			
			echo "DB Error : " . mysql_error($conn) . "\n";
		}
		return false;
	}
	return mysql_affected_rows($conn);
}

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