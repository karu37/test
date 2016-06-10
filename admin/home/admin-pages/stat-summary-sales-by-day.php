<?
	$where = "";
	$day = $_REQUEST['day'];
	if (!$day) $day = date("Y-m-d");
	
	$db_date = mysql_real_escape_string($day);
	if ($day) $where = " AND a.m_reg_day = '{$db_date}'";
	
	// --------------------------------
	// Paginavigator initialize	
	$sql = "SELECT COUNT(*) as cnt FROM al_user_app_saving_t a WHERE 1=1 {$where}";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$pages = new Paginator($row['cnt']);
	$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;
	// --------------------------------
	
	$sql = "SELECT a.id, a.adid, c.app_iconurl, c.app_title, a.merchant_fee, a.publisher_fee, a.m_reg_date, a.mcode, m.name as 'm_name', a.pcode, p.name as 'p_name'
			FROM al_user_app_saving_t a
				LEFT OUTER JOIN al_app_t c ON a.app_key = c.app_key
				LEFT OUTER JOIN al_publisher_t p ON a.pcode = p.pcode
				LEFT OUTER JOIN al_merchant_t m ON a.mcode = m.mcode
			WHERE 1=1 {$where} ORDER BY a.id DESC {$limit}";
	$result = mysql_query($sql, $conn);
?>
	<style>
		.main-list tr > * 	{height:25px; line-height:1em; padding: 4px 4px}
		.main-list span 	{ padding: 5px 10px }
		
		.main-list .type-A	{ background: #fff }
		.main-list .type-P	{ background: #ffe }
		.main-list .type-B	{ background: #eee }
	</style>
	<t4 style='line-height: 40px'>Merchant 적립 내역</t4>
	<hr>
	<div>
			<div style='display:inline-block; width:70px; vertical-align:top; padding-top:12px; padding-left: 20px;'>날짜 선택 : </div>
			<div style="display:inline-block">
				<div class='ui-grid-a'>
					<div class='ui-block-a' style='width:120px; padding-top:5px'><input type="text" data-role="date" id='param-date' data-clear-btn='true' value="<?=$day?>"/></div>
					<div class='ui-block-b' style='width:300px; padding-left:5px'>
						<a href='#' onclick="$('#param-date').val('<?=date("Y-m-d", strtotime($day. " -1 day"))?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'>이전날</a>
						<a href='#' onclick="$('#param-date').val('<?=date("Y-m-d")?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'>오늘</a>
						<a href='#' onclick="$('#param-date').val('<?=date("Y-m-d", strtotime($day. " +1 day"))?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'>다음날</a>
					</div>
				</div>
				<script>$('#param-date').change(function(){ window.location.href=g_admin_util.set_url_param("<?=$_SERVER['REQUEST_URI']?>", "day", $(this).val()); });</script>
			</div>
	</div>		
	
	<hr>
	<div style="display:block; padding-top:20px; padding-left: 10px; font-size:22px; color: blue; font-weight: bold">총 : <?=number_format($pages->total_items)?> 건</div>
	<div class='ui-grid-a' style='padding:5px 10px; <?=$pages->num_pages <= 1 ? "display:none" : ""?>'>
		<div class='ui-block-a' style='width:70%; padding-top:5px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>
	<hr>
	<br>
	<table class='main-list single-line' cellpadding=0 cellspacing=0 width=100%>
	<thead>
		<tr>
			<th width=1% >No</th>
			<th width=1% > </th>
			<th width=10% >ADID</th>
			<th width=5%>Merchant</th>
			<th width=7%>제목</th>
			<th width=5%>Publisher</th>
			<th width=1%>M금액</th>
			<th width=1%>P금액</th>
			<th width=5%>시간</th>
		</tr>	

	</thead>
	<tbody>
		<?
		
		$arr_app_type = array('I' => '설치형', 'E' => '실행형', 'W' => '웹형', 'R' => '가입형', 'C' => '기타');
		$arr_market_name = array('A' => 'AppStore', 'P' => 'PlayStore', 'T' => 'T-Store', 'O' => 'OlleStore', 'U' => 'U+Store', 'N' => 'NaverStore', 'X' => "-");
		$arr_status = array('A' => '참여하기 실행', 'B' => '적립하기 실행', 'D' => '적립완료', 'F' => '적립실패');
		
		while ($row = mysql_fetch_assoc($result)) 
		{
			$id = $row['id'];
			$merchant_fee = number_format($row['merchant_fee']) . " 원";
			$publisher_fee = number_format($row['publisher_fee']) . " 원";
			$disp_time = admin_to_datetime($row['m_reg_date']);
			
			echo "<tr>
		        <td align=center>{$id}</td>
		        <td align=center><img src='{$row['app_iconurl']}' width=40px /></td>
		        <td align=center>{$row['adid']}</td>
		        <td>{$row['m_name']}</td>
		        <td>{$row['app_title']}</td>
		        <td>{$row['p_name']}</td>
		        <td align=center>{$merchant_fee}</td>
		        <td align=center>{$publisher_fee}</td>
		        <td align=center>{$disp_time}</td>
			</tr>";
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
			on_btn_appstatus: function(sz_status) {
				window.location.href = '?id=<?=$page_id?>&userid=<?=urlencode($user_id)?>&status=' + sz_status;
			},
			on_btn_saving_force: function(user_app_id) {
				var ar_param = {userid: '<?=$user_id?>', userappid: user_app_id};
				
				util.MessageBox("알림", "강제 적립하시겠습니까 ?", function(sel) {
					if (sel == 1) {
						util.request(get_ajax_url('admin-user-force-appsaving', ar_param), function(sz_data) {
							
							var js_data = util.to_json(sz_data);
							if (js_data['result']) {
								util.Alert('알림', js_data['msg'], function() {
									window.location.reload();
								});
							} 
							else alert(js_data['msg']);
							
						});
					}
				});				
			},
			
		},
	};		
	function setEvents() {
		$(document).on("pageinit", function(){ 
			page.action.initialize();} );
	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
