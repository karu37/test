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

if (!$conn) return_die(false, '서비스 연결이 원활하지 않습니다.(20)');

if (file_exists($file)) {
	
	ini_set("display_errors", "1");
	error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);	
	
	include $file;
	exit;
}

?>