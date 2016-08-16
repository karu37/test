<?
	$partner_id = $_REQUEST['partnerid'];
	$pcode = $_REQUEST['pcode'];

	$date = $_REQUEST['date'];
	if (!$date) $date = date("Y-m-d");
	
	$year = date("Y", strtotime($date));
	$month = date("m", strtotime($date));
	$day = date("d", strtotime($date));
	
	// 해당월의 마지막 일을 얻어옴
	$sql = "SELECT DAY(LAST_DAY('{$year}-{$month}-01')) as 'last_day'";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$last_day = intval($row['last_day']);
		
	$ar_key_names = array();
	
	$db_partner_id = mysql_real_escape_string($partner_id);
	$db_pcode = mysql_real_escape_string($pcode);
	
	$sql = "SELECT a.app_key, SUM(a.publisher_cnt) AS 'cnt', SUM(a.publisher_fee) AS 'fee', b.app_title AS 'name'
			FROM al_summary_sales_d_t a 
				INNER JOIN al_app_t b ON a.app_key = b.app_key
				INNER JOIN al_partner_mpcode_t mp ON mp.partner_id = '$db_partner_id' AND mp.pcode = a.pcode AND mp.type = 'P'
			WHERE a.pcode = '{$db_pcode}' AND a.reg_day >= '{$date}' AND a.reg_day < DATE_ADD('{$date}', INTERVAL 1 DAY) 
			GROUP by a.reg_day
			ORDER by fee DESC";
	$result = mysql_query($sql, $conn);
	while ($row = mysql_fetch_assoc($result)) {

		$row_key = $row['app_key'];
		$ar_key_names[$row_key] = $row['name'];
		$ar_summary[$row_key] = $row;			// $ar_summary['앱키']['cnt']
		
		// COL별 합
		$ar_summary_col['cnt'] += $row['cnt'];
		$ar_summary_col['fee'] += $row['fee'];
		
		// TOTAL 합
		$ar_summary_all['cnt'] += $row['cnt'];
		$ar_summary_all['fee'] += $row['fee'];
	}
?>
	<style>
		.main-list tr:not(:last-child):hover td 			{background:#dff}
		.main-list .col-sum th			{text-align:right; padding-right: 15px; line-height:25px; background-color:#ddffdd}
		.main-list td					{text-align:right; padding-right: 15px; line-height:25px}

		.main-list th, .main-list td 	{border-left: 1px solid #888; border-top: 1px solid #888}
		.main-list th:last-child, .main-list td:last-child 	{border-right: 1px solid #888}
		.main-list tr:last-child td 	{border-bottom: 1px solid #888}
		
		.main-list .cnt					{color: #888}
		.main-list .sal					{color: #00f; font-weight:bold}
		
	</style>
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script type="text/javascript">
		google.charts.load('current', {packages:["corechart"]});
	</script>
	
	<t4 style='line-height: 40px'>월간 앱별 실적 현황</t4>
	<hr>
	<div class='ui-grid-a'>
		<div class='ui-block-a'>
			<div style='display:inline-block; width:70px; vertical-align:top; padding-top:12px; padding-left: 20px;'>날짜 선택 : </div>
			<div style="display:inline-block">
				<div class='ui-grid-a'>
					<!-- 년도 선택 -->
					<!--; -->
					<div class='ui-block-a' style='width:120px; padding-top:5px'><input type="text" data-role='date' id='param-date' data-clear-btn='true' value="<?=$date?>" /></div>
					<div class='ui-block-b' style='width:300px; padding-left:5px'>
						<a href='#' onclick="$('#param-date').val('<?=date("Y-m-d", strtotime("{$date} -1 day"))?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'><<</a>
						<a href='#' onclick="$('#param-date').val('<?=date("Y-m-d")?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'>오늘</a>
						<a href='#' onclick="$('#param-date').val('<?=date("Y-m-d", strtotime("{$date} 1 day"))?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'>>></a>
					</div>
				</div>
				<script> 
					$('#param-date').change(function(){ if ($(this).val() != '<?=$date?>') window.location.href=location.href.del_url_param("page").set_url_param("date", $(this).val()); });
				</script>
			</div>
		</div>
	</div>
	<hr>
	<br>
	<div>
		<t3><?=$year?>년 <?=$month?>월 <?=$day?>일 광고별 현황</t3>
	</div>	
	
	<table width=600px class='main-list' cellpadding=0 cellspacing=0>
	<thead>
		<tr>
			<th width=40px>번호</th>
			<th width=360px>광고 명</th>
			<th width=150px>적립 건수</th>
			<th width=150px>적립 실적</th>
		</tr>	
		<tr class='col-sum'>
			<th colspan=2><div style='text-align:center; padding: 0 10px'>계</div></th>
			<th class='cnt'><?=number_format($ar_summary_col['cnt'])?></th>
			<th class='sal'><?=number_format($ar_summary_col['fee'])?></th>
		</tr>
			
	</thead>
	<tbody>
<?
		$idx = 0;
       	foreach($ar_key_names as $key => $val)
		{
			$idx++;
?>
			<tr class='row-datum week-<?=$week_day?>'>
				<td><?=$idx?></td>
				<td><div style='text-align:left; padding: 0 10px'><?=$val?></div></td>
				<td class='cnt'><?=number_format($ar_summary[$key]['cnt'])?></td>
				<td class='sal'><?=number_format($ar_summary[$key]['fee'])?></td>
			</tr>
<?
		}
?>
	</tbody>
	</table>
			