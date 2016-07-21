<? 	ini_set( 'session.cookie_httponly', 1 );
	session_start();
	error_reporting(6135);

	include dirname(__FILE__)."/php_lib/util.php";
	include dirname(__FILE__)."/php_lib/paginator.class.php";

	if (!is_loginned()) goPage_die("login.php", "");
	
	$page_id = "";
	if (isset($_REQUEST['id'])) $page_id = $_REQUEST['id'];

	if ($page_id == "") $page_id = 'home';
	$js_page_id = str_replace("-", "_", $page_id);

	$file = dirname(__FILE__) . "/partner-pages/{$page_id}.php";
	
	$partner_id = $_SESSION['partnerid'];
	$db_partner_id = @mysql_real_escape_string($partner_id);
	
	$partner_name = $_SESSION['partnername'];
	$db_partner_name = @mysql_real_escape_string($partner_name);

	// db 연결	
	$conn = dbConn();
	if (file_exists($file)) {
		
		ini_set("display_errors", "1");
		error_reporting(E_ERROR & ~E_WARNING & ~E_NOTICE);
		
		include $file;
		
	} else {
		echo "Invalid - Request";		
	}

?>