<?
	// $partner_id, $partner_name 
	// $db_partner_id, $db_partner_name
	
	// --------------------------------
	$searchfor = $_REQUEST['searchfor'];
	if (!$searchfor) $searchfor = 'appkey';
	$search = trim($_REQUEST['search']);
	$db_search = mysql_real_escape_string($search);

	$where = "";
	if ($searchfor == "appkey" && $search) $where .= "AND a.guest_id IN (SELECT guest_id FROM guest_app_list WHERE app_key = '{$db_search}')";
	if ($searchfor == "company" && $search) $where .= "AND a.company LIKE '{$db_search}%'";
	if ($searchfor == "telno" && $search) $where .= "AND a.telno = '{$db_search}'";
	
	$order_by = "ORDER BY a.id DESC";
	// --------------------------------
	// Paginavigator initialize	
	$sql = "SELECT COUNT(*) as cnt FROM guest_user_t a WHERE a.partner_id = '{$db_partner_id}' {$where}";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$pages = new Paginator($row['cnt']);
	$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;
	// --------------------------------

	$sql = "SELECT a.*, COUNT(*) AS app_cnt FROM guest_user_t a LEFT OUTER JOIN guest_app_list b ON a.guest_id = b.guest_id WHERE a.partner_id = '{$db_partner_id}' {$where} GROUP BY a.guest_id {$order_by} {$limit}";
	$result = mysql_query($sql, $conn);
?>
	<style>
		#ctl-main-list tr:not(:last-child):hover td 			{background:#dff}
		
		#ctl-main-list tr	{line-height:25px}
		#ctl-main-list th	{padding: 2px 4px}
		#ctl-main-list td	{padding: 2px 4px}
		
		#ctl-main-list .deleted-Y 				{background:#666666}
		#ctl-main-list .deleted-Y td			{color: #bbb}
		
		#ctl-main-list .deleted-N .btn-restore	{display: none}
		#ctl-main-list .deleted-Y .btn-delete	{display: none}
		
	</style>
	<t4 style='line-height: 40px'>광고주 계정 목록</t4>
	<hr>
	<form onsubmit='return <?=$js_page_id?>.action.on_btn_search()'>
		<table border=0 cellpadding=0 cellspacing=0 width=100%>
		</td><td valign=top align=right style='border-left: 1px solid #ddd'>
			    <div class='ui-grid-b' style='padding:2px 0px; width: 430px;'>
			    	<div class='ui-block-a' style='width:230px; text-align: left'>
						<fieldset id="search-for" class='td-2-item' data-role="controlgroup" data-type="horizontal" style='margin-top: 3px;' data-mini=true init-value="<?=$searchfor?>" >
					        <input name="search-for" id="search-for-appkey" value="appkey" type="radio" />
					        <label for="search-for-appkey">광고키</label>
					        <input name="search-for" id="search-for-company" value="company" type="radio" />
					        <label for="search-for-company">회사명</label>
					        <input name="search-for" id="search-for-telno" value="telno" type="radio" />
					        <label for="search-for-telno">전화번호</label>
					    </fieldset>	
			    	</div>
			    	<div class='ui-block-b' style='width:120px'><input type=text name=search id=search data-clear-btn='true' value="<?=$_REQUEST['search']?>"  style='line-height: 25px;'/></div>
					<div class='ui-block-c' style='width:80px; padding-top: 1px'><a href='#' onclick='<?=$js_page_id?>.action.on_btn_search()' data-role='button' data-mini='true'>검색</a></div>
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
  			<a href='?id=guest-regist' data-role='button' data-mini='true' data-inline='true'>새 광고주 계정</a>
	</div>		
	<hr>
	<br>
	<table id='ctl-main-list' class='single-line' cellpadding=0 cellspacing=0 width=100%>
	<thead>
		<tr>
			<th>IDX</th>
			<th>상태</th>
			<th></th>
			<th>광고주 ID</th>
			<th>광고주 명</th>
			<th>앱 개수</th>
			<th>회사</th>
			<th>전화번호</th>
			<th>등록일</th>
			<th></th>
		</tr>	
	</thead>
	<tbody>
		<?
		$arr_deleted = array('Y' => '삭제', 'N' => '정상');
		while ($row = mysql_fetch_assoc($result)) {
			$id = $row['id'];
		?>
			<tr id='list-<?=$row['id']?>' class='deleted-<?=$row['is_deleted']?>'>
				<td><?=$row['id']?></td>
				<td id='user-status-<?=$row['id']?>'><?=$arr_deleted[$row['is_deleted']]?></td>
				<td>
					<a class='btn-delete' href='#' onclick='<?=$js_page_id?>.action.on_btn_delete("<?=$row['guest_id']?>", "<?=$row['id']?>")'  style='padding: 5px 4px'data-role='button' data-mini='true' data-inline='true'>삭제</a>
					<a class='btn-restore' href='#' onclick='<?=$js_page_id?>.action.on_btn_restore("<?=$row['guest_id']?>", "<?=$row['id']?>")'  style='padding: 5px 4px'data-role='button' data-mini='true' data-inline='true'>복구</a>
				</td>
				<td><?=$row['guest_id']?></td>
				<td><?=$row['guest_name']?></td>
				<td><?=$row['app_cnt']?></td>
				<td><?=$row['company']?></td>
				<td><?=to_phoneno($row['telno'])?></td>
				<td><?=admin_to_date($row['reg_date'])?></td>
				<td>
					<a href='?id=guest-modify&guestid=<?=$row['guest_id']?>' style='padding: 5px 4px' data-theme='b' data-role='button' data-mini='true' data-inline='true'>정보 수정</a>
					<a href='http://guest.aline-soft.co.kr/login.php?guestid=<?=urlencode($row['guest_id'])?>&guestpw=<?=urlencode($row['guest_pw'])?>' target=_blank style='padding: 5px 4px' data-theme='b' data-role='button' data-mini='true' data-inline='true'>광고주 로그인</a>
				</td>
				
			</tr>
		<?
		}
		?>
	</tbody>
	</table>
	
	<div style='padding: 10px'>
  			<a href='?id=guest-regist' data-role='button' data-mini='true' data-inline='true'>새 광고주 계정</a>
	</div>		
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
						
			on_btn_delete: function(guestid, listid) {
				var ar_param = {guestid: guestid, deleted: 'Y'};
				util.request(get_ajax_url('admin-guest-set-delete', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						$("#list-" + listid).removeClass('deleted-N').addClass('deleted-Y');
						$("#user-status-" + listid).html("삭제");
						toast('삭제되었습니다.');						
					} else util.Alert(js_data['msg']);
				});
			},
			on_btn_restore: function(guestid, listid) {
				var ar_param = {guestid: guestid, deleted: 'N'};
				util.request(get_ajax_url('admin-guest-set-delete', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						$("#list-" + listid).removeClass('deleted-Y').addClass('deleted-N');
						$("#user-status-" + listid).html("정상");
						toast('복구되었습니다.');						
					} else util.Alert(js_data['msg']);
				});
			},			
			
			on_btn_modify: function(guestid) {
				window.location.href = "?id=guest-modify&guestid=" + util.urlencode(guestid);
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
