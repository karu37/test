<?
	// --------------------------------
	$searchfor = $_REQUEST['searchfor'];
	$search = trim($_REQUEST['search']);
	
	if (!$searchfor) $searchfor = 'name';
	$db_search = mysql_real_escape_string($search);

	$where = "";
	if ($searchfor == "name" && $search) $where .= " AND a.name LIKE '%{$db_search}%'";
	$order_by = "ORDER BY a.id DESC";
	
	// --------------------------------
	// Paginavigator initialize	
	// --------------------------------
	$sql = "SELECT COUNT(*) as cnt FROM al_publisher_t a WHERE 1=1 {$where}";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$pages = new Paginator($row['cnt']);
	$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;

	// ---------------------------------------
	// publisher info
	// ---------------------------------------
	$sql = "SELECT * FROM al_publisher_t a {$order_by} {$limit}";
	$result = mysql_query($sql, $conn);

?>
	<style>
		#ctl-main-list tr:not(:last-child):hover td 			{background:#dff}
		#ctl-main-list tr.mactive-D:hover td 					{background:#888}
		
		#ctl-main-list tr	{line-height:25px}
		#ctl-main-list th	{padding: 2px 4px}
		#ctl-main-list td	{padding: 2px 4px}
		
		/* 비활성 (로그인 불가) */
		#ctl-main-list .mactive-N 				{background:#666666}
		#ctl-main-list .mactive-N td			{color: #bbb}
		
		/* 삭제 (로그인 불가) */
		#ctl-main-list .mactive-D 				{background:#666666}
		#ctl-main-list .mactive-D td			{color: #bbb}
		
		#ctl-main-list .mactive-Y .btn-restore	{display: none}	/* 활성화면     삭제버튼(디폴트), 복구(숨기기) */
		#ctl-main-list .mactive-N .btn-delete	{display: none}	/* 비활성상태면 삭제버튼(디폴트), 복구(숨기기) */
		#ctl-main-list .mactive-D .btn-delete	{display: none}	/* 삭제상태면   삭제버튼(숨기기), 복구(디폴트) */
		
		#ctl-main-list tr:hover td				{background:#dff}
		
	</style>
	<t4 style='line-height: 40px'>전체 Publisher 목록</t4>
	<hr>
	<form onsubmit='return <?=$js_page_id?>.action.on_btn_search()'>
		<table border=0 cellpadding=0 cellspacing=0 width=100%>
		<tr><td id='btns-group' valign=top>
		</td><td valign=top align=right style='border-left: 1px solid #ddd'>
			
			<div style='width:300px; padding-top:10px; text-align: left'>
				<fieldset id="search-for" class='td-2-item' data-role="controlgroup" data-type="horizontal" style='margin-top: 3px;' data-mini=true init-value="<?=$searchfor?>" >
			        <input name="search-for" id="search-for-name" value="name" type="radio" />
			        <label for="search-for-name">회사명</label>
			        <input name="search-for" id="search-for-id" value="id" type="radio" />
			        <label for="search-for-id">아이디</label>
			    </fieldset>	
			    <div class='ui-grid-a' style='padding:2px 0px; width: 300px; margin: 0 0 0 auto'>
			    	<div class='ui-block-a' style='width:200px'><input type=text name=search id=search data-clear-btn='true' value="<?=$_REQUEST['search']?>"  style='line-height: 25px;'/></div>
					<div class='ui-block-b' style='width:100px'><a href='#' onclick='<?=$js_page_id?>.action.on_btn_search()' data-role='button' data-mini='true'>검색</a></div>
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
	<hr>
	<div style='padding: 10px'>
  			<a href='?id=partner-add' data-role='button' data-mini='true' data-inline='true'>새 업체 등록</a>
	</div>		
	<hr>
	<br>
	
	<table class='single-line list'  cellpadding=0 cellspacing=0 width=100%>
	<thead>
		<tr>
			<th>Idx</th>
			<th width=1px><div class='th_status'>상태</div></th>
			<th></th>
			<th>Icon</th>
			<th>mcode</th>
			<th>PlatForm</th>
			<th>타입</th>
			<th>마켓</th>
			<th>제목/</th>
			<th>적립원가</th>
			<th>필터</th>
			<th>광고활성</th>
			<th>적립/수량</th>
			<th>활성일</th>
			<th>등록일</th>
		</tr>
	</thead>
	<tbody>
	<?
		while ($publisher = mysql_fetch_assoc($result)) {
			
			$url_pcode = urlencode($publisher['pcode']);
			$td_onclick = "onclick='window.location.href=\"?id=publisher-appkey-list&partnerid={$partner_id}&pcode={$url_pcode}\"'";

			// 현재의 Publisher의 active상태 : Y / T / N 만 가능함.					
			$ar_btn_theme = array('a','a','a');
			if ($publisher['is_mactive'] == 'Y') $ar_btn_theme = array('b','a','a');
			else if ($publisher['is_mactive'] == 'T') $ar_btn_theme = array('a','b','a');
			else if ($publisher['is_mactive'] == 'N') $ar_btn_theme = array('a','a','b');

			?>
			<tr style='cursor:pointer' id='line-p-<?=$publisher['pcode']?>'>
				<td <?=$td_onclick?>><?=$publisher['id']?></td>
				<td class='btn-td'>
					<div class='btn-wrapper'>
						<a class='btn-p-<?=$publisher['pcode']?> btn-Y' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_publisher_active("<?=$publisher['pcode']?>", "Y")' data-theme='<?=$ar_btn_theme[0]?>' data-role='button' data-mini='true' data-inline='true'>연<br>동</a>
						<a class='btn-p-<?=$publisher['pcode']?> btn-T' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_publisher_active("<?=$publisher['pcode']?>", "T")' data-theme='<?=$ar_btn_theme[1]?>'  data-role='button' data-mini='true' data-inline='true'>개<br>발</a>
						<a class='btn-p-<?=$publisher['pcode']?> btn-N' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_publisher_active("<?=$publisher['pcode']?>", "N")' data-theme='<?=$ar_btn_theme[2]?>'  data-role='button' data-mini='true' data-inline='true'>중<br>지</a>
					</div>
				</td>
				<td <?=$td_onclick?>><?=$publisher['pcode']?></td>
				<td <?=$td_onclick?>><?=$publisher['name']?></td>
				<td <?=$td_onclick?>><?=$publisher['offer_fee_rate']?></td>
				<td <?=$td_onclick?>><?=$publisher['level']?></td>
				<td><a href='#' onclick='goPage("dlg-publisher-modify", null, {publisher_code:"<?=$publisher['pcode']?>"})' data-theme='a' data-role='button' data-mini='true' data-inline='true'>정보<br>변경</a></td>
				<td><a href='#' onclick='<?=$js_page_id?>.action.on_btn_delete_partner_publisher_code("<?=$partner_id?>", "<?=$publisher['pcode']?>")' data-theme='a' data-role='button' data-mini='true' data-inline='true'>코드<br>제외</a></td>
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
			on_btn_delete: function(partnerid, listid) {
				var ar_param = {'partnerid': partnerid, mactive: 'D'};
				util.request(get_ajax_url('admin-partner-set-mactive', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						$("#list-" + listid).removeClass().addClass('mactive-D');
						$("#user-status-" + listid).html("삭제");
						toast('삭제되었습니다.');						
					} else util.Alert(js_data['msg']);
				});
			},
			on_btn_restore: function(partnerid, listid) {
				var ar_param = {'partnerid': partnerid, mactive: 'Y'};
				util.request(get_ajax_url('admin-partner-set-mactive', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						$("#list-" + listid).removeClass().addClass('mactive-Y');
						$("#user-status-" + listid).html("정상");
						toast('복구되었습니다.');						
					} else util.Alert(js_data['msg']);
				});
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
