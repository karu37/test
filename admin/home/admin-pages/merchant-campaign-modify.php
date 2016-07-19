<?
	$mcode = $_REQUEST['mcode'];
	$appkey = $_REQUEST['appkey'];
	
	$db_appkey = mysql_real_escape_string($appkey);
	$db_mcode = mysql_real_escape_string($mcode);
	
	$sql = "SELECT app.*, m.name AS 'm_name' FROM al_app_t app LEFT OUTER JOIN al_merchant_t m ON app.mcode = m.mcode WHERE app.app_key = '{$db_appkey}' AND app.mcode = '{$db_mcode}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if ( !in_array( $row['app_exec_type'], array('I', 'E', 'S') ) ) {
		include "merchant-campaign-modify-web.php";
	} else {
		include "merchant-campaign-modify-app.php";
	}
	exit;
?>