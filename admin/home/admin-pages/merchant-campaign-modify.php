<?
	$partner_id = $_REQUEST['partnerid'];
	$mcode = $_REQUEST['mcode'];
	$appkey = $_REQUEST['appkey'];
	
	$db_appkey = mysql_real_escape_string($appkey);
	$db_mcode = mysql_real_escape_string($mcode);
	
	$sql = "SELECT * FROM al_app_t WHERE app_key = '{$db_appkey}' AND mcode = '{$db_mcode}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	
	if ( !in_array( $row['app_exec_type'], array('I', 'E', 'S') ) ) {
		include "merchant-campaign-modify-web.php";
	} else {
		include "merchant-campaign-modify-app.php";
	}
	exit;
?>