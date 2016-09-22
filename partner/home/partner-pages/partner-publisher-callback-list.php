<?
	// $partner_id, $partner_name 
	// $db_partner_id, $db_partner_name
	
	// --------------------------------
	$searchfor = $_REQUEST['searchfor'];
	$search = trim($_REQUEST['search']);
	if (!$searchfor) $searchfor = 'name';
	$db_search = mysql_real_escape_string($search);

	$where = "";
	if ($searchfor == "name" && $search) $where .= " AND a.name LIKE '%{$db_search}%'";
	if ($searchfor == "code" && $search) $where .= " AND a.pcode LIKE '%{$db_search}%'";
	
	$order_by = "ORDER BY a.reg_date DESC";

	// --------------------------------
	// Paginavigator initialize	
	// --------------------------------
	$sql = "SELECT COUNT(*) as cnt FROM al_publisher_t a INNER JOIN al_partner_mpcode_t mp ON a.pcode = mp.pcode AND mp.type = 'P' WHERE mp.partner_id = '{$db_partner_id}' {$where}";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$pages = new Paginator($row['cnt']);
	$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;

	// ---------------------------------------
	// publisher info
	// ---------------------------------------
	$sql = "SELECT * FROM al_publisher_t a INNER JOIN al_partner_mpcode_t mp ON a.pcode = mp.pcode AND mp.type = 'P' WHERE mp.partner_id = '{$db_partner_id}'{$where} {$order_by} {$limit}";
	$result = mysql_query($sql, $conn);
?>
	<style>
		/* line hover setup using mactive flag */
		.list tr:hover td 				{background:#eff}
		.list tr.mactive-N td 			{background:#999; color:#fff}
		.list tr.mactive-N:hover td 	{background:#888}
		.list tr.mactive-T td 			{background:#f90; color:#000}
		.list tr.mactive-T:hover td 	{background:#f80}
				
		.list tr > * 		{height:40px; line-height:1em; padding: 4px 4px}
		
		.list .btn-td									{padding-left: 0px padding-right: 0px}
		.list .th_status, .list .btn-td .btn-wrapper	{width: 66px}
		.list .btn-td a									{padding:7px 4px; font-size: 10px; letter-spacing:0px; margin: 2px -2px 2px -1px; box-shadow:none;}
	</style>
<div style='width:800px'>
	<t4 style='line-height: 40px'>전체 Publisher 목록</t4>
	<hr>
	<form onsubmit='return <?=$js_page_id?>.action.on_btn_search()'>
		<table border=0 cellpadding=0 cellspacing=0 width=100%>
		<tr><td valign=top align=right style='border-left: 1px solid #ddd; border-right: 1px solid #ddd'>
			
			<div style='width:550px; padding-top:10px; text-align: left'>
			    <div class='ui-grid-b' style='padding:2px 0px; width: 380px; margin: 0 0 0 auto'>
			    	<div class='ui-block-a' style='width:120px'>
						<fieldset id="search-for" class='td-2-item' data-role="controlgroup" data-type="horizontal" style='margin-top: 2px;' data-mini=true init-value="<?=$searchfor?>" >
					        <input name="search-for" id="search-for-name" value="name" type="radio" />
					        <label for="search-for-name">이름</label>
					        <input name="search-for" id="search-for-code" value="code" type="radio" />
					        <label for="search-for-code">코드</label>
					    </fieldset>	
			    	</div>
			    	<div class='ui-block-b' style='width:150px'><input type=text name=search id=search data-clear-btn='true' value="<?=$_REQUEST['search']?>"  style='line-height: 24px;'/></div>
					<div class='ui-block-c' style='width:80px'><a href='#' onclick='<?=$js_page_id?>.action.on_btn_search()' data-role='button' data-mini='true'>검색</a></div>
				</div>
			</div>
			
		</td></tr></table>
	</form>
	<hr>
	<div style="display:block; padding-top:20px; padding-left: 10px; font-size:22px; color: blue; font-weight: bold">총 : <?=number_format($pages->total_items)?> 건</div>
	<div class='ui-grid-a' style='padding:5px 10px; <?=$pages->num_pages <= 1 ? "display:none" : ""?>'>
		<div class='ui-block-a' style='width:70%; padding-top:5px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>
	<br>
	
	<table class='single-line list'  cellpadding=0 cellspacing=0 width=100%>
	<thead>
		<tr>
			<th width=30px>순서</th>
			<th width=1px><div class='th_status'>상태</div></th>
			<th width=70px>등록일</th>
			<th>이름</th>
			<th>코드</th>
		</tr>
	</thead>
	<tbody>
	<?
		$idx = 0;
		$arr_status = array('Y' => '정상', 'T' => '<span style="color:blue; font-weight:bold">개발</span>', 'N' => '<span style="color:red; font-weight:bold">중지</span>');
		while ( $publisher = mysql_fetch_assoc($result) ) {
			$idx ++;
			
			// 현재의 Publisher의 active상태 : Y / T / N 만 가능함.					
			$ar_btn_theme = array('a','a','a');
			if ($publisher['is_mactive'] == 'Y') $ar_btn_theme = array('b','a','a');
			else if ($publisher['is_mactive'] == 'T') $ar_btn_theme = array('a','b','a');
			else if ($publisher['is_mactive'] == 'N') $ar_btn_theme = array('a','a','b');

			?>
			<tr style='cursor:pointer' id='line-p-<?=$publisher['pcode']?>' class="mactive-<?=$publisher['is_mactive']?>" onclick='mvPage("publisher-setup-callback", null, {pcode:"<?=$publisher['pcode']?>"})'>
				<td><?=$pages->limit_start + $idx?></td>
				<td><?=$arr_status[$publisher['is_mactive']]?></td>
				<td><?=admin_to_date($publisher['reg_date'])?></td>
				<td><?=$publisher['name']?></td>
				<td><?=$publisher['pcode']?></td>
			</tr>
			<?
		}
		
	?>
	</tbody>
	</table>
	<hr>
	<div style='padding:10px' class='ui-grid-a'>
		<div class='ui-block-a' style='width:70%; padding-top:20px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>
</div>

<script type="text/javascript"> 

var <?=$js_page_id?> = function()
{
	// 외부에서 사용할 (Event Callback)함수 정의
	var page = 
	{			
		action: {
			initialize: function() {
				util.initPage($('#page'));
				$("div[data-role='popup']").on("popupbeforeposition", function(){ util.initPage($(this)); });
			},
			
			on_btn_search: function() {
				var ar_param = {
						id: '<?=$page_id?>', 
						searchfor: util.get_item_value($("#search-for")), 
						search: $("#search").val()
				};
				window.location.href = '?' + util.json_to_urlparam(ar_param);
				return false;
			},
			
		},
	};		
	
	function setEvents() {
		$(document).on("pageinit", function(){page.action.initialize();} );
	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
