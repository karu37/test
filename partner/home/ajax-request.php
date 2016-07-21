<?
header("Content-Type: text/html; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-Requested-With");

include dirname(__FILE__)."/php_lib/util.php";

$id = $_REQUEST['id'];
$path_queries = dirname(__FILE__) . "/partner-ajax";
$file = $path_queries . "/{$id}.php";

$conn = dbConn();

if (!$conn) return_die(false, '서비스 연결이 원활하지 않습니다.(20)');

if (file_exists($file)) {
	
	ini_set("display_errors", "1");
	error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);	
	
	include $file;
	exit;
}

function get_auth_partnerid($req_user_fields = "", &$ar_partner = null) 
{
	global $conn;
	$partner_id = $_REQUEST['partner_id'];
	$db_partner_id = mysql_real_escape_string($partner_id);
	$db_umcode = mysql_real_escape_string($_REQUEST['umcode']);
	if (!$db_partner_id) return "";
	
	if ($req_user_fields == "") {
		$sql = "select id from al_partner_t where partner_id = '{$db_partner_id}' and md5(partner_pw) = '{$db_umcode}'";
		$result = mysql_query($sql, $conn);
		$row = mysql_fetch_assoc($result);
		if (!$row || !$row['id']) return "";
		return $partner_id;
	}
	
	//  field 가 있는 경우 파랍미터 2번째 값에 값들을 리턴한다.
	$sql = "select id, {$req_user_fields} from al_partner_t where partner_id = '{$db_partner_id}' and md5(partner_pw) = '{$db_umcode}'";
	$result = mysql_query($sql, $conn);
	$row = mysql_fetch_assoc($result);
	if (!$row || !$row['id']) return "";
	$ar_partner = $row;
	return $partner_id;

}
?>