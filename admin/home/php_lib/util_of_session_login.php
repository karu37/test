<?
function is_loginned() 
{
	if ($_SESSION && $_SESSION['adminid'] && $_SESSION['adminname'] && $_SESSION['umcode']) return true;
	return false;
}
function get_loginname()
{
	return $_SESSION['adminname'];
}
function get_session_id()
{
	return $_SESSION['adminid'];
}
function get_session_level()
{
	return $_SESSION['adminlevel'];
}
function doLogout($url="", $msg="") 
{
	unset($_SESSION['adminid']);
	unset($_SESSION['adminpw']);
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