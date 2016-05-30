<?
	// --------------------------------
	$searchfor = $_REQUEST['searchfor'];
	$search = trim($_REQUEST['search']);
	
	if (!$searchfor) $searchfor = 'title';
	$db_search = mysql_real_escape_string($search);

	$where = "AND app.is_mactive <> 'D' AND app.is_active <> 'N'";
	if ($searchfor == "title" && $search) $where .= " AND app.app_title LIKE '%{$db_search}%'";
	if ($searchfor == "packageid" && $search) $where .= " AND app.app_packageid LIKE '{$db_search}%'";
	if ($searchfor == "mcode" && $search) $where .= " AND app.mcode = '{$db_search}'";
	$order_by = "ORDER BY app.id DESC";
	
	// --------------------------------
	// Paginavigator initialize	
	// --------------------------------
	$sql = "SELECT COUNT(*) as cnt FROM al_app_t app WHERE 1=1 {$where}";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$pages = new Paginator($row['cnt']);
	$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;

	// ---------------------------------------
	// publisher info
	// ---------------------------------------
	$sql = "SELECT app.*, 
				m.is_mactive as 'm_is_mactive',
				IF(app.exec_edate < CURRENT_DATE, 'N', 'Y') as 'exec_edate_check',
				
				IFNULL(IF(s.exec_time = LEFT(NOW(), 12), s.exec_hour_cnt, 0), 0) as 'app_exec_hour_cnt',
				IFNULL(IF(s.exec_time = CURRENT_DATE, s.exec_day_cnt, 0), 0) as 'app_exec_day_cnt',
				IFNULL(s.exec_tot_cnt, 0) as 'app_exec_tot_cnt',

				IF (app.exec_hour_max_cnt <= IFNULL(IF(s.exec_time = LEFT(NOW(), 12), s.exec_hour_cnt, 0), 0), 'N', 'Y') as 'exec_hour_check',
				IF (app.exec_day_max_cnt <= IFNULL(IF(s.exec_time = CURRENT_DATE, s.exec_day_cnt, 0), 0), 'N', 'Y') as 'exec_day_check',
				IF (IFNULL(app.exec_tot_max_cnt, 0) <= IFNULL(s.exec_tot_cnt, 0), 'N', 'Y')  as 'exec_tot_check'
				
			FROM al_app_t app 
				INNER JOIN al_merchant_t m ON app.mcode = m.mcode 
				LEFT OUTER JOIN al_app_exec_stat_t s ON app.app_key = s.app_key
			WHERE 1=1 {$where} {$order_by} {$limit}";
	$result = mysql_query($sql, $conn);

