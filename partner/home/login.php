<? 	ini_set( 'session.cookie_httponly', 1 );
	header("Content-Type: text/html; charset=UTF-8");
	session_start();

	include dirname(__FILE__)."/php_lib/util.php";
	
	$conn = dbConn();
	if (isset($_REQUEST['partnerid'])) $r_partnerid = $_REQUEST['partnerid'];
	if (isset($_REQUEST['partnerpw'])) $r_partnerpw = $_REQUEST['partnerpw'];
	if ($r_partnerid) 
	{
		$db_partnerpw = mysql_real_escape_string($r_partnerpw);
		$sql = "SELECT id, md5(partner_pw) as 'umcode', partner_id, name, telno from al_partner_t where partner_id='{$r_partnerid}' AND partner_pw = '{$db_partnerpw}' AND is_mactive = 'Y'";
		$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
		if (!$row) {
			goPage_die("", "로그인을 실패했습니다.");
			exit;
		}
		
		// 로그인 Session 셋팅하는 곳
		foreach($_SESSION as $key=>$value) unset($_SESSION[$key]);
		$_SESSION['partnerid'] = $row['partner_id'];
		$_SESSION['partnername'] = $row['name'];
		$_SESSION['umcode'] = $row['umcode'];

		goPage_die("partner_index.php", "");
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0">
	<title>ALINE 파트너</title>
	<Link rel="stylesheet" type="text/css" href="js/css/global.css">
	<script src="js/jquery/jquery.min.js"></script>
	<script type="text/javascript">
		//<!--
		function doLogin(){
			var partner_id = $("#partnerid").val();
			var partner_pw = $("#partnerpw").val();
			if (!partner_id || !partner_pw) {
				alert('아이디와 암호를 입력하세요.');
				return;
			}
			var frm = document.frm;
			frm.action = "login.php";
			frm.submit();
		}
		//-->
	</script>
</head>
<body topmargin=0 leftmargin=0>
<div align=center>
	<table width="100%" height=100% cellpadding=0 cellspacing=0>	
		<tr>
			<td align=center valign=middle>
				<table width=666 cellpadding=0 cellspacing=0>
					<tr>
						<td height=11 valign=top><img src=images/adm/top_bar.gif></td>
					</tr>
					<tr>
						<td>
						<form id="frm" name="frm" method="post">
							<table width=666 height=297 cellpadding=0 cellspacing=1 bgcolor=#d5d4d4>
								<tr>
									<td bgcolor=#ffffff valign=top background=images/adm/login_bg2.gif style="padding-left:146px;padding-top:117px">
										<span style=padding-left:1px><input type="text" id="partnerid" name="partnerid" class=login/></span><br>
										<img src=images/adm/blank.gif height=15px><br>
										<span style=padding-left:1px><input type="password" id="partnerpw" name="partnerpw" class=login/></span><br>
										<img src=images/adm/blank.gif height=15px><br>
											<input type=submit style="background:url(images/adm/btn_login.gif) no-repeat; width:239px; height:52px; border:0px" onclick="javascript:doLogin(); return false" value="" border=0 />
										<img src=images/adm/blank.gif height=5px><br>
									</td>
								</tr>
							</table>					
							</form>
						</td>
					</tr>				
					<tr>
						<td align=center valign=top><img src=images/adm/footer.gif></td>
					</tr>
				</table>
			</td>
		</tr>	
		
	</table>
	</div>
</body>
</html>