<?
	$appkey = $_REQUEST['appkey'];
	
	$db_appkey = mysql_real_escape_string($appkey);
	
	$sql = "SELECT * FROM al_app_t WHERE app_key = '{$db_appkey}'";
	$row_appkey = @mysql_fetch_assoc(mysql_query($sql, $conn));
	
	$where = "AND app_key = '{$db_appkey}'";
	// --------------------------------
	// Paginavigator initialize	
	// --------------------------------
	$sql = "SELECT COUNT(*) as cnt FROM al_app_adid_uploaded_t WHERE 1=1 {$where}";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$pages = new Paginator($row['cnt'], 7, array(500, 1000, "All"));
	$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;
	
	$sql = "SELECT adid FROM al_app_adid_uploaded_t WHERE 1=1 $where $limit";
	$result = mysql_query($sql, $conn);
	
?>
	<t4 style='line-height: 40px; color:darkred'><?=$row_appkey['app_title']?> <n4 style='color:#888'>의 업로드된 ADID 목록</n4></t4>
	<hr>
	<div style="display:block; padding-top:20px; padding-left: 10px; font-size:22px; color: blue; font-weight: bold">총 : <?=number_format($pages->total_items)?> 건</div>
	<div class='ui-grid-a' style='padding:5px 10px; <?=$pages->num_pages <= 1 ? "display:none" : ""?>'>
		<div class='ui-block-a' style='width:70%; padding-top:5px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>
	<hr>
	<table class='single-line list' cellpadding=0 cellspacing=0 width=400px>
	<thead>
		<tr>
			<th>ADID</th>
		</tr>
	</thead>
	<tbody>
		<?
		while ($row = mysql_fetch_assoc($result)) {
		?>
			<tr><td><?=$row['adid']?></td></tr>
		<?
		}
		?>
	</tbody>
	</table>
	<br>

	<div class='ui-grid-a' style='padding:5px 10px; <?=$pages->num_pages <= 1 ? "display:none" : ""?>'>
		<div class='ui-block-a' style='width:70%; padding-top:5px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>
