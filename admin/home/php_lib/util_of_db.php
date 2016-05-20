<?
function dbConn() {
	
	global $site_define;
	
	$db = $site_define['db-name'];
	$db_host = $site_define['db-host'];
	$db_user = $site_define['db-user'];
	$db_passwd = $site_define['db-passwd'];
	
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

?>