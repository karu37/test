<?
header("Content-Type: text/html; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-Requested-With");

include dirname(__FILE__)."/php_lib/util.php";

$id = $_REQUEST['id'];
$path_queries = dirname(__FILE__) . "/guest-ajax";
$file = $path_queries . "/{$id}.php";

$conn = dbConn();

if (!$conn) return_die(false, '서비스 연결이 원활하지 않습니다.(20)');

if (file_exists($file)) {
	
	ini_set("display_errors", "1");
	error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);	
	
	include $file;
	exit;
}

function get_auth_guestid($req_user_fields = "", &$ar_guest = null) 
{
	global $conn;
	$guest_id = $_REQUEST['guest_id'];
	$db_guest_id = mysql_real_escape_string($guest_id);
	$db_umcode = mysql_real_escape_string($_REQUEST['umcode']);
	if (!$db_guest_id) return "";
	
	if ($req_user_fields == "") {
		$sql = "select id from guest_user_t where guest_id = '{$db_guest_id}' and md5(guest_pw) = '{$db_umcode}'";
		$result = mysql_query($sql, $conn);
		$row = mysql_fetch_assoc($result);
		if (!$row || !$row['id']) return "";
		return $guest_id;
	}
	
	//  field 가 있는 경우 파랍미터 2번째 값에 값들을 리턴한다.
	$sql = "select id, {$req_user_fields} from guest_user_t where guest_id = '{$db_guest_id}' and md5(guest_pw) = '{$db_umcode}'";
	$result = mysql_query($sql, $conn);
	$row = mysql_fetch_assoc($result);
	if (!$row || !$row['id']) return "";
	$ar_guest = $row;
	return $guest_id;

}
?>