<?
	// $partner_id, $partner_name 
	// $db_partner_id, $db_partner_name
	
	// ---------------------------------------	
	// During-Month (Daily) Navigation
	// ---------------------------------------	
	$date = $_REQUEST['date'];
	if (!$date) $date = date("Y-m-d");

	$year = date("Y", strtotime($date));
	$month = date("m", strtotime($date));
	
	// 해당월의 마지막 일을 얻어옴
	$sql = "SELECT DAY(LAST_DAY('{$year}-{$month}-01')) as 'last_day'";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$last_day = intval($row['last_day']);
	
	// --------------------------------
	$searchfor = $_REQUEST['searchfor'];
	$search = trim($_REQUEST['search']);
	if (!$searchfor) $searchfor = 'name';
	$db_search = mysql_real_escape_string($search);

	$where = "";
	// $where .= "AND a.is_mactive " . (ifempty($_REQUEST['listtype'], 'A') == 'A' ? " <> 'N'" : " = 'N'");
	if ($searchfor == "name" && $search) $where .= " AND a.name LIKE '%{$db_search}%'";
	if ($searchfor == "code" && $search) $where .= " AND a.mcode LIKE '%{$db_search}%'";
	
	$order_by = "ORDER BY a.reg_date DESC";

	// --------------------------------
	// Paginavigator initialize	
	// --------------------------------
	$sql = "SELECT COUNT(*) as cnt FROM al_merchant_t a INNER JOIN al_partner_mpcode_t mp ON a.mcode = mp.mcode AND mp.type = 'M' WHERE mp.partner_id = '{$db_partner_id}' {$where}";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$pages = new Paginator($row['cnt']);
	$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;

	// ---------------------------------------
	// Total merchant_cnt, fee
	// ---------------------------------------
	$sql = "SELECT SUM(c.merchant_cnt) as 'merchant_cnt', SUM(c.merchant_fee) as 'merchant_fee'
			FROM al_merchant_t a 
				INNER JOIN al_partner_mpcode_t mp ON a.mcode = mp.mcode AND mp.type = 'M' 
				LEFT OUTER JOIN al_summary_sales_m_t c ON a.mcode = c.mcode AND c.reg_day = '{$year}-{$month}-01'
			WHERE mp.partner_id = '{$db_partner_id}' {$where}";
	$result = mysql_query($sql, $conn);
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$total_merchant_cnt = $row['merchant_cnt'];
	$total_merchant_fee = $row['merchant_fee'];

	// ---------------------------------------
	// publisher info
	// ---------------------------------------
	$sql = "SELECT a.*, count(distinct b.app_key) as 'appkey_cnt', c.merchant_cnt, c.merchant_fee
			FROM al_merchant_t a 
				INNER JOIN al_partner_mpcode_t mp ON a.mcode = mp.mcode AND mp.type = 'M' 
				LEFT OUTER JOIN al_app_t b ON a.mcode = b.mcode
				LEFT OUTER JOIN al_summary_sales_m_t c ON a.mcode = c.mcode AND c.reg_day = '{$year}-{$month}-01'
			WHERE mp.partner_id = '{$db_partner_id}' {$where} 
			GROUP BY a.mcode 
			{$order_by} {$limit}";
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
		.list tr.sum > * 	{font-weight: bold; background-color:#efefff; color: blue}

		.list .btn-td									{padding-left: 0px padding-right: 0px}
		.list .th_status, .list .btn-td .btn-wrapper	{width: 66px}
		.list .btn-td a									{padding:7px 4px; font-size: 10px; letter-spacing:0px; margin: 2px -2px 2px -1px; box-shadow:none;}
	</style>
<div style='width:800px'>
	<t4 style='line-height: 40px'>Merchant 실적</t4>
	<hr>

	<hr>
	<form onsubmit='return <?=$js_page_id?>.action.on_btn_search()'>
		<table border=0 cellpadding=0 cellspacing=0 width=100%>
		<tr><td id='btns-group' valign=top>
			
			<div style="display:inline-block; padding-top:3px">
				<div class='ui-grid-a'>
					<!-- 년도 선택 -->
					<div class='ui-block-a' style='width:120px; padding-top:5px'>
						<input type="text" data-role='text' id='param-date' style='text-align: center' value="<?=$year . '-' . $month?>" />
					</div>
					<div class='ui-block-b' style='width:200px; padding-left:5px'>
						<a href='#' onclick="$('#param-date').val('<?=date("Y-m", strtotime("$year-$month-01". " -1 month"))?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'><<</a>
						<a href='#' onclick="$('#param-date').val('<?=date("Y-m")?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'>이번달</a>
						<a href='#' onclick="$('#param-date').val('<?=date("Y-m", strtotime("$year-$month-01". " 1 month"))?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'>>></a>
					</div>
				</div>
				<script>$('#param-date').change(function(){ if ($(this).val() != '<?=$year.'-'.$month?>') window.location.href=location.href.del_url_param("page").set_url_param("date", $(this).val()); });</script>
			</div>			

		</td><td valign=top align=right style='border-left: 1px solid #ddd'>
			
			<div style='width:450px; padding-top:0px; text-align: left'>

			    <div class='ui-grid-b' style='padding:2px 0px; width: 400px; margin: 0 0 0 auto'>
			    	<div class='ui-block-a' style='display: block; width:115px'>
			    		<fieldset id="search-for" class='td-2-item' data-role="controlgroup" data-type="horizontal" style='margin-top: 3px;' data-mini=true init-value="<?=$searchfor?>" >
					        <input name="search-for" id="search-for-name" value="name" type="radio" />
					        <label for="search-for-name">이름</label>
					        <input name="search-for" id="search-for-code" value="code" type="radio" />
					        <label for="search-for-code">코드</label>
					    </fieldset>	
				    </div>
			    	<div class='ui-block-b' style='width:200px'><input type=text name=search id=search data-clear-btn='true' value="<?=$_REQUEST['search']?>"  style='line-height: 25px;'/></div>
					<div class='ui-block-c' style='width:60px'><a href='#' onclick='<?=$js_page_id?>.action.on_btn_search()' data-role='button' data-mini='true'>검색</a></div>
				</div>
			</div>
			
		</td></tr></table>
	</form>
	<hr>
	<div style="float:left; display:block; padding-top:20px; padding-right: 10px; font-size:22px; color: black; font-weight: bold"><?="{$year}년 {$month}월"?> 실적</div>
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
			<th width=1px><div class='th_status'>상태</div></th>
			<th width=70px>등록일</th>
			<th>이름</th>
			<th>mcode</th>
			<th width=40px>광고수</th>
			<th>적립 건수</th>
			<th>적립 실적</th>
		</tr>
	</thead>
	<tbody>
		<tr class='sum'>
			<td colspan=6 style='text-align:left; padding-left:10px'>전체 합</td>
			<td><?=admin_number($total_merchant_cnt)?></td>
			<td><?=admin_number($total_merchant_fee)?></td>
		</tr>
	<?
		$idx = 0;
		$arr_status = array('Y' => '정상', 'T' => '<span style="color:blue; font-weight:bold">개발</span>', 'N' => '<span style="color:red; font-weight:bold">중지</span>');
		while ( $merchant = mysql_fetch_assoc($result) ) {
			$idx ++;
			
			$url_mcode = urlencode($merchant['mcode']);
			$td_onclick = "onclick='window.location.href=\"?id=stat-summary-partner-merchant-sales-month&date={$date}&mcode={$url_mcode}\"'";
			?>
			<tr style='cursor:pointer' id='line-m-<?=$merchant['mcode']?>' class="mactive-<?=$merchant['is_mactive']?>">
				<td <?=$td_onclick?>><?=$pages->limit_start + $idx?></td>
				<td <?=$td_onclick?>><?=$arr_status[$merchant['is_mactive']]?></td>
				<td <?=$td_onclick?>><?=admin_to_date($merchant['reg_date'])?></td>
				<td <?=$td_onclick?>><?=$merchant['name']?></td>
				<td <?=$td_onclick?>><?=$merchant['mcode']?></td>
				<td <?=$td_onclick?>><?=admin_number($merchant['appkey_cnt'])?></td>
				<td <?=$td_onclick?>><b><?=admin_number($merchant['merchant_cnt'])?></b></td>
				<td <?=$td_onclick?>><b><?=admin_number($merchant['merchant_fee'])?></b></td>
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
			on_btn_set_merchant_active: function(mcode, status)
			{
				var ar_param = {
					'mcode' : mcode,
					'isactive' : status
				};
				util.post(get_ajax_url('admin-merchant-set-mactive'), ar_param, function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						toast('변경되었습니다.');
						$('.btn-m-'+mcode+'.btn-Y').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-m-'+mcode+'.btn-T').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-m-'+mcode+'.btn-N').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-m-'+mcode+'.btn-' + ar_param.isactive).addClass('ui-btn-b ui-btn-up-b').attr('data-theme', 'b');
						
						$('#line-m-'+mcode).removeClassMatch(/mactive\-/g).addClass('mactive-'+status);
					} else util.Alert(js_data['msg']);
				});
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
