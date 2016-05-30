<?
	$appkey = $_REQUEST['appkey'];
	$date = $_REQUEST['date'];
	if (!$date) $date = date("Y-m-d");
	
	$year = date("Y", strtotime($date));
	$month = date("m", strtotime($date));
	$start_date = date("Y-m-01", strtotime($date));
	
	$db_appkey = mysql_real_escape_string($appkey);

	// getting last day of month 
	$sql = "SELECT DAY(LAST_DAY('{$year}-{$month}-01')) as 'last_day'";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$last_day = intval($row['last_day']);

	// getting registered app information
	$sql = "SELECT * FROM al_app_t WHERE app_key = '{$db_appkey}'";
	$row_app = @mysql_fetch_assoc(mysql_query($sql, $conn));
	
	// getting execution count and total count from live stat table
	$sql = "SELECT DATE(a.exec_time) reg_day, IF(DATE(a.exec_time) = CURRENT_DATE, a.exec_day_cnt, 0) as 'exec_day_cnt', a.exec_tot_cnt
				FROM al_app_exec_stat_t a 
				WHERE a.app_key = '{$db_appkey}'";
 	$result = mysql_query($sql, $conn);
 	$row_today = @mysql_fetch_assoc(mysql_query($sql, $conn));
/* 	
 	// getting statistic data from summary table
	$sql = "SELECT a.reg_day, a.cnt 
				FROM summary_app_exec_d_t a 
				WHERE a.app_key = '{$db_appkey}'
				AND a.reg_day >= '{$start_date}' 
				AND a.reg_day <= LAST_DAY('{$start_date}') 
			ORDER BY a.reg_day DESC";
 	$result = mysql_query($sql, $conn);
	while ($row = mysql_fetch_assoc($result)) {

		$ar_summary[$row['reg_day']] = $row;
		
		// COL별 합
		$ar_summary_col['cnt'] += $row['cnt'];
	}
*/	
?>
준비중.
<!--
	<style>
		.main-list tr:not(:last-child):hover td 			{background:#dff}

		.main-list .col-sum th			{text-align:right; padding-right: 5px; line-height:20px; background-color:#ddffdd}
		.main-list td					{text-align:right; padding-right: 5px; line-height:20px}

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
	<t4 style='line-height: 40px'>상세 정보 현황</t4>
	<div>
		<input style='display:none' type="text" data-role='text' id='param-date' value="<?=$date?>" />
		<a href='#' onclick="$('#param-date').val('<?=date("Y-m-d", strtotime("$year-$month-01". " -1 month"))?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'><<</a>
		<a href='#' onclick="$('#param-date').val('<?=date("Y-m-d")?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'>이번달</a>
		<a href='#' onclick="$('#param-date').val('<?=date("Y-m-d", strtotime("$year-$month-01". " 1 month"))?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'>>></a>
		<script>$('#param-date').change(function(){ window.location.href=g_admin_util.set_url_param(location.href, "date", $(this).val()); });</script>
	</div>
	<br>
	<div>
		<t3><?=$year?>년 <?=$month?>월 집행 현황</t3>
	</div>	
	
	<br>
	
	<table width=500px class='main-list' cellpadding=0 cellspacing=0>
	<thead>
		<tr>
			<th width=3%>일</th>
			<th width=4%>소진 수</th>
		</tr>	
		<tr class='col-sum'>
<?
		// 조회중인달이 현재의 달인경우 오늘 카운터를 더해야 함.
		if (date("Y-m") == date("Y-m", strtotime($date))) 
			$month_sum = $ar_summary_col['cnt'] + $row_today['cnt'];
		else
			$month_sum = $ar_summary_col['cnt'];
?>			
			<th style='text-align:center'>계</th>
			<th class='sal'><?=number_format($month_sum)?></th>
		</tr>
	</thead>
	<tbody>
<?
		$today = date("Y-m-d");
		for ($i = 1; $i <= $last_day; $i ++)	// 1월 부터 12월 까지
		{
			$check_date = sprintf("%04d-%02d-%02d", $year, $month, $i);
			$cnt = $ar_summary[$check_date]['cnt'];
			if ($check_date == $today) $cnt = $row_today['cnt'];
			?>
			<tr>
				<td style='text-align:center'><?=$check_date?></td>
				<td class='sal'><?=number_format($cnt)?></td>
			</tr>
			<?
		}
?>
	<tbody>
	</table>	
-->	