<?
header("Content-Type: text/html; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin,Accept,X-Requested-With,Content-Type,Access-Control-Request-Method,Access-Control-Request-Headers,Authorization");

include dirname(__FILE__)."/../../.admin_ips.php";
include dirname(__FILE__)."/php_lib/util.php";

$id = $_REQUEST['id'];
$path_queries = dirname(__FILE__) . "/admin-ajax";
$file = $path_queries . "/{$id}.php";

$conn = dbConn();

if (!$conn) return_die(false, '���� ������ ��Ȱ���� �ʽ��ϴ�.(20)');

if (file_exists($file)) {
	
	ini_set("display_errors", "1");
	error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);	
	
	include $file;
	exit;
}

// http://www.golfon.kr/golf/ajax/request.php?admin_id=man%40gmail.com&umcode=970e817c27cd333e1c4a57cd90f3df5b&id=user-get-avail-cashback
function get_auth_adminid($req_user_fields = "", &$ar_admin = null) 
{
	global $conn;
	$admin_id = $_REQUEST['admin_id'];
	$db_admin_id = mysql_real_escape_string($admin_id);
	$db_umcode = mysql_real_escape_string($_REQUEST['umcode']);
	if (!$db_admin_id) return "";
	
	if ($req_user_fields == "") {
		$sql = "select id from al_admin_user_t where admin_id = '{$db_admin_id}' and md5(admin_pw) = '{$db_umcode}'";
		$result = mysql_query($sql, $conn);
		$row = mysql_fetch_assoc($result);
		if (!$row || !$row['id']) return "";
		return $admin_id;
	}
	
	//  field �� �ִ� ��� �Ķ����� 2��° ���� ������ �����Ѵ�.
	$sql = "select id, {$req_user_fields} al_from admin_user_t where admin_id = '{$db_admin_id}' and md5(user_pw) = '{$db_umcode}'";
	$result = mysql_query($sql, $conn);
	$row = mysql_fetch_assoc($result);
	if (!$row || !$row['id']) return "";
	$ar_admin = $row;
	return $admin_id;

}
?>