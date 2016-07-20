<? 	ini_set( 'session.cookie_httponly', 1 );
	session_start();

	include dirname(__FILE__)."/php_lib/util.php";
	
	if (!is_loginned()) goPage_die("login.php", "");

	goPage_die("guest_index.php", "");
?>