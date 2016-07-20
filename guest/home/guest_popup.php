<? 	ini_set( 'session.cookie_httponly', 1 );
	session_start();
	error_reporting(6135);
	
	header("Content-Type: text/html; charset=utf8");

	include dirname(__FILE__)."/php_lib/util.php";
	include dirname(__FILE__)."/php_lib/paginator.class.php";

	$page_id = $_REQUEST['id'];
	if ($page_id == "") $page_id = 'home';
	$js_page_id = str_replace("-", "_", $page_id);
	
	$file = dirname(__FILE__) . "/guest-pages/{$page_id}.php";

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
<div data-role="page" id='<?=$page_id?>' style='min-width: <?=$body_min_width?>; overflow-x: scroll'>
	<div data-role="main" id="content">	
	<? 
		include "common/header.htm"; 
	?>
	<!-- BODY AREA -->
		<table width=100% border=0 cellpadding=0 cellspacing=0>
		<col width=200px></col><col width=100%></col>
		<tr>
			<td valign=top>
				<div id='page' style="text-align:left; padding: 10px; min-width:800px">
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
	<? include "common/footer.htm"; ?>
	</div>
<script type=text/javascript>	
	<?=$post_script?>
</script>	
</div>	
</body>
</html>	