?>
	<style>
		/* line hover setup using mactive flag */
		.list tr:hover td 				{background:#eff}
		.list tr.mactive-N td 			{background:#ccc; color:#444}
		.list tr.mactive-N:hover td 	{background:#ddd}
		.list tr.mactive-D td 			{background:#aaa; color:#000}
		.list tr.mactive-D:hover td 	{background:#bbb}
		
		.list tr > * 	{height:25px; line-height:1em; padding: 4px 4px}
		
		.list .btn-td									{padding-left: 0px padding-right: 0px}
		.list .th_status, .list .btn-td .btn-wrapper	{width: 66px}
		.list .btn-td a									{padding:7px 4px; font-size: 10px; letter-spacing:0px; margin: 2px -2px 2px -1px; box-shadow:none;}
		
	</style>
	<t4 style='line-height: 40px'>전체 광고 목록</t4>
	<hr>
	<form onsubmit='return <?=$js_page_id?>.action.on_btn_search()'>
		<table border=0 cellpadding=0 cellspacing=0 width=100%>
		<tr><td id='btns-group' valign=top>
		</td><td valign=top align=right style='border-left: 1px solid #ddd'>
			
			<div style='width:300px; padding-top:10px; text-align: left'>
				<fieldset id="search-for" class='td-2-item' data-role="controlgroup" data-type="horizontal" style='margin-top: 3px;' data-mini=true init-value="<?=$searchfor?>" >
			        <input name="search-for" id="search-for-title" value="title" type="radio" />
			        <label for="search-for-title">제목</label>
			        <input name="search-for" id="search-for-packageid" value="packageid" type="radio" />
			        <label for="search-for-packageid">패키지ID</label>
			        <input name="search-for" id="search-for-mcode" value="mcode" type="radio" />
			        <label for="search-for-mcode">매체코드</label>
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
			<th width=30px>앱<br>적립</th>
			<th width=1px><div class='th_status'>관리<br>차단</div></th>
			<th>아이콘</th>
			<th width=80px>M코드</th>
			<th width=30px>M<br>상태</th>
			<th width=30px>레벨<br>제한</th>
			<th width=50px>공개<br>모드</th>
			<th width=65px>기간<br>종료</th>
			<th>환경</th>
			<th>제목</th>
			<th width=30px>원가</th>
			<th width=40px>시간당<br>수행수</th>
			<th width=40px>일일당<br>수행수</th>
			<th width=40px>총<br>수행수</th>
			<th>필터</th>
			<th>활성일</th>
			<th>등록일</th>
		</tr>
	</thead>
	<tbody>
	<?
		$arr_platform = array('A' => '<span style="color:blue;font-weight:bold">Android</span>', 'I' => '<span style="color:red;font-weight:bold">IOS</span>', 'W' => '<span style="color:orange;font-weight:bold;font-size:11px">WEB</span>');
		$arr_market = array('P' => '<span style="color:blue;font-weight:bold;font-size:11px">플레이스토어</span>', 'A' => '<span style="color:red;font-weight:bold;font-size:11px">앱스토어</span>', 'W' => '<span style="color:orange;font-weight:bold;font-size:11px">수행형</span>');
		$arr_exectype = array('I' => '<span style="color:blue;font-weight:bold">CPI</span>', 'E' => '<span style="color:green;font-weight:bold">CPE</span>', 'F' => '<span style="color:orange;font-weight:bold;font-size:11px">페북좋아요</span>');
		$arr_gender = array('M' => '남성', 'F' => '여성');
		
		$arr_active = array('Y' => '적립 가능', 'N' => '<span style="color:red; font-weight: bold">적립 불가</span>');
		$arr_mp_mactive = array('Y' => '연동', 'N' => '<span style="color:red; font-weight: bold">중지</span>', 'T' => '<span style="color:red; font-weight: bold">개발</span>', 'D' => '<span style="color:red; font-weight: bold">삭제</span>');
		$arr_public_mode = array('Y' => '공개', 'N' => '<span style="color:blue;font-weight:bold">비공개</span>');
		while ($appkey = mysql_fetch_assoc($result)) {
			
			$url_appkey = urlencode($appkey['app_key']);
			$url_mcode = urlencode($appkey['mcode']);
			$td_onclick = "onclick=\"mvPage('merchant-campaign-modify', null, {mcode: '{$appkey['mcode']}', appkey: '{$appkey['app_key']}'})\"";

			// 현재의 Publisher의 active상태 : Y / T / N 만 가능함.					
			$ar_btn_theme = array('a','a','a');
			if ($appkey['is_mactive'] == 'Y') $ar_btn_theme = array('b','a','a');
			else if ($appkey['is_mactive'] == 'N') $ar_btn_theme = array('a','b','a');
			else if ($appkey['is_mactive'] == 'D') $ar_btn_theme = array('a','a','b');
			
			// 필터 정보
			$filter = "";
			if ($appkey['app_gender'] != "") $filter .= ($filter?"<br>":"") . "성별: {$arr_gender[$appkey['app_gender']]}";
			if ($appkey['app_agefrom'] != "") $filter .= ($filter?"<br>":"") . "나이: {$appkey['app_agefrom']}~{$appkey['app_ageto']}";
			if ($appkey['exec_stime'] != "") {
				$shour = date("H", strtotime($appkey['exec_stime']));
				$ehour = date("H", strtotime($appkey['exec_etime']));
				$filter .= ($filter?"<br>":"") . "<span style='color:darkgreen;font-size:inherit'>시간: {$shour}~{$ehour}</span>";
			}
			if ($appkey['exec_edate'] != "") {
				$edate = admin_to_date($appkey['exec_edate']);
				$filter .= ($filter?"<br>":"") . "<span style='color:darkgreen;font-size:inherit'>만료: {$edate}</span>";
			}
			if ($filter) $filter = "<div style='text-align:left;padding: 0 5px; font-size:9px; line-height:1em'>{$filter}</div>";
			
			// Packageid (Optional display)
			$app_packageid = ($appkey['app_packageid'] ? "<div style='text-align:left; padding: 0 5px; color:#888; font-size:9px'>{$appkey['app_packageid']}</div>" : "");
			
			$exec_edate = $appkey['exec_edate'];
			if ($appkey['exec_edate_check'] == 'N') $exec_edate = '<span style="color:red; font-weight: bold">'.$appkey['exec_edate'].'</span>';

			// 수행완료에 따른 색상 처리
			$exec_hour_cnt = admin_number($appkey['app_exec_hour_cnt']) . '<br>' . admin_number($appkey['exec_hour_max_cnt'], "-", "0");
			if ($appkey['exec_hour_check'] == 'N') $exec_hour_cnt = '<span style="color:red; font-weight: bold">'. $exec_hour_cnt .'</span>';
			
			$exec_day_cnt = admin_number($appkey['app_exec_day_cnt']) . '<br>' . admin_number($appkey['exec_day_max_cnt'], "-", "0");
			if ($appkey['exec_day_check'] == 'N') $exec_day_cnt = '<span style="color:red; font-weight: bold">'. $exec_day_cnt .'</span>';
			
			$exec_tot_cnt = admin_number($appkey['app_exec_tot_cnt']) . '<br>' . admin_number($appkey['exec_tot_max_cnt'], "-", "0");
			if ($appkey['exec_tot_check'] == 'N') $exec_tot_cnt = '<span style="color:red; font-weight: bold">'. $exec_tot_cnt .'</span>';

			?>
			<tr style='cursor:pointer' id='line-<?=$appkey['app_key']?>' class='mactive-<?=$appkey['is_mactive']?>'>
				<td <?=$td_onclick?>><?=$appkey['id']?></td>
				<td <?=$td_onclick?>><?=$arr_active[$appkey['is_active']]?></td>
				<td class='btn-td'>
					<div class='btn-wrapper'>
						<a class='btn-<?=$appkey['app_key']?> btn-Y' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_appkey_mactive("<?=$appkey['mcode']?>", "<?=$appkey['app_key']?>", "Y")' data-theme='<?=$ar_btn_theme[0]?>' data-role='button' data-mini='true' data-inline='true'>정<br>상</a>
						<a class='btn-<?=$appkey['app_key']?> btn-N' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_appkey_mactive("<?=$appkey['mcode']?>", "<?=$appkey['app_key']?>", "N")' data-theme='<?=$ar_btn_theme[1]?>'  data-role='button' data-mini='true' data-inline='true'>중<br>지</a>
						<a class='btn-<?=$appkey['app_key']?> btn-D' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_appkey_mactive("<?=$appkey['mcode']?>", "<?=$appkey['app_key']?>", "D")' data-theme='<?=$ar_btn_theme[2]?>' data-role='button' data-mini='true' data-inline='true'>삭<br>제</a>
					</div>
				</td>
				<td <?=$td_onclick?>><img src='<?=$appkey['app_iconurl']?>' width=40px style='width:40px;height:40px;overflow:hidden;border-radius:0.5em;border:1px solid #888' /></td>
				<td <?=$td_onclick?>><?=$appkey['mcode']?></td>
				<td <?=$td_onclick?>><?=$arr_mp_mactive[$appkey['m_is_mactive']]?></td>
				<td <?=$td_onclick?>><?=admin_number($appkey['publisher_level'])?></td>
				<td <?=$td_onclick?>><?=$arr_public_mode[$appkey['is_public_mode']]?></td>
				<td <?=$td_onclick?>><?=$exec_edate?></td>
				
				<td <?=$td_onclick?>><?=$arr_platform[$appkey['app_platform']]?><br><?=$arr_market[$appkey['app_market']]?><br><?=$arr_exectype[$appkey['app_exec_type']]?></td>
				
				<td <?=$td_onclick?>><div style='text-align:left; padding: 0 5px; color:inherit'><?=$appkey['app_title']?></div><?=$app_packageid?></td>
				<td <?=$td_onclick?>><?=$appkey['app_merchant_fee']?></td>
				<td <?=$td_onclick?>><?=$exec_hour_cnt?></td>
				<td <?=$td_onclick?>><?=$exec_day_cnt?></td>
				<td <?=$td_onclick?>><?=$exec_tot_cnt?></td>
				<td <?=$td_onclick?>><?=$filter?></td>
				<td <?=$td_onclick?>><?=$appkey['is_active'] == 'Y' ? admin_to_date($appkey['last_active_time']).'<br>'.admin_to_time($appkey['last_active_time']) : ""?></td>
				<td <?=$td_onclick?>><?=admin_to_date($appkey['reg_date']).'<br>'.admin_to_time($appkey['reg_date'])?></td>
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
			on_btn_set_appkey_mactive: function(mcode, appkey, status) {
				var ar_param = {'mcode': mcode, 'appkey': appkey, 'ismactive': status};
				util.request(get_ajax_url('admin-campaign-set-mactive', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						
						$('.btn-'+appkey+'.btn-Y').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-'+appkey+'.btn-N').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-'+appkey+'.btn-D').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-'+appkey+'.btn-' + ar_param.ismactive).addClass('ui-btn-b ui-btn-up-b').attr('data-theme', 'b');
						
						$("#line-" + appkey).removeClass().addClass('mactive-' + status);
						
						if (ar_param.ismactive == 'D') $("#line-" + appkey).hide();
						
						toast('설정되었습니다.');
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
