<?
/* 광고 목록 리스팅 대상

** 개발 테스트용 앱 (T)는 대상에서 제외한다

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

	$where = ""; // "AND app.is_active = 'Y'";
	if ($searchfor == "title" && $search) $where .= " AND app.app_title LIKE '%{$db_search}%'";
	
	// list type filter
	$listtype = ifempty($_REQUEST['listtype'], 'A');
	if ($listtype == 'A') $where .= " AND app.is_active = 'Y'";
	else $where .= " AND (app.is_active <> 'Y')";
	
	$order_by = "ORDER BY IF(CONCAT(app.is_mactive) = 'Y', 1, 2) ASC, app.id DESC";
	// --------------------------------
	// Paginavigator initialize	
	$sql = "SELECT COUNT(*) as cnt FROM al_app_t app WHERE app.is_mactive <> 'T' {$where}";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$pages = new Paginator($row['cnt']);
	$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;
	// --------------------------------

	$sql = "SELECT * FROM al_publisher_t WHERE pcode = '{$db_pcode}'";
	$row_publisher = @mysql_fetch_assoc(mysql_query($sql, $conn));
	
	
	// NULL이 Y인경우 (FALSE에 Y를 함), 조건은 거짓으로 만듬 ==> IF (COMPARE-WITH-NULL, 'N', 'Y')
	
	// --------------------------------
	// IFNULL(b.app_offer_fee, FLOOR(a.app_merchant_fee * IFNULL(b.app_offer_fee_rate, p.offer_fee_rate) / 100) ) as 'publisher_fee'
	// 공급가 계산 : 1. al_publisher_app_t.app_offer_fee 가 최우선
	//               2. al_publisher_app_t.app_offer_fee_rate가 not null ==> floor( app_merchant_fee * al_publisher_app_t.app_offer_fee_rate / 100 )
	//				 3. al_publisher_t.offer_fee_rate 로 결정 floor( app_merchant_fee * al_publisher_t.offer_fee_rate / 100 )
	$sql = "SELECT app.*, 
				pa.app_offer_fee, 
				pa.app_offer_fee_rate, 
				pa.publisher_disabled, 
				pa.active_time, 
				
				IFNULL(IF(LEFT(s.exec_time, 13) = LEFT(NOW(), 13), s.exec_hour_cnt, 0), 0) as 'app_exec_hour_cnt',
				IFNULL(IF(DATE(s.exec_time) = CURRENT_DATE, s.exec_day_cnt, 0), 0) as 'app_exec_day_cnt',
				IFNULL(s.exec_tot_cnt, 0) as 'app_exec_tot_cnt',
				
				pa.exec_hour_max_cnt as 'pa_exec_hour_max_cnt',
				pa.exec_day_max_cnt as 'pa_exec_day_max_cnt',
				pa.exec_tot_max_cnt as 'pa_exec_tot_max_cnt',

				IFNULL(IF(LEFT(ps.exec_time, 13) = LEFT(NOW(), 13), ps.exec_hour_cnt, 0), 0) as 'ps_exec_hour_cnt',
				IFNULL(IF(DATE(ps.exec_time) = CURRENT_DATE, ps.exec_day_cnt, 0), 0) as 'ps_exec_day_cnt',
				IFNULL(ps.exec_tot_cnt, 0) as 'ps_exec_tot_cnt',
				
				app.exec_hour_max_cnt as 'app_exec_hour_max_cnt',
				app.exec_day_max_cnt as 'app_exec_day_max_cnt',
				app.exec_tot_max_cnt as 'app_exec_tot_max_cnt',
				
				IFNULL(pa.is_mactive, 'Y') as 'pa_is_mactive',
				IFNULL(pa.app_offer_fee, FLOOR(app.app_merchant_fee * IFNULL(pa.app_offer_fee_rate, p.offer_fee_rate) / 100) ) AS 'publisher_fee', 
				
				m.name AS 'merchant_name', m.is_mactive as 'm_is_mactive',
				p.is_mactive as 'p_is_mactive',
				
				IF(app.exec_edate < CURRENT_DATE, 'N', 'Y') as 'exec_edate_check',

				IF(app.publisher_level IS NULL OR p.level <= app.publisher_level, 'Y', 'N') as 'p_lvmode',
				
				IF (app.is_public_mode = 'Y', 
					IF(IFNULL(pa.merchant_disabled,'N')='N','Y', 'N'),
					IF(IFNULL(pa.merchant_enabled,'N')='Y', 'Y', 'N')) as 'pa_pmode',
					
				(CASE 
					WHEN p.level = 1 THEN level_1_active_date
					WHEN p.level = 2 THEN level_2_active_date
					WHEN p.level = 3 THEN level_3_active_date
					WHEN p.level = 4 THEN level_4_active_date
					WHEN p.level >= 5 THEN NULL
					ELSE 'N' END) as 'p_level_active_date',
					
				IF(CASE 
					WHEN p.level = 1 THEN level_1_active_date
					WHEN p.level = 2 THEN level_2_active_date
					WHEN p.level = 3 THEN level_3_active_date
					WHEN p.level = 4 THEN level_4_active_date
					WHEN p.level >= 5 THEN NULL
					ELSE 'N' END > NOW(), 'N', 'Y') as 'p_level_active_mode',					
					
				IF ( ( app.exec_stime IS NULL OR app.exec_etime IS NULL ) OR
				  	  IF ( app.exec_stime <= app.exec_etime, 
				  	 	 app.exec_stime <= TIME(NOW()) AND app.exec_etime > TIME(NOW()), 
				  	 	 app.exec_stime < TIME(NOW()) OR app.exec_etime >= TIME(NOW()) )
					, 'Y', 'N') as 'check_time_period',
					
				IF(pa.active_time > NOW(), 'N', 'Y') as 'pa_active_time_mode',

				IF (pa.exec_hour_max_cnt <= IF(LEFT(ps.exec_time, 13) = LEFT(NOW(), 13), ps.exec_hour_cnt, 0), 'N', 'Y') as 'ps_check_hour_executed',
				IF (pa.exec_day_max_cnt <= IF(DATE(ps.exec_time) = CURRENT_DATE, ps.exec_day_cnt, 0), 'N', 'Y') as 'ps_check_day_executed',
				IF (pa.exec_tot_max_cnt <= IFNULL(ps.exec_tot_cnt,0), 'N', 'Y') as 'ps_check_tot_executed',
				
				IF (app.exec_hour_max_cnt <= IF(LEFT(s.exec_time, 13) = LEFT(NOW(), 13), s.exec_hour_cnt, 0), 'N', 'Y') as 'check_hour_executed',
				IF (app.exec_day_max_cnt <= IF(DATE(s.exec_time) = CURRENT_DATE, s.exec_day_cnt, 0), 'N', 'Y') as 'check_day_executed',
				IF (app.exec_tot_max_cnt > IFNULL(s.exec_tot_cnt,0), 'Y', 'N') as 'check_tot_executed',

				t.short_txt AS 'app_exec_type_name'
			FROM al_app_t app
				LEFT OUTER JOIN al_publisher_app_t pa ON app.app_key = pa.app_key AND pcode = '{$db_pcode}' 
				INNER JOIN al_merchant_t m ON app.mcode = m.mcode 
				INNER JOIN al_publisher_t p ON p.pcode = '{$db_pcode}' 
				LEFT OUTER JOIN al_app_exec_stat_t s ON app.app_key = s.app_key
				LEFT OUTER JOIN al_app_exec_pub_stat_t ps ON app.app_key = ps.app_key AND ps.pcode = '{$db_pcode}'
				LEFT OUTER JOIN string_t t ON t.type = 'app_exec_type' AND app.app_exec_type = t.code 
			WHERE app.is_mactive <> 'T' {$where} {$order_by} {$limit}";
	$result = mysql_query($sql, $conn);
?>
	<style>
		.list .mactive-N 			{background:#999}
		.list .mactive-N td			{color: #ddd}
		
		/* al_app_t.is_mactive */
		.list .appmactive-N 			{background:#888}
		.list .appmactive-N td			{color: #ddd}
		.list .appmactive-N:hover td 	{background:#aaa}
		
		.list .appmactive-D 			{background:#999}
		.list .appmactive-D td			{color: #ddd}
		.list .appmactive-D:hover td 	{background:#aaa}
		
		.list tr:hover td 			{background:#dff}
		.list tr.active-N td 			{background:#eee}
		.list tr.mactive-N:hover td {background:#ddd}
		
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
			<fieldset id="list-type" data-theme='c' class='td-2-item' data-role="controlgroup" data-type="horizontal" data-mini=true init-value="<?=ifempty($_REQUEST['listtype'],'A')?>" >
		        <input name="list-type" id="list-type-normal" value="A" type="radio" onclick="window.location.href=window.location.href.set_url_param('listtype', 'A').del_url_param('page')" />
		        <label for="list-type-normal">적립가능 목록</label>
		        <input name="list-type" id="list-type-deleted" value="B" type="radio" onclick="window.location.href=window.location.href.set_url_param('listtype', 'B').del_url_param('page')" />
		        <label for="list-type-deleted">적립불가 목록</label>
		    </fieldset>			
			<table class='line-height-15px' border=0 cellpadding=0 cellspacing=3px>
			<tr>
				<td valign=top align=right><t4 style='padding-top:6px; line-height:22px'>매체 코드 :<t4></td>
				<td valign=center>
					<t3 style='padding-top: 4px'>
						<?=$row_publisher['pcode']?> (<?=$row_publisher['level']?> 레벨)
					</t3>
				</td>
				<td></td>
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
				<td></td>
			</tr><tr>		
				<td align=right><t4>Publisher 상태 :<t4></td>
				<td>
					<div class='ui-block-a' style='width:200px'>
						<fieldset class='fieldset-nopadding' id="publisher-active" data-role="controlgroup" data-type="horizontal" data-mini=true init-value="<?=$row_publisher['is_mactive']?>" >
					        <input name="publisher-active" id="publisher-active-Y" value="Y" type="radio" />
					        <label for="publisher-active-Y">연동</label>
					        <input name="publisher-active" id="publisher-active-T" value="T" type="radio" />
					        <label for="publisher-active-T">개발</label>
					        <input name="publisher-active" id="publisher-active-N" value="N" type="radio" />
					        <label for="publisher-active-N">중지</label>
					    </fieldset>
					</div>
					<div class='ui-block-b' style='padding-top:5px'>
						<a href='#' onclick='<?=$js_page_id?>.action.on_btn_save_publisher_active()' data-role='button' data-theme='a' data-transition="none" data-inline='true' data-mini='true'>상태 적용</a>
					</div>
				</td>
				<td>
					<div style='padding: 5px; color:#888; background: #eef; font-size:11px; border-radius:0.6em; border: 1px solid #88f'>
					* 연동 상태: 광고 노출 + 적립 가능<br>
					* 개발 상태: 테스트로 광고만 노출<br>
					* 중지 상태: 광고 숨김 + 적립 불가
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
			<th width=20px>IDX</th>
			<th width=30px>적립</th>
			<th width=1px>P<br>수신<br>거부</th>
			<th width=80px>M 이름</th>
			<th width=40px>타입</th>
			<th width=80px>제목</th>

			<th>AD<br>오픈<br>시간</th>
			
			<th width=30px>AD<br>관리<br>차단</th>
			<th>AD<br>시간<br>수행</th>
			<th>AD<br>일일<br>수행</th>
			<th>AD<br>총<br>수행</th>
			<th width=70px>AD<br>레벨<br>오픈</th>
			<th width=70px>AD<br>기간<br>종료</th>
			
			<th width=30px>M<br>상태</th>
			<th width=30px>M<br>공개<br>모드</th>
			<th width=30px>P<br>상태</th>
			<th width=30px>P<br>레벨<br>제한</th>
			
			<th>PUB<br>지정가</th>
			<th>PUB<br>지정율</th>
			<th width=70px>PUB<br>지정<br>오픈</th>

			<th>PUB<br>시간<br>수행</th>
			<th>PUB<br>일일<br>수행</th>
			<th>PUB<br>총<br>수행</th>
			
			<th width=1px></th>
			<th>원가</th>
			<th>공급금액</th>
		</tr>	
	</thead>
	<tbody>
		<?
		$arr_active = array('Y' => '가능', 'N' => '<span style="color:blue;font-weight:bold">불가</span>');
		$arr_mp_mactive = array('Y' => '연동', 'N' => '<span style="color:red; font-weight: bold">중지</span>', 'T' => '<span style="color:red; font-weight: bold">개발</span>', 'D' => '<span style="color:red; font-weight: bold">삭제</span>');
		$arr_block_mode = array('Y' => '허용', 'N' => '<span style="color:red; font-weight: bold">차단</span>', 'D' => '<span style="color:red; font-weight: bold">삭제</span>');
		while ($row = mysql_fetch_assoc($result)) {
			$id = $row['id'];

			// 현재의 Publisher의 active상태 : Y / T / N 만 가능함.					
			$ar_btn_theme = array('a','a');
			if ($row['pa_is_mactive'] == 'Y') $ar_btn_theme = array('b','a');
			else if ($row['pa_is_mactive'] == 'N') $ar_btn_theme = array('a','b');
			
			$td_onclick = "onclick=\"mvPage('merchant-campaign-modify', null, {mcode: '{$row['mcode']}', appkey: '{$row['app_key']}'})\"";
			
			// Packageid (Optional display)
			$app_packageid = ($row['app_packageid'] ? "<div style='text-align:left; padding: 0; color:#888; font-size:9px'>{$row['app_packageid']}</div>" : "");
			
			$app_status = 'Y';
			if ($row['is_active'] == 'N' || 
				$row['is_mactive'] == 'N' || $row['is_mactive'] == 'D' || $row['exec_edate_check'] == 'N' || 
				$row['p_is_mactive'] == 'N' || $row['p_is_mactive'] == 'T' ||
				$row['check_time_period'] == 'N' || 
				$row['p_lvmode'] == 'N' || $row['p_level_active_mode'] == 'N' || $row['pa_active_time_mode'] == 'N' ||
				$row['pa_pmode'] == 'N' || 
				$row['check_hour_executed'] == 'N' || $row['check_day_executed'] == 'N' || $row['check_tot_executed'] == 'N' ||
				$row['ps_check_hour_executed'] == 'N' || $row['ps_check_day_executed'] == 'N' || $row['ps_check_tot_executed'] == 'N'
				) $app_status = 'N';
			
			
			// 오픈 시간
			$time_period = "";
			if ($row['exec_stime'] != "") {
				$shour = date("H", strtotime($row['exec_stime']));
				$ehour = date("H", strtotime($row['exec_etime']));
				$time_period = "{$shour}~{$ehour}";
				if ($row['check_time_period'] == 'N') $time_period = '<span style="color:red; font-weight: bold">'. $time_period .'</span>';
			}
/*
			// ------------------------------------------------------------------
			// 정상표시이고 app_status가 N 이면 표시 안함
			if ($app_status == 'N' && ifempty($_REQUEST['listtype'],'A') == 'A') continue;
			
			// 중지표시이고 app_status가 Y 이면 표시 안함
			if ($app_status == 'Y' && ifempty($_REQUEST['listtype'],'A') == 'B') continue;
			// ------------------------------------------------------------------
*/
			
			// 기간 오픈전에 따른 색상 처리
			$active_time = $row['active_time'];
			if ($row['pa_active_time_mode'] == 'N') $active_time = '<span style="color:red; font-weight: bold">'.$row['active_time'].'</span>';
			
			$p_level_active_date = $row['p_level_active_date'];
			if ($row['p_level_active_mode'] == 'N') $p_level_active_date = '<span style="color:red; font-weight: bold">'.$row['p_level_active_date'].'</span>';
			
			// 기간 종료
			$exec_edate = $row['exec_edate'];
			if ($row['exec_edate_check'] == 'N') $exec_edate = '<span style="color:red; font-weight: bold">'. $exec_edate .'</span>';
			
			// 수행완료에 따른 색상 처리
			$exec_hour_cnt = admin_number($row['app_exec_hour_cnt']) . '<br>' . admin_number($row['app_exec_hour_max_cnt'], "-", "0");
			if ($row['check_hour_executed'] == 'N') $exec_hour_cnt = '<span style="color:red; font-weight: bold">'. $exec_hour_cnt .'</span>';
			
			$exec_day_cnt = admin_number($row['app_exec_day_cnt']) . '<br>' . admin_number($row['app_exec_day_max_cnt'], "-", "0");
			if ($row['check_day_executed'] == 'N') $exec_day_cnt = '<span style="color:red; font-weight: bold">'. $exec_day_cnt .'</span>';
			
			$exec_tot_cnt = admin_number($row['app_exec_tot_cnt']) . '<br>' . admin_number($row['app_exec_tot_max_cnt'], "-", "0");
			if ($row['check_tot_executed'] == 'N') $exec_tot_cnt = '<span style="color:red; font-weight: bold">'. $exec_tot_cnt .'</span>';
			
			// Pcode별 수행 개수 ==> 수행완료에 따른 색상 처리
			$ps_exec_hour_cnt = admin_number($row['ps_exec_hour_cnt']) . '<br>' . admin_number($row['pa_exec_hour_max_cnt'], "-", "0");
			if ($row['ps_check_hour_executed'] == 'N') $ps_exec_hour_cnt = '<span style="color:red; font-weight: bold">'. $ps_exec_hour_cnt .'</span>';
			
			$ps_exec_day_cnt = admin_number($row['ps_exec_day_cnt']) . '<br>' . admin_number($row['pa_exec_day_max_cnt'], "-", "0");
			if ($row['ps_check_day_executed'] == 'N') $ps_exec_day_cnt = '<span style="color:red; font-weight: bold">'. $ps_exec_day_cnt .'</span>';
			
			$ps_exec_tot_cnt = admin_number($row['ps_exec_tot_cnt']) . '<br>' . admin_number($row['pa_exec_tot_max_cnt'], "-", "0");
			if ($row['ps_check_tot_executed'] == 'N') $ps_exec_tot_cnt = '<span style="color:red; font-weight: bold">'. $ps_exec_tot_cnt .'</span>';
			
			?>
			<tr id='list-<?=$row['id']?>' class='mactive-<?=$row['pa_is_mactive']?> appmactive-<?=$app_status?> active-<?=$appkey['is_active']?>' style='cursor:pointer'>
				<td <?=$td_onclick?>><?=$row['id']?></td>
				<td <?=$td_onclick?>><?=$arr_active[$row['is_active']]?></td>
				<td>
					<div class='btn-small-wrapper btn-wrapper'>
						<a class='btn-<?=$row['app_key']?> btn-Y' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_publisherapp_active("<?=$row_publisher['pcode']?>", "<?=$row['app_key']?>", "<?=$row['id']?>", "Y")' data-theme='<?=$ar_btn_theme[0]?>' data-role='button' data-mini='true' data-inline='true'>정<br>상</a>
						<a class='btn-<?=$row['app_key']?> btn-N' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_publisherapp_active("<?=$row_publisher['pcode']?>", "<?=$row['app_key']?>", "<?=$row['id']?>", "N")' data-theme='<?=$ar_btn_theme[1]?>'  data-role='button' data-mini='true' data-inline='true'>중<br>지</a>
					</div>
				</td>
				<td <?=$td_onclick?>><?=$row['merchant_name']?></td>
				
				<td <?=$td_onclick?>><?=$row['app_exec_type_name']?></td>
				<td <?=$td_onclick?>><div style='text-align:left; padding-left:5px'><?=$row['app_title']?><?=$app_packageid?></div></td>
				
				<td <?=$td_onclick?>><?=$time_period?></td>
				
				<td <?=$td_onclick?>><?=$arr_block_mode[$row['is_mactive']]?></td>
				<td <?=$td_onclick?>><?=$exec_hour_cnt?></td>
				<td <?=$td_onclick?>><?=$exec_day_cnt?></td>
				<td <?=$td_onclick?>><?=$exec_tot_cnt?></td>
				<td <?=$td_onclick?>><?=$p_level_active_date?></td>
				<td <?=$td_onclick?>><?=$exec_edate?></td>
				
				<td <?=$td_onclick?>><?=$arr_mp_mactive[$row['m_is_mactive']]?></td>
				<td <?=$td_onclick?>><?=$arr_block_mode[$row['pa_pmode']]?></td>
				<td <?=$td_onclick?>><?=$arr_mp_mactive[$row['p_is_mactive']]?></td>
				<td <?=$td_onclick?>><?=admin_number($row['publisher_level'])?><br><?=$arr_block_mode[$row['p_lvmode']]?></td>
								
				<td <?=$td_onclick?>><?=admin_number($row['app_offer_fee'], "-", "0")?></td>
				<td <?=$td_onclick?>><?=admin_number($row['app_offer_fee_rate'], "-", "0")?></td>
				
				<td <?=$td_onclick?>><?=$active_time?></td>
				
				<td <?=$td_onclick?>><?=$ps_exec_hour_cnt?></td>
				<td <?=$td_onclick?>><?=$ps_exec_day_cnt?></td>
				<td <?=$td_onclick?>><?=$ps_exec_tot_cnt?></td>
				
				<td><a href='#' onclick='goPage("dlg-publisherapp-config", null, {pcode: "<?=$pcode?>", appkey:"<?=$row['app_key']?>"})' data-theme='b' data-role='button' data-mini='true' data-inline='true'>공급<br>지정</a></td>
				<td <?=$td_onclick?>><?=number_format($row['app_merchant_fee'])?></td>
				<td <?=$td_onclick?>><b><?=admin_number($row['publisher_fee'])?></b></td>
				
			</tr>
			<?
		}
		?>
	</tbody>
	</table>
	<div style='padding: 5px; color:#888; background: #eef; font-size:11px; border-radius:0.6em; border: 1px solid #88f'>
		<b>[P 수신 거부]</b><br>
		* 정상: Publisher에게 지정광고 송출 허용<br>
		* 중지: Publisher에게 지정광고 송출 금지
	</div>		
	<div style='padding:10px' class='ui-grid-a'>
		<div class='ui-block-a' style='width:70%; padding-top:20px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>

<pre>
	* P 수신거부 	: Publisher에서 특정 광고의 수신 거부 설정 (Y/N)
	* 앱 적립 		: 광고 적립 가능/불가 여부 (Y/N)
	* 앱 관리차단 	: 관리자가 해당 광고 허용/차단 (Y/N)
	* M 상태		: Merchant의 광고 연동 상태 연동/개발/차단 (Y/T/N)
	* P 상태		: Publisher의 광고 연동 상태 연동/개발/차단  (Y/T/N)
	* M 레벨차단	: 앱에 지정된 공급레벨과 Publisher 레벨에 의한 차단 (Y/T/N)
	* M 공개모드	: Merchant가 광고에 대해 자동 공개/비공개 여부 : 설정된 대상만 차단/허용
	* 지정 오픈일	
	* 레벨 지정 오픈일
	* 시간 수행 완료
	* 일일 수행 완료
	* 총 수행 완료	
</pre>

<script type="text/javascript"> 

var <?=$js_page_id?> = function()
{
	// 외부에서 사용할 (Event Callback)함수 정의
	var ar_publisher_active = {'Y':'연동', 'T':'개발', 'N':'중지'};
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
