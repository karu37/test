<?
/* 광고 목록 리스팅 대상

-- Merchant 기준
# al_merchant_t.is_active	: 해당 Merchat 전체 광고 허용/금지 (Merchant가 자기의 모든 광고들을 설정)
# al_merchant_t.is_deleted	: 해당 Merchat 전체 광고 허용/금지/삭제 (관리자가 Merchant의 모든 광고들을 설정)-Y/H/N
# al_app_t.is_active		: 해당 광고 허용/금지 ( Merchant가 app_key 설정 )
# al_app_t.is_deleted		: 해당 광고 허용/금지/삭제 (관리자가 app_key 설정 ) - Y/H/N

-- Merchant 기준
# al_merchant_t.is_active									: 해당 Merchant에게 허용/금지/Test상태 (관리자가 Merchant 허용/금지/테스트 설정)
# al_merchant_t.level <vs> al_app_t.merchant_level		: 해당 Merchant & 광고를 허용/금지 (관리자가 Level 설정)
# al_merchant_app_t.is_deleted								: 해당 Merchant & 광고를 허용/금지 (관리자가 설정)-Y/N
	# al_merchant_app_t.merchant_disabled 					: 해당 Merchant & 광고를 허용/금지 (Merchant가 설정)
# al_merchant_app_t.merchant_enabled, merchant_disabled	: 해당 Merchant & 광고를 허용/금지 (Merchant가 설정)

-- 광고 자체 오픈 시간 조정 (아래조건은 모두 AND)
# 	al_merchant_app_t.active_time		: 해당 Merchant & 광고를 허용/금지
# 	al_app_t.exec_sdate ~ exec_edate
# 	al_app_t.exec_stime ~ exec_etime
#	al_app_t.exec_hour_max_cnt <vs> al_app_exec_stat_t.live_exec_tmcnt
#	al_merchant_app_t.exec_day_max_cnt or al_app_t.exec_day_max_cnt <vs> al_app_exec_stat_t.live_exec_cnt
# 	al_merchant_app_t.exec_tot_max_cnt or al_app_t.exec_tot_max_cnt <vs> al_app_exec_stat_t.live_exec_totcnt

*/
	$partner_id = $_REQUEST['partnerid'];	// this'll be used for go back to partnerid's appkey list page.
	$mcode = $_REQUEST['mcode'];
	$db_mcode = mysql_real_escape_string($mcode);
	
	// --------------------------------
	$searchfor = $_REQUEST['searchfor'];
	$search = trim($_REQUEST['search']);
	if (!$searchfor) $searchfor = 'title';
	$db_search = mysql_real_escape_string($search);

	// $where = "AND app.is_active = 'Y' AND app.is_mactive = 'Y'";
	$where = "";
	
	// ------------------------------------------------------
	// A 면 is_active = Y AND is_mactive <> T 인 대상들 전체
	$listtype = ifempty($_REQUEST['listtype'], 'A');
	if ($listtype == 'A') $where .= " AND app.is_active = 'Y' AND app.is_mactive <> 'D'";
	else if ($listtype == 'B') $where .= " AND app.is_active <> 'Y' AND app.is_mactive <> 'D'";
	else if ($listtype == 'D') $where .= " AND app.is_mactive = 'D'";
	// ------------------------------------------------------
	
	if ($searchfor == "title" && $search) $where .= " AND app.app_title LIKE '%{$db_search}%'";
	
	$order_by = "ORDER BY IF(CONCAT(app.is_mactive, app.is_active) = 'YY', 1, 2) ASC, app.app_exec_type ASC, app.id DESC";
	// --------------------------------
	// Paginavigator initialize	
	$sql = "SELECT COUNT(*) as cnt FROM al_app_t app WHERE app.mcode = '{$db_mcode}' {$where}";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$pages = new Paginator($row['cnt']);
	$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;
	// --------------------------------

	$sql = "SELECT * FROM al_merchant_t WHERE mcode = '{$db_mcode}'";
	$row_merchant = @mysql_fetch_assoc(mysql_query($sql, $conn));

	$sql = "SELECT app.*,
				t.short_txt AS 'app_exec_type_name' 
			FROM al_app_t app
				LEFT OUTER JOIN string_t t ON t.type = 'app_exec_type' AND app.app_exec_type = t.code 
			WHERE app.mcode = '{$db_mcode}' {$where} {$order_by} {$limit}";
	$result = mysql_query($sql, $conn);
