<?
function is_loginned() 
{
	if (isset($_SESSION['guestid']) && isset($_SESSION['guestname']) && isset($_SESSION['umcode']) && 
		$_SESSION['guestid'] && $_SESSION['guestname'] && $_SESSION['umcode']) return true;
	return false;
}
function get_loginname()
{
	if (!isset($_SESSION['guestid'])) return "";
	return $_SESSION['guestname'];
}
function get_session_id()
{
	if (!isset($_SESSION['guestid'])) return "";
	return $_SESSION['guestid'];
}
function doLogout($url="", $msg="") 
{
	unset($_SESSION['guestid']);
	unset($_SESSION['guestpw']);
	session_destroy();
	
	if (empty($url)) $url = "/";
	if (empty($msg)) $msg = "로그아웃 되었습니다.";
	
	goPage_die($url, $msg);
}

function goPage_die($url, $msg = "") {
	
	$script_msg = "";
	if (empty($url)) $url = $_SERVER[PHP_SELF];
	if (!empty($msg)) 
	{
		$msg = str_replace("\n", "\\n", $msg);
		$script_msg = "alert('$msg');";
	}
	
	$htm_block_1 =<<<CODE1
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
	<SCRIPT LANGUAGE='JavaScript'>
		{$script_msg}
		window.location.href='{$url}';
	</SCRIPT>
</head>
<body>
</body>
</html>
CODE1;

	echo $htm_block_1;
	die();
}
?>