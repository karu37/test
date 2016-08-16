<?
	// $partner_id, $partner_name 
	// $db_partner_id, $db_partner_name

	// --------------------------------
	$b_no_query = false;
	
	$where = "";
	
	$appkey = $_REQUEST['appkey'];
	$searchfor = $_REQUEST['searchfor'];
	$search = trim($_REQUEST['search']);
	if (!$searchfor) $searchfor = 'adid';
	$db_appkey = mysql_real_escape_string($appkey);
	$db_search = mysql_real_escape_string($search);
	
	if ($appkey) $where .= "AND a.app_key = '{$db_appkey}'";
	if ($searchfor == "adid" && $search) $where .= " AND a.adid = '{$db_search}'";
	else if ($searchfor == "imei" && $search) $where .= " AND a.imei = '{$db_search}'";
	else if ($searchfor == "uid" && $search) $where .= " AND a.uid = '{$db_search}'";
	else $b_no_query = true;
	
	$order_by = "ORDER BY a.id DESC";

	if (!$b_no_query) {
		// --------------------------------
		// Paginavigator initialize	
		// --------------------------------
		$sql = "SELECT COUNT(*) as cnt FROM al_user_app_t a INNER JOIN al_app_t b ON a.app_key = b.app_key WHERE 1=1 {$where}";
		$row = mysql_fetch_assoc(mysql_query($sql, $conn));
		$pages = new Paginator($row['cnt']);
		$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;
	
		// ---------------------------------------
		// Total publisher_cnt, fee
		// ---------------------------------------
		$sql = "SELECT a.*, b.app_title FROM al_user_app_t a INNER JOIN al_app_t b ON a.app_key = b.app_key WHERE 1=1 {$where} {$order_by}";
		$result = mysql_query($sql, $conn);
	}
