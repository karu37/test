<?
/* 광고 목록 리스팅 대상

-- Merchant 기준
# al_merchant_t.is_active	: 해당 Merchat 전체 광고 허용/금지 (Merchant가 자기의 모든 광고들을 설정)
# al_merchant_t.is_deleted	: 해당 Merchat 전체 광고 허용/금지/삭제 (관리자가 Merchant의 모든 광고들을 설정)-Y/H/N
# al_app_t.is_active		: 해당 광고 허용/금지 ( Merchant가 app_key 설정 )
# al_app_t.is_deleted		: 해당 광고 허용/금지/삭제 (관리자가 app_key 설정 ) - Y/H/N

-- Publisher 기준
# al_publisher_t.is_active									: 해당 Publisher에게 허용/금지/Test상태 (관리자가 Publisher 허용/금지/테스트 설정)
# al_publisher_t.level <vs> al_app_t.publisher_level		: 해당 Publisher & 광고를 허용/금지 (관리자가 Level 설정)
# al_publisher_app_t.is_deleted								: 해당 Publisher & 광고를 허용/금지 (관리자가 설정)-Y/N
	# al_publisher_app_t.publisher_disabled 					: 해당 Publisher & 광고를 허용/금지 (Publisher가 설정)
# al_publisher_app_t.merchant_enabled, merchant_disabled	: 해당 Publisher & 광고를 허용/금지 (Merchant가 설정)

-- 광고 자체 오픈 시간 조정 (아래조건은 모두 AND)
# 	al_publisher_app_t.active_time		: 해당 Publisher & 광고를 허용/금지
# 	al_app_t.exec_sdate ~ exec_edate
# 	al_app_t.exec_stime ~ exec_etime
#	al_app_t.exec_hour_max_cnt <vs> al_app_exec_stat_t.live_exec_tmcnt
#	al_publisher_app_t.exec_day_max_cnt or al_app_t.exec_day_max_cnt <vs> al_app_exec_stat_t.live_exec_cnt
# 	al_publisher_app_t.exec_tot_max_cnt or al_app_t.exec_tot_max_cnt <vs> al_app_exec_stat_t.live_exec_totcnt

*/
	$partner_id = $_REQUEST['partnerid'];	// this'll be used for go back to partnerid's appkey list page.
	
	$pcode = $_REQUEST['pcode'];
	$db_pcode = mysql_real_escape_string($pcode);
	
	// --------------------------------
	$searchfor = $_REQUEST['searchfor'];
	$search = trim($_REQUEST['search']);
	if (!$searchfor) $searchfor = 'title';
	$db_search = mysql_real_escape_string($search);

	$where = "AND app.is_active = 'Y' AND app.is_mactive = 'Y'";
	if ($searchfor == "title" && $search) $where .= " AND app.app_title LIKE '%{$db_search}%'";
	
	$order_by = "ORDER BY app.id DESC";
	// --------------------------------
	// Paginavigator initialize	
	$sql = "SELECT COUNT(*) as cnt FROM al_app_t app WHERE 1=1 {$where}";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$pages = new Paginator($row['cnt']);
	$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;
	// --------------------------------

	$sql = "SELECT * FROM al_publisher_t WHERE pcode = '{$db_pcode}'";
	$row_publisher = @mysql_fetch_assoc(mysql_query($sql, $conn));

	// --------------------------------
	// IFNULL(b.app_offer_fee, FLOOR(a.app_merchant_fee * IFNULL(b.app_offer_fee_rate, p.offer_fee_rate) / 100) ) as 'publisher_fee'
	// 공급가 계산 : 1. al_publisher_app_t.app_offer_fee 가 최우선
	//               2. al_publisher_app_t.app_offer_fee_rate가 not null ==> floor( app_merchant_fee * al_publisher_app_t.app_offer_fee_rate / 100 )
	//				 3. al_publisher_t.offer_fee_rate 로 결정 floor( app_merchant_fee * al_publisher_t.offer_fee_rate / 100 )
	$sql = "SELECT app.*, 
				pa.app_offer_fee, pa.app_offer_fee_rate, pa.publisher_disabled, pa.active_time, pa.exec_hour_max_cnt, pa.exec_day_max_cnt, pa.exec_tot_max_cnt, 
				IFNULL(pa.is_mactive, 'Y') as 'pa_is_mactive',
				IFNULL(pa.app_offer_fee, FLOOR(app.app_merchant_fee * IFNULL(pa.app_offer_fee_rate, p.offer_fee_rate) / 100) ) AS 'publisher_fee', 
				
				m.name AS 'merchant_name', m.is_mactive as 'm_is_mactive',
				p.is_mactive as 'p_is_mactive',
				
				IF(app.publisher_level IS NULL OR p.level <= app.publisher_level, 'Y', 'N') as 'p_lvmode',
				
				IF (app.is_public_mode = 'Y', 
					IF(IFNULL(pa.merchant_disabled,'N')='N','Y', 'N'),
					IF(IFNULL(pa.merchant_enabled,'N')='Y', 'Y', 'N')) as 'pa_pmode',
				t.short_txt AS 'app_exec_type_name' 
			FROM al_app_t app
				LEFT OUTER JOIN al_publisher_app_t pa ON app.app_key = pa.app_key AND pcode = '{$db_pcode}' 
				INNER JOIN al_merchant_t m ON app.mcode = m.mcode 
				INNER JOIN al_publisher_t p ON p.pcode = '{$db_pcode}' 
				LEFT OUTER JOIN string_t t ON t.type = 'app_exec_type' AND app.app_exec_type = t.code 
			WHERE 1=1 {$where} {$order_by} {$limit}";
	$result = mysql_query($sql, $conn);