?>
	<style>
		.list tr:hover td 				{background:#eff}
		.list tr.app-active-N td 		{background:#eee; color:#444}
		.list tr.app-active-N:hover td 	{background:#ddd}
		
		.list tr > * 	{height:25px; line-height:1em; padding: 4px 4px}
				
		.btn-small-wrapper a	{font-size: 10px}
		.btn-wrapper			{width: 90px}
		.btn-wrapper a			{padding:7px 4px; letter-spacing:0px; margin: 2px -2px 2px -1px; box-shadow:none;}
	</style>
	<t4 style='line-height: 40px'><a href='?id=partner-detail&partnerid=<?=$partner_id?>'><b3 style='color:darkred'><?=$row_merchant['name']?></b3></a> 의 광고별 설정</t4>
	<hr>
	<form onsubmit='return <?=$js_page_id?>.action.on_btn_search()'>
		<table border=0 cellpadding=0 cellspacing=0 width=100%>
		<tr><td id='btns-group' valign=top>
			
			<fieldset id="list-type" data-theme='c' class='td-2-item' data-role="controlgroup" data-type="horizontal" data-mini=true init-value="<?=ifempty($_REQUEST['listtype'],'A')?>" >
		        <input name="list-type" id="list-type-normal" value="A" type="radio" onclick="window.location.href=window.location.href.set_url_param('listtype', 'A').del_url_param('page')" />
		        <label for="list-type-normal">적립가능 목록</label>
		        <input name="list-type" id="list-type-disabled" value="B" type="radio" onclick="window.location.href=window.location.href.set_url_param('listtype', 'B').del_url_param('page')" />
		        <label for="list-type-disabled">적립불가 목록</label>
		        <input name="list-type" id="list-type-deleted" value="D" type="radio" onclick="window.location.href=window.location.href.set_url_param('listtype', 'D').del_url_param('page')" />
		        <label for="list-type-deleted">삭제 목록</label>
		    </fieldset>			
			
			<table class='line-height-15px' border=0 cellpadding=0 cellspacing=3px>
			<tr>
				<td valign=top align=right><t4 style='padding-top:6px; line-height:22px'>광고사 코드 :<t4></td>
				<td valign=center>
					<t3 style='padding-top: 4px'>
						<?=$row_merchant['mcode']?>
					</t3>
				</td>
				<td></td>
			</tr><tr>	
				<td align=right><t4>Merchant 상태 :<t4></td>
				<td>
					<div class='ui-block-a' style='width:200px'>
						<fieldset class='fieldset-nopadding' id="merchant-active" data-role="controlgroup" data-type="horizontal" data-mini=true init-value="<?=$row_merchant['is_mactive']?>" >
					        <input name="merchant-active" id="merchant-active-Y" value="Y" type="radio" />
					        <label for="merchant-active-Y">연동</label>
					        <input name="merchant-active" id="merchant-active-T" value="T" type="radio" />
					        <label for="merchant-active-T">개발</label>
					        <input name="merchant-active" id="merchant-active-N" value="N" type="radio" />
					        <label for="merchant-active-N">중지</label>
					    </fieldset>
					</div>
					<div class='ui-block-b' style='padding-top:5px'>
						<a href='#' onclick='<?=$js_page_id?>.action.on_btn_save_merchant_active()' data-role='button' data-theme='a' data-transition="none" data-inline='true' data-mini='true'>상태 적용</a>
					</div>
				</td>
				<td>
					<div style='padding: 5px; color:#888; background: #eef; font-size:11px; border-radius:0.6em; border: 1px solid #88f'>
						* 연동 상태: 사용자 목록에 노출 + 사용자 적립 가능<br>
						* 개발 상태: 사용자 목록에 숨김 + 사용자 적립 가능<br>
						* 중지 상태: 사용자 목록에 숨김 + 사용자 적립 불가
					</div>
				</td>				
			</tr>
			</table>			
		<td valign=top align=right style='border-left: 1px solid #ddd'>
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
	<div style='text-align:left; padding-top:10px'>
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_new_app_campaign()' data-role='button' data-theme='b' data-transition="none" data-inline='true' data-mini='true'>새 광고 등록</a>
	</div>
	<div style="display:block; padding-top:20px; padding-left: 10px; font-size:22px; color: blue; font-weight: bold"><?=$row_merchant['name']?> - 총 : <?=number_format($pages->total_items)?> 건</div>
	<div class='ui-grid-a' style='padding:5px 10px; <?=$pages->num_pages <= 1 ? "display:none" : ""?>'>
		<div class='ui-block-a' style='width:70%; padding-top:5px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>
	<br>
	<table class='list single-line' cellpadding=0 cellspacing=0 width=100%>
	<thead>
		<tr>
			<th width=30px>IDX</th>
			<th>적립</th>
			<th width=1px>관리자<br>차단</th>
			<th>타입</th>
			<th>제목</th>
			<th>원가</th>
			<th>판매</th>
			<th>공개모드</th>
			<th width=60px>적립<br>활성일</th>
			<th width=60px>적립<br>불가일</th>
			<th width=60px></th>
		</tr>	
	</thead>
	<tbody>
		<?
		$arr_public_mode = array('Y' => '공개', 'N' => '<span style="color:blue;font-weight:bold">제한</span>');
		$arr_isactive = array('Y' => '가능', 'N' => '<span style="color:blue;font-weight:bold">불가</span>');
		
		while ($row = mysql_fetch_assoc($result)) {
			$id = $row['id'];
			
			// 현재의 Merchant의 active상태 : Y / T / N 만 가능함.					
			$ar_btn_theme = array('a','a','a','a');
			if ($row['is_mactive'] == 'Y') $ar_btn_theme[0] = 'b';
			else if ($row['is_mactive'] == 'N') $ar_btn_theme[1] = 'b';
			else if ($row['is_mactive'] == 'D') $ar_btn_theme[2] = 'b';
			else if ($row['is_mactive'] == 'T') $ar_btn_theme[3] = 'b';			
			
			$url_mcode = urlencode($mcode);
			$url_appkey = urlencode($row['app_key']);
			$td_onclick = "onclick=\"mvPage('merchant-campaign-modify', null, {mcode:'{$mcode}', appkey: '{$row['app_key']}'})\"";
			
			// 광고 노출여부 Flag
			$app_active = 'Y';
			if ( $row['is_active'] != 'Y' || $row['is_mactive'] != 'Y' )
				$app_active = 'N';
			
			?>
			<tr id='list-<?=$row['id']?>' class='app-active-<?=$app_active ?>' style='cursor:pointer'>
				<td <?=$td_onclick?>><?=$row['id']?></td>
				<td <?=$td_onclick?>><?=$arr_isactive[$row['is_active']]?></td>
				<td>
					<div class='btn-small-wrapper btn-wrapper'>
						<a class='btn-<?=$row['app_key']?> btn-Y' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_merchantapp_active("<?=$row_merchant['mcode']?>", "<?=$row['app_key']?>", "<?=$row['id']?>", "Y")' data-theme='<?=$ar_btn_theme[0]?>' data-role='button' data-mini='true' data-inline='true'>정<br>상</a>
						<a class='btn-<?=$row['app_key']?> btn-N' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_merchantapp_active("<?=$row_merchant['mcode']?>", "<?=$row['app_key']?>", "<?=$row['id']?>", "N")' data-theme='<?=$ar_btn_theme[1]?>' data-role='button' data-mini='true' data-inline='true'>중<br>지</a>
						<a class='btn-<?=$row['app_key']?> btn-D' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_merchantapp_active("<?=$row_merchant['mcode']?>", "<?=$row['app_key']?>", "<?=$row['id']?>", "D")' data-theme='<?=$ar_btn_theme[2]?>' data-role='button' data-mini='true' data-inline='true'>삭<br>제</a>
						<a class='btn-<?=$row['app_key']?> btn-T' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_merchantapp_active("<?=$row_merchant['mcode']?>", "<?=$row['app_key']?>", "<?=$row['id']?>", "T")' data-theme='<?=$ar_btn_theme[3]?>' data-role='button' data-mini='true' data-inline='true'>개<br>발</a>
					</div>
				</td>
				<td <?=$td_onclick?>><?=$row['app_exec_type_name']?></td>
				<td <?=$td_onclick?>><?=$row['app_title']?></td>
				<td <?=$td_onclick?>><?=number_format($row['app_merchant_fee'])?></td>
				<td <?=$td_onclick?>><?=number_format($row['app_tag_price'])?></td>
				<td <?=$td_onclick?>><?=$arr_public_mode[$row['is_public_mode']]?></td>
				<td <?=$td_onclick?>><?=admin_to_datetime($row['last_active_time'])?></td>
				<td <?=$td_onclick?>><?=admin_to_datetime($row['last_deactive_time'])?></td>
				<td>
					<a href='#' onclick='mvPage("dlgpage-merchantapp-config", null, {partnerid: "<?=$partner_id?>", mcode: "<?=$row_merchant['mcode']?>", appkey:"<?=$row['app_key']?>"})' data-theme='b' data-role='button' data-mini='true' data-inline='true'>Publisher별 광고 설정</a>
				</td>
			</tr>
			<?
		}
		?>
	</tbody>
	</table>
	
	<div style='padding: 5px; color:#888; background: #eef; font-size:11px; border-radius:0.6em; border: 1px solid #88f'>
		<b>[관리자 차단]</b><br>
		* 정상: 사용자 목록에 노출 + 사용자 적립 가능<br>
		* 중지: 사용자 목록에 숨김 + 사용자 적립 불가<br>
		* 삭제: 관리 목록에서 제외 (사용자 목록에 숨김 + 사용자 적립 불가)<br>
		* 개발: Publisher 개발사에게만 노출 + 적립되는 것처럼 동작 (실제 적립 안 함)
	</div>	
	
	<div style='padding:10px' class='ui-grid-a'>
		<div class='ui-block-a' style='width:70%; padding-top:20px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>

	<div style='text-align:left; padding-top:10px'>
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_new_app_campaign()' data-role='button' data-theme='b' data-transition="none" data-inline='true' data-mini='true'>새 광고 등록</a>
	</div>
	

<script type="text/javascript"> 

var <?=$js_page_id?> = function()
{
	// 외부에서 사용할 (Event Callback)함수 정의
	var ar_merchant_active = {'Y':'연동', 'T':'개발', 'N':'중지'};
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
						mcode: '<?=$mcode?>', 
						searchfor: util.get_item_value($("#search-for")), 
						search: $("#search").val()
				};
				window.location.href = '?' + util.json_to_urlparam(ar_param);
				return false;
			},
			on_btn_save_merchant_active: function() {

				var ar_param = {mcode: '<?=$mcode?>', isactive: util.get_item_value($("#merchant-active"))};
				util.request(get_ajax_url('admin-merchant-set-mactive', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						toast('저장되었습니다. (' + ar_merchant_active[ar_param.isactive] + ')');
					} else util.Alert(js_data['msg']);
				});
				
			},
			on_btn_set_merchantapp_active: function(mcode, appkey, listid, active) {
				
				var ar_param = {mcode: '<?=$mcode?>', 'appkey': appkey, isactive: active};
				util.request(get_ajax_url('admin-merchantapp-set-mactive', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						$("#list-" + listid).removeClass().addClass('mactive-' + active);
						$('.btn-'+appkey+'.btn-Y').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-'+appkey+'.btn-N').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-'+appkey+'.btn-D').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-'+appkey+'.btn-' + ar_param.isactive).addClass('ui-btn-b ui-btn-up-b').attr('data-theme', 'b');
						toast('저장되었습니다. (' + ar_merchant_active[ar_param.isactive] + ')');
					} else util.Alert(js_data['msg']);
				});
				
			},
			on_btn_new_app_campaign: function() {
				mvPage('merchant-campaign-add-app', null, {mcode: '<?=$mcode?>'});
			},
			on_btn_new_web_campaign: function() {
				mvPage('merchant-campaign-add-web', null, {mcode: '<?=$mcode?>'});
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