?>
	<style>
		/* line hover setup using mactive flag */
		.list tr:hover td 				{background:#eff}
		.list tr.mactive-N td 			{background:#999; color:#fff}
		.list tr.mactive-N:hover td 	{background:#888}
		.list tr.mactive-T td 			{background:#f90; color:#000}
		.list tr.mactive-T:hover td 	{background:#f80}
		
		.list tr th 		{font-size:11px; line-height:1em; padding: 4px 4px}
		.list tr td 		{font-size:12px; line-height:0.95em}

		.list .btn-td									{padding-left: 0px padding-right: 0px}
		.list .th_status, .list .btn-td .btn-wrapper	{width: 66px}
		.list .btn-td a									{padding:7px 4px; font-size: 10px; letter-spacing:0px; margin: 2px -2px 2px -1px; box-shadow:none;}
		
		.callback_resp		{width: 100% !important; min-height: 40px !important}
	</style>
<div style='width:100%'>
	<t4 style='line-height: 40px'>광고 참여 조회</t4>
	<hr>

	<hr>
	<form onsubmit='return <?=$js_page_id?>.action.on_btn_search()'>

		<table border=0 cellpadding=0 cellspacing=0 width=800px>
		<tr>
			<td><div style='text-size:14px; font-weight: bold; padding: 0px 10px 0 0; text-align:right'>대상 : </div></td>
			<td>
				<div style='width:550px; padding-top:0px; text-align: left'>
				    <div class='ui-grid-a' style='padding:2px 0px; margin: 0 0 0 auto'>
				    	<div class='ui-block-a' style='display: block; width:175px'>
				    		<fieldset id="search-for" class='td-2-item' data-role="controlgroup" data-type="horizontal" style='margin-top: 3px;' data-mini=true init-value="<?=$searchfor?>" >
						        <input name="search-for" id="search-for-adid" value="adid" type="radio" />
						        <label for="search-for-adid">ADID</label>
						        <input name="search-for" id="search-for-imei" value="imei" type="radio" />
						        <label for="search-for-imei">IMEI</label>
						        <input name="search-for" id="search-for-uid" value="uid" type="radio" />
						        <label for="search-for-uid">UID</label>
						    </fieldset>	
					    </div>
				    	<div class='ui-block-b' style='width:300px'><input type=text name=search id=search data-clear-btn='true' value="<?=$_REQUEST['search']?>"  style='line-height: 25px;'/></div>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<td><div style='text-size:14px; font-weight: bold; padding: 0px 10px 0 0; text-align:right'>광고 키 : </div></td>
			<td>
			    <div style='width:475px'><input type=text name=appkey id=appkey data-clear-btn='true' value="<?=$_REQUEST['appkey']?>"  style='line-height: 25px;'/></div>
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<div style='width:350px; padding-left: 50px; padding-top: 10px; padding-bottom: 5px'><a href='#' onclick='<?=$js_page_id?>.action.on_btn_search()' data-role='button' data-mini='false' data-theme='b'>조회</a></div>
			</td>
		</tr>
		</table>

	</form>
	<hr>
	
<? if (!$b_no_query) { ?>

	<div style="float:left; display:block; padding-top:20px; padding-right: 10px; font-size:22px; color: black; font-weight: bold">조회 결과</div>
	<div style="float:right; display:block; padding-top:20px; padding-left: 10px; font-size:12px; color: blue; font-weight: bold">총 : <?=number_format($pages->total_items)?> 건</div>
	<div style='clear:both'></div>
	<div class='ui-grid-a' style='padding:5px 10px; <?=$pages->num_pages <= 1 ? "display:none" : ""?>'>
		<div class='ui-block-a' style='width:70%; padding-top:5px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>
	<br>
	<table class='single-line list'  cellpadding=0 cellspacing=0 width=100%>
	<thead>
		<tr>
			<th width=30px>순서</th>
			<th width=200px>광고 제목</th>
			<th width=30px>상태</th>
			<th width=50px>참여<br>시작</th>
			<th width=50px>적립<br>요청</th>
			<th width=50px>적립<br>완료</th>
			<th width=50px>오류<br>발생</th>
			<th width=150px>오류<br>메시지</th>
			<th width=50px>콜백<br>호출</th>
			<th>콜백응답</th>
		</tr>
	</thead>
	<tbody>
	<?
		$idx = 0;
		$arr_status = array('A' => '참여<br>시작', 'B' => '적립<br>확인', 'D' => '적립<br>완료', 'F' => '오류');
		$arr_callbackdone = array('' => '-', 'Y' => '호출', 'E' => '오류', 'N' => '실패');
		while ( $row = mysql_fetch_assoc($result) ) {
			$idx ++;
			?>
			<tr>
				<td><?=$pages->limit_start + $idx?></td>
				<td><?=$row['app_title']?></td>
				<td><?=$arr_status[$row['status']]?></td>
				
				<td><?=admin_to_datetime($row['action_atime'])?></td>
				<td><?=admin_to_datetime($row['action_btime'])?></td>
				<td><?=admin_to_datetime($row['action_dtime'])?></td>
				<td><?=admin_to_datetime($row['action_ftime'])?></td>
				<td><?=$row['error'] . '<br>' . $row['error_msg']?></td>
				<td><?=$arr_callbackdone[$row['callback_done']] . "<br>" . admin_to_datetime($row['callback_time'])?></td>
				<td><textarea class='callback_resp' data-role='none' wrap="off" style='font-size:10px; font-family: Dotum; width:100%; line-height: 0.9em; border: none; max-height:400px; overflow-x: hidden; overflow-y: scroll; text-align:left'><?
					echo  htmlspecialchars("Callback URL : " . $row['callback_url'] . "\nPost Data : " . http_build_query(json_decode($row['callback_post'])) . "\nResponse : " . trim($row['callback_resp']));
				?></textarea></td>
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
	
<? } ?>	
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
		$('input#param-date').monthpicker();
	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