?>
	<style>
		.list .mactive-N 			{background:#999}
		.list .mactive-N td			{color: #ddd}
		
		.list tr:hover td 			{background:#dff}
		.list tr.mactive-N:hover td {background:#888}
		
		.list tr > * 	{height:25px; line-height:1em; padding: 4px 4px}
				
		.btn-small-wrapper a	{font-size: 10px}
		.btn-wrapper			{width: 50px}
		.btn-wrapper a			{padding:7px 4px; letter-spacing:0px; margin: 2px -2px 2px -1px; box-shadow:none;}
	</style>
	<t4 style='line-height: 40px'><a href='?id=partner-detail&partnerid=<?=$partner_id?>'><b3 style='color:darkred'><?=$row_publisher['name']?></b3></a> 의 광고별 설정</t4>
	<hr>
	<form onsubmit='return <?=$js_page_id?>.action.on_btn_search()'>
		<table border=0 cellpadding=0 cellspacing=0 width=100%>
		<tr><td id='btns-group' valign=top>
			
			<table class='line-height-15px' border=0 cellpadding=0 cellspacing=3px>
			<tr>
				<td valign=top align=right><t4 style='padding-top:6px; line-height:22px'>매체 코드 :<t4></td>
				<td valign=center>
					<t3 style='padding-top: 4px'>
						<?=$row_publisher['pcode']?>
					</t3>
				</td>
			</tr><tr>	
				<td valign=top align=right><t4 style='padding-top:6px'>기본 수수료 :<t4></td>
				<td valign=top>
					<div>
						<div class='ui-block-b' style='padding-top:0px'>
							
							<div class='ui-block-a' style='width:200px; padding:0 '>
								<div style='display: inline-block; width:100px'>
									<input type="number" id="publisher_offer_fee" value='<?=$row_publisher['offer_fee_rate']?>' />
								</div>
								<div style='display: inline-block; vertical-align:top; padding-top: 8px'>% (Percent)</div>
							</div>
							<div class='ui-block-b' style='padding: 0'>
								<a href='#' onclick='<?=$js_page_id?>.action.on_btn_save_publisher_offerfeerate()' data-role='button' data-theme='a' data-transition="none" data-inline='true' data-mini='true'>수수료 적용</a>
							</div>
		
						</div>
					</div>
				</td>
			</tr><tr>		
				<td align=right><t4>광고 송출 :<t4></td>
				<td>
					<div class='ui-block-a' style='width:200px'>
						<fieldset class='fieldset-nopadding' id="publisher-active" data-role="controlgroup" data-type="horizontal" data-mini=true init-value="<?=$row_publisher['is_mactive']?>" >
					        <input name="publisher-active" id="publisher-active-Y" value="Y" type="radio" />
					        <label for="publisher-active-Y">정상</label>
					        <input name="publisher-active" id="publisher-active-T" value="T" type="radio" />
					        <label for="publisher-active-T">테스트</label>
					        <input name="publisher-active" id="publisher-active-N" value="N" type="radio" />
					        <label for="publisher-active-N">중지</label>
					    </fieldset>
					</div>
					<div class='ui-block-b' style='padding-top:5px'>
						<a href='#' onclick='<?=$js_page_id?>.action.on_btn_save_publisher_active()' data-role='button' data-theme='a' data-transition="none" data-inline='true' data-mini='true'>상태 적용</a>
					</div>
				</td>
			</tr>
			</table>			
		<td valign=top align=right style='border-left: 1px solid #ddd'>
			<< 검색은 나중에 개발 >>
			<div style='width:300px; padding-top:10px; text-align: left'>
				<fieldset id="search-for" class='td-2-item' data-role="controlgroup" data-type="horizontal" style='margin-top: 3px;' data-mini=true init-value="<?=$searchfor?>" >
			        <input name="search-for" id="search-for-title" value="title" type="radio" />
			        <label for="search-for-title">앱 제목</label>
			    </fieldset>	
			    <div class='ui-grid-a' style='padding:2px 0px; width: 300px; margin: 0 0 0 auto;'>
			    	<div class='ui-block-a' style='width:200px'><input type=text name=search id=search data-clear-btn='true' value="<?=$_REQUEST['search']?>"  style='line-height: 25px;'/></div>
					<div class='ui-block-b' style='width:100px'><a href='#' onclick='<?=$js_page_id?>.action.on_btn_search()' data-role='button' data-mini='true'>검색</a></div>
				</div>
			</div>
			
		</td></tr></table>
	</form>
	<hr>
	<div style="display:block; padding-top:20px; padding-left: 10px; font-size:22px; color: blue; font-weight: bold">광고목록 - 총 : <?=number_format($pages->total_items)?> 건</div>
	<div class='ui-grid-a' style='padding:5px 10px; <?=$pages->num_pages <= 1 ? "display:none" : ""?>'>
		<div class='ui-block-a' style='width:70%; padding-top:5px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>
	<br>
	<table class='list single-line' cellpadding=0 cellspacing=0 width=100%>
	<thead>
		<tr>
			<th>IDX</th>
			<th width=1px>수신<br>여부</th>
			<th>M 이름</th>
			<th>타입</th>
			<th>제목</th>
			
			<th width=30px>M<br>상태</th>
			<th width=30px>P<br>상태</th>
			<th width=30px>M/P<br>모드</th>
			<th width=30px>M/LV<br>차단</th>
			
			<th>지정가</th>
			<th>지정율</th>
			<th>지정오픈</th>
			<th>시간수행</th>
			<th>일일수행</th>
			<th>총수행</th>
			<th width=1px></th>
			<th>원가</th>
			<th>공급금액</th>
		</tr>	
	</thead>
	<tbody>
		<?
		$arr_active = array('Y' => '적립 가능', 'N' => '적립 불가');
		$arr_mp_mactive = array('Y' => '연동', 'N' => '중지', 'T' => '개발', 'D' => '삭제');
		
		$arr_block_mode = array('Y' => '허용', 'N' => '<span style="color:red; font-weight: bold">차단</span>');
		while ($row = mysql_fetch_assoc($result)) {
			$id = $row['id'];
			
			// 현재의 Publisher의 active상태 : Y / T / N 만 가능함.					
			$ar_btn_theme = array('a','a');
			if ($row['pa_is_mactive'] == 'Y') $ar_btn_theme = array('b','a');
			else if ($row['pa_is_mactive'] == 'N') $ar_btn_theme = array('a','b');
			
			$td_onclick = "onclick=\"mvPage('merchant-campaign-modify', null, {mcode: '{$row['mcode']}', appkey: '{$row['app_key']}'})\"";
			?>
			<tr id='list-<?=$row['id']?>' class='mactive-<?=$row['pa_is_mactive']?>' style='cursor:pointer'>
				<td <?=$td_onclick?>><?=$row['id']?></td>
				<td>
					<div class='btn-small-wrapper btn-wrapper'>
						<a class='btn-<?=$row['app_key']?> btn-Y' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_publisherapp_active("<?=$row_publisher['pcode']?>", "<?=$row['app_key']?>", "<?=$row['id']?>", "Y")' data-theme='<?=$ar_btn_theme[0]?>' data-role='button' data-mini='true' data-inline='true'>정<br>상</a>
						<a class='btn-<?=$row['app_key']?> btn-N' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_publisherapp_active("<?=$row_publisher['pcode']?>", "<?=$row['app_key']?>", "<?=$row['id']?>", "N")' data-theme='<?=$ar_btn_theme[1]?>'  data-role='button' data-mini='true' data-inline='true'>중<br>지</a>
					</div>
				</td>
				<td <?=$td_onclick?>><?=$row['merchant_name']?></td>
				
				<td <?=$td_onclick?>><?=$row['app_exec_type_name']?></td>
				<td <?=$td_onclick?>><?=$row['app_title']?></td>
				
				<td <?=$td_onclick?>><?=$arr_mp_mactive[$row['m_is_mactive']]?></td>
				<td <?=$td_onclick?>><?=$arr_mp_mactive[$row['p_is_mactive']]?></td>
				<td <?=$td_onclick?>><?=$arr_block_mode[$row['pa_pmode']]?></td>
				<td <?=$td_onclick?>><?=$arr_block_mode[$row['p_lvmode']]?></td>
								
				<td <?=$td_onclick?>><?=admin_number($row['app_offer_fee'], "-", "0")?></td>
				<td <?=$td_onclick?>><?=admin_number($row['app_offer_fee_rate'], "-", "0")?></td>
				
				<td <?=$td_onclick?>><?=admin_to_datehour($row['active_time'])?></td>
				<td <?=$td_onclick?>><?=admin_number($row['exec_hour_max_cnt'], "-", "0")?></td>
				<td <?=$td_onclick?>><?=admin_number($row['exec_day_max_cnt'], "-", "0")?></td>
				<td <?=$td_onclick?>><?=admin_number($row['exec_tot_max_cnt'], "-", "0")?></td>
				
				<td><a href='#' onclick='goPage("dlg-publisherapp-config", null, {pcode: "<?=$pcode?>", appkey:"<?=$row['app_key']?>"})' data-theme='b' data-role='button' data-mini='true' data-inline='true'>공급<br>지정</a></td>
				<td <?=$td_onclick?>><?=number_format($row['app_merchant_fee'])?></td>
				<td <?=$td_onclick?>><b><?=admin_number($row['publisher_fee'])?></b></td>
				
			</tr>
			<?
		}
		?>
	</tbody>
	</table>
	
	<div style='padding:10px' class='ui-grid-a'>
		<div class='ui-block-a' style='width:70%; padding-top:20px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>

<script type="text/javascript"> 

var <?=$js_page_id?> = function()
{
	// 외부에서 사용할 (Event Callback)함수 정의
	var ar_publisher_active = {'Y':'정상', 'T':'테스트', 'N':'중지'};
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
						partnerid: '<?=$partner_id?>', 
						pcode: '<?=$pcode?>', 
						searchfor: util.get_item_value($("#search-for")), 
						search: $("#search").val()
				};
				window.location.href = '?' + util.json_to_urlparam(ar_param);
				return false;
			},
			on_btn_save_publisher_offerfeerate: function() {

				var ar_param = {pcode: '<?=$pcode?>', offerfeerate: $("#publisher_offer_fee").val()};
				util.request(get_ajax_url('admin-publisher-set-offerfeerate', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						toast('저장되었습니다.');						
					} else util.Alert(js_data['msg']);
				});
				
			},
			on_btn_save_publisher_active: function() {

				var ar_param = {pcode: '<?=$pcode?>', isactive: util.get_item_value($("#publisher-active"))};
				util.request(get_ajax_url('admin-publisher-set-mactive', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						toast('저장되었습니다. (' + ar_publisher_active[ar_param.isactive] + ')');
					} else util.Alert(js_data['msg']);
				});
				
			},
			on_btn_set_publisherapp_active: function(pcode, appkey, listid, active) {
				
				var ar_param = {pcode: '<?=$pcode?>', 'appkey': appkey, isactive: active};
				util.request(get_ajax_url('admin-publisherapp-set-mactive', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						$("#list-" + listid).removeClass().addClass('mactive-' + active);
						$('.btn-'+appkey+'.btn-Y').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-'+appkey+'.btn-N').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-'+appkey+'.btn-' + ar_param.isactive).addClass('ui-btn-b ui-btn-up-b').attr('data-theme', 'b');
						toast('저장되었습니다. (' + ar_publisher_active[ar_param.isactive] + ')');
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
