<?
header("Content-Type: text/html; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin,Accept,X-Requested-With,Content-Type,Access-Control-Request-Method,Access-Control-Request-Headers,Authorization");

include dirname(__FILE__)."/../../.admin_ips.php";
include dirname(__FILE__)."/php_lib/util.php";

$id = $_REQUEST['id'];
$path_queries = dirname(__FILE__) . "/admin-ajax";
$file = $path_queries . "/{$id}.php";

$_start_api_tm = get_timestamp();

$conn = dbConn();

if (!$conn) return_die(false, null, '서비스 연결이 원활하지 않습니다.(20)');

if (file_exists($file)) {
	
	ini_set("display_errors", "1");
	error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);	
	
	include $file;
	exit;
}

// http://www.golfon.kr/golf/ajax/request.php?admin_id=man%40gmail.com&umcode=970e817c27cd333e1c4a57cd90f3df5b&id=user-get-avail-cashback
function get_publisher_info($req_user_fields = "", &$ar_publisher = array())
{
	global $conn;
	
	$pcode = mysql_real_escape_string($_REQUEST['pcode']);
	if ($req_user_fields == "") {
		$sql = "select is_mactive from al_publisher_t where pcode = '{$pcode}'";
		$result = mysql_query($sql, $conn);
		$row = mysql_fetch_assoc($result);
		if (!$row || !$row['is_mactive']) return "";
		return $row['is_mactive'];
	}
	
	//  field 가 있는 경우 파랍미터 2번째 값에 값들을 리턴한다.
	$sql = "select is_mactive, {$req_user_fields} from al_publisher_t where pcode = '{$pcode}'";
	$result = mysql_query($sql, $conn);
	$row = mysql_fetch_assoc($result);
	if (!$row || !$row['is_mactive']) return "";
	$ar_publisher = $row;
	return $row['is_mactive'];

}

?>