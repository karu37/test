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

?>