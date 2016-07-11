<?
	$guest_id = get_session_id();
	if (!$guest_id) exit("Login 상태가 아닙니다.");
	
	$db_guest_id = mysql_real_escape_string($guest_id);

	// --------------------------------
	// Paginavigator initialize	
	$sql = "SELECT COUNT(*) as cnt FROM guest_app_list WHERE guest_id = '{$db_guest_id}'";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$pages = new Paginator($row['cnt']);
	$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;
	// --------------------------------

	$sql = "SELECT a.*, IF(CURRENT_DATE = DATE(b.exec_time), exec_day_cnt, 0) AS 'exec_day_cnt', exec_tot_cnt
			FROM guest_app_list f
				INNER JOIN al_app_t a ON a.app_key = f.app_key
				LEFT OUTER JOIN al_app_exec_stat_t b ON a.app_key = b.app_key
			WHERE f.guest_id = '{$db_guest_id}' ORDER BY f.id DESC $limit";
 	$result = mysql_query($sql, $conn);
?>
	<style>
		#ctl-main-list tbody tr	{height: 49px; line-height:18px}
		#ctl-main-list th	{padding: 2px 4px}
		#ctl-main-list td	{padding: 2px 4px}
		
		#ctl-main-list .recentad-1								{background:#eeffff}

		#ctl-main-list .mactive-D 								{background:#ffeeee}
		#ctl-main-list .mactive-D td							{color: #000}
		#ctl-main-list .mactive-H 								{background:#EEEEEE}
		#ctl-main-list .mactive-H td							{color: #000}
		
		#ctl-main-list .mactive-Y.active-N						{background: #eee}
		
		#ctl-main-list .mactive-Y.active-Y td.condition-daily-expire	{background: #fbb}
		#ctl-main-list .mactive-H.active-Y td.condition-daily-expire	{background: #fbb}
		#ctl-main-list .mactive-Y.active-N td.condition-daily-expire	{background: #fbb}
		#ctl-main-list .mactive-H.active-N td.condition-daily-expire	{background: #fbb}
		
		#ctl-main-list .mactive-Y.active-Y td.condition-expire	{background: #fbb}
		#ctl-main-list .mactive-H.active-Y td.condition-expire	{background: #fbb}
		#ctl-main-list .mactive-Y.active-N td.condition-expire	{background: #fbb}
		#ctl-main-list .mactive-H.active-N td.condition-expire	{background: #fbb}
		
		#ctl-main-list .mactive-Y .btn-restore	{display: none}
		#ctl-main-list .mactive-D .btn-delete	{display: none}
		#ctl-main-list .mactive-D .btn-hide	{display: none}
		#ctl-main-list .mactive-H .btn-hide	{display: none}
		
		#ctl-main-list .cond-fail				{background: #fbb}
		#ctl-main-list .list-order .ui-input-text	{border: none}
		
		#ctl-main-list tr:not(:last-child):hover td 			{background:#dff}
		
	</style>
	<t4 style='line-height: 40px'>광고 목록</t4>
	<hr>

	<div style="display:block; padding-top:20px; padding-left: 10px; font-size:22px; color: blue; font-weight: bold">총 : <?=number_format($pages->total_items)?> 건</div>
	<div class='ui-grid-a' style='padding:5px 10px; <?=$pages->num_pages <= 1 ? "display:none" : ""?>'>
		<div class='ui-block-a' style='width:70%; padding-top:5px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>
	<br>
	<table id='ctl-main-list' class='single-line' cellpadding=0 cellspacing=0>
	<thead>
		<tr>
			<th width=4%>상태</th>
			<th width=3%>Icon</th>
			<th width=7%>타입</th>
			<th width=6%>마켓</th>
			<th width=20%>앱 제목</th>
			
			<th width=5%>집행기간</th>
			<th width=5%>집행시간</th>
			<th width=6%>집행수량</th>
			<th width=6%>총 수량</th>
			
			<th width=3%>등록일</th>
		</tr>	
	</thead>
	<tbody>
		<?
		$arr_filter_group = array('iexec' => '설치/실행형', 'fin' => '신청형(보험/카드)', 'reg' => '가입/로그인형', 'blog' =>'블로그형');
		$arr_app_type = array('I' => '설치형', 'E' => '실행형', 'S' => '검색설치형', 'W' => '웹형', 'R' => '구글리뷰형');
		$arr_market_name = array('A' => '앱스토어', 'P' => '<b>플레이스토어</b>', 'T' => 'T-스토어', 'O' => '올레스토어', 'U' => 'U+스토어', 'N' => 'Naver스토어', 'X' => "-");

		while ($row = mysql_fetch_assoc($result)) {
			$id = $row['id'];

			// active / deleted
			if ($row['is_active'] == 'Y') {
				if ($row['is_mactive'] == 'D') $status = '<span style="color:red">삭제</span>';
				else if ($row['is_mactive'] == 'H') $status = '숨김';
				else $status = '<span style="color:blue; font-weight: bold">정상</span>';
			} else {
				if ($row['is_mactive'] == 'D') $status = '<span style="color:red">중지삭제</span>';
				else if ($row['is_mactive'] == 'H') $status = '중지숨김';
				else $status = '<span style="color:blue; font-weight: bold">중지</span>';
			}
			
			// 실행 조건 체크
			$to_day = date("Y-m-d");
			$now_time = date("H:i:s");
			
			$status_date_expire = "normal";
			$status_count_expire = "normal";
			if ($row['is_active'] == 'Y') {
				if (!($now_time >= $row['exec_stime'] && $now_time < $row['exec_etime'])) {$status_date_expire = 'daily-expire';}
				if (!($to_day <= $row['exec_edate'])) {$status_date_expire = 'expire';}
				if (!($row['exec_day_max_cnt'] > $row['exec_day_cnt'])) {$status_count_expire = 'daily-expire';}
				if (!($row['exec_tot_max_cnt'] > $row['exec_tot_cnt'])) {$status_count_expire = 'expire';}
			}
			
			// filter 조건
			$filter_gender = "";
			if ($row['app_gender'] == 'M') $filter_gender = "남자";
			else if ($row['app_gender'] == 'F') $filter_gender = "여자";
			
			$filter_age = "";
			if ($row['app_agefrom'] != 0 && $row['app_agefrom'] != 10000)
				$filter_age = "{$row['app_agefrom']} ~ {$row['app_ageto']}";
		?>
			<tr id='list-<?=$row["id"]?>' class='active-<?=$row['is_active']?> mactive-<?=$row['is_mactive']?> recentad-<?=$row["recent_ad"]?>' onclick='<?=$js_page_id?>.action.on_row_click("<?=$row['app_key']?>")' style='cursor:pointer'>
				<td id="user-status-<?=$row['id']?>"><?=$status?></td>
				<td><img src='<?=$row['app_iconurl']?>' width=40px /></td>
				<td><div style='color:blue; font-weight:bold; text-align: center; min-width: 70px'><?=$arr_app_type[$row['app_exec_type']]?></div><span id='list-group-<?=$row['id']?>'><?=$arr_filter_group[$row['filter_group']]?></span></td>
				<td><?=$arr_market_name[$row['app_market']]?></td>
				<td style='text-align:left; font-size:12px; line-height: 14px'><?=$row['app_title']?></td>
				<td class='condition-<?=$status_date_expire?>'>
					<div style='min-width:100px'>
						<?=admin_to_date($row['exec_edate'])?>
					</div>
				</td>
				<td class='condition-<?=$status_date_expire?>'>
					<?=admin_time_period($row['exec_stime'], $row['exec_etime'])?>
				</td>
				<td class='condition-<?=$status_count_expire?>'>
					<?=admin_number($row['exec_tot_cnt'])?>
				</td>
				<td class='condition-<?=$status_count_expire?>'>
					<?=admin_number($row['exec_tot_max_cnt'])?>
				</td>
				<td>
					<?=admin_to_datetime($row['reg_date'])?>
				</td>
			</tr>
		<?
		}
		?>
	</tbody>
	</table>
	
	<div style='padding: 0px'>
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
			on_row_click: function(appkey) {
				window.location.href='?id=guest-app-view&appkey=' + util.urlencode(appkey);
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
