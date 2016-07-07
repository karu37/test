<?
function dbConn() {
	
	$db = "aline";
	// $db_host = "localhost";
	$db_host = "111.67.221.123";
	$db_user = "aline";
	$db_passwd = "alinewd12^^!#";

	$connect = @mysql_connect($db_host,$db_user,$db_passwd, null, MYSQL_CLIENT_COMPRESS);
	if (!$connect) return null;
	
	@mysql_select_db($db, $connect);
	@mysql_query("SET NAMES 'utf8';");
	return $connect;	
}

?>