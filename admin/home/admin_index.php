<? 	ini_set( 'session.cookie_httponly', 1 );
	session_start();
	error_reporting(E_ALL & ~E_NOTIC);

	header("Content-Type: text/html; charset=UTF-8");

	include dirname(__FILE__)."/../.admin_ips.php";
	include dirname(__FILE__)."/php_lib/util.php";
	include dirname(__FILE__)."/php_lib/paginator.class.php";

	if (!is_loginned()) goPage_die("login.php", "");
	
	$header_title = "A-Line 관리자";
	$page_id = "";
	if (isset($_REQUEST['id'])) $page_id = $_REQUEST['id'];
	
	if ($page_id == "") $page_id = 'home';
	$js_page_id = str_replace("-", "_", $page_id);
	
	$is_dlgpage = false;
	if (preg_match('/^dlgpage\-/usim', $page_id) || (isset($_REQUEST['dlgpage']) && $_REQUEST['dlgpage'] == 'Y')) $is_dlgpage = true;
	
	$file = dirname(__FILE__) . "/admin-pages/{$page_id}.php";

	if (!$is_dlgpage) {
		$menu_width = "180";
		$page_width = "900";
		$body_min_width = ($menu_width + $page_width) . "px";
	} else {
		$menu_width = "0";
		$page_width = "0";
		$body_min_width = ($menu_width + $page_width) . "px";
		
		$dlg_body_style = "background: #ddd; padding-top: 50px";
		$dlg_frame_style = "background: white; display: inline-block; margin: 0 auto";
	}

	// db 연결	
	$conn = dbConn();
	
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
<div data-role="page" id='<?=$page_id?>' style='min-width: <?=$body_min_width?>; overflow-x: scroll; <?=$dlg_body_style?>'>
	<div data-role="main" id="content" style="<?=$dlg_frame_style?>">	
	<?
		if (!$is_dlgpage) {
			include "common/header.htm"; 
		}
	?>
	<!-- BODY AREA -->
		<table width=100% border=0 cellpadding=0 cellspacing=0>
		<col width=<?=$menu_width?>px></col><col width=100%></col>
		<tr>
			<? if (!$is_dlgpage) { ?>
				<td class='bg1' style="border-right: 1px solid #ddd" valign=top>
				<div style='width:<?=$menu_width?>px; overflow-x: hidden'>
				<?
					include dirname(__FILE__).'/admin-pages/admin-menu.php';
				?>
				</div>
				</td>
			<? } ?>
			<td valign=top>
				<div id='page' style="text-align:left; padding: 10px; min-width:700px">
				<?
					if (file_exists($file)) {
						
						ini_set("display_errors", "1");
						error_reporting(E_ERROR & ~E_WARNING & ~E_NOTICE);
						
						include $file;
						
					} else {
						echo "Invalid - Request";		
					}
				?>	
				</div>
			</td>
		</tr>
		</table>
	<!-- BODY END -->	
	<? 
		if (!$is_dlgpage) {
			include "common/footer.htm"; 
		}
	?>
	</div>
<script type=text/javascript>	
	<?=$post_script?>
</script>	
</div>	
</body>
</html>