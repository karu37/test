<? 	ini_set( 'session.cookie_httponly', 1 );
	session_start();
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

	include dirname(__FILE__)."/php_lib/util.php";
	include dirname(__FILE__)."/php_lib/paginator.class.php";

	if (!is_loginned()) goPage_die("login.php", "");
	
	$header_title = "ALINE 파트너";
	
	if (isset($_REQUEST['id'])) $page_id = $_REQUEST['id'];
	if ($page_id == "") $page_id = 'partner-app-lists';
	$js_page_id = str_replace("-", "_", $page_id);
	
	$file = dirname(__FILE__) . "/partner-pages/{$page_id}.php";

	// db 연결	
	$conn = dbConn();

	$body_min_width = "1100px";
	$post_script = "";

	$partner_id = $_SESSION['partnerid'];
	$db_partner_id = @mysql_real_escape_string($partner_id);
	
	$partner_name = $_SESSION['partnername'];
	$db_partner_name = @mysql_real_escape_string($partner_name);
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<? include "common/head.htm"; ?>
	<script>
	    $.extend($.mobile, {
	        ajaxEnabled: false,
	    });
	</script>
</head>
<body>
<div data-role="page" id='<?=$page_id?>' style='min-width: <?=$body_min_width?>; overflow-x: scroll'>
	<div data-role="main" id="content">	
	<? 
		include "common/header.htm"; 
	?>
	<!-- BODY AREA -->
		<table width=100% border=0 cellpadding=0 cellspacing=0>
		<col width=200px></col><col width=100%></col>
		<tr>
			<td class='bg1' style="border-right: 1px solid #ddd" valign=top>
			<div style='width:200px'>
			<?
				include dirname(__FILE__).'/partner-pages/partner-menu.php';
			?>
			</div>
			</td>
			<td valign=top>
				<div id='page' style="text-align:left; padding: 10px; min-width:700px">
				<?
					if (file_exists($file)) {
						
						ini_set("display_errors", "1");
						error_reporting(E_ERROR & ~E_WARNING & ~E_NOTICE);
						
						include $file;
						
					} else {
						echo "";		
					}
				?>	
				</div>
			</td>
		</tr>
		</table>
	<!-- BODY END -->	
	<? include "common/footer.htm"; ?>
	</div>
<script type=text/javascript>	
	<? if ($post_script) echo $post_script; ?>
</script>	
</div>	
</body>
</html>