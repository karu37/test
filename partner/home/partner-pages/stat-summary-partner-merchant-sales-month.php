<?
	$partner_id = $_REQUEST['partnerid'];
	$mcode = $_REQUEST['mcode'];

	$date = $_REQUEST['date'];
	if (!$date) $date = date("Y-m-d");
	
	$year = date("Y", strtotime($date));
	$month = date("m", strtotime($date));
	
	// 해당월의 마지막 일을 얻어옴
	$sql = "SELECT DAY(LAST_DAY('{$year}-{$month}-01')) as 'last_day'";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$last_day = intval($row['last_day']);
		
	$ar_key_names = array();
	
	$db_partner_id = mysql_real_escape_string($partner_id);
	$db_mcode = mysql_real_escape_string($mcode);
	
	$sql = "SELECT a.mcode, sum(a.merchant_cnt) as 'cnt', sum(a.merchant_fee) as 'fee', reg_day, b.name 
			FROM al_summary_sales_d_t a 
				INNER JOIN al_merchant_t b ON a.mcode = b.mcode 
			WHERE a.mcode = '{$db_mcode}' AND a.reg_day >= '{$year}-{$month}-01' AND a.reg_day <= LAST_DAY('{$year}-{$month}-01') 
			GROUP by a.reg_day";

	$result = mysql_query($sql, $conn);
	while ($row = mysql_fetch_assoc($result)) {

		// mcode.name 이 하나만 존재하며 ==> 이 구성 그대로 두되, 목록 UI에서 합 컬럽만 제거한다.
		$ar_summary[$row['reg_day']] = $row;	// $ar_summary['2016-07-13']['cnt']
		
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
	
	<t4 style='line-height: 40px'>월간 실적 현황</t4>
	<hr>
	<div>
		<div style='display:inline-block; width:70px; vertical-align:top; padding-top:12px; padding-left: 20px;'>날짜 선택 : </div>
		<div style="display:inline-block">
			<div class='ui-grid-a'>
				<!-- 년도 선택 -->
				<!--; -->
				<div class='ui-block-a' style='width:120px; padding-top:5px'><input type="text" data-role='text' id='param-date' data-clear-btn='true' value="<?=$year . '-' . $month?>" /></div>
				<div class='ui-block-b' style='width:300px; padding-left:5px'>
					<a href='#' onclick="$('#param-date').val('<?=date("Y-m", strtotime("$year-$month-01". " -1 month"))?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'><<</a>
					<a href='#' onclick="$('#param-date').val('<?=date("Y-m")?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'>이번달</a>
					<a href='#' onclick="$('#param-date').val('<?=date("Y-m", strtotime("$year-$month-01". " 1 month"))?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'>>></a>
				</div>
			</div>
			<script> 
				$('input#param-date').monthpicker();
				$('#param-date').change(function(){ if ($(this).val() != '<?=$year.'-'.$month?>') window.location.href=location.href.del_url_param("page").set_url_param("date", $(this).val()); });
			</script>
		</div>
	</div>
	<hr>
	<br>
	<div>
		<t3><?=$year?>년 <?=$month?>월 실적 현황</t3>
	</div>	
	
	<br>
	<div style='width:600px'>
		<div style='width:100%;'>
		    <script type="text/javascript">
		      google.charts.setOnLoadCallback(drawChart);
		      function drawChart() {
		        var data = google.visualization.arrayToDataTable([
		          ['일시' , '금액']
		<?          
					for ($i = 1; $i <= $last_day; $i ++)
					{			
						$disp_date = $i;
						$check_date = sprintf("%04d-%02d-%02d", $year, $month, $i);
						
				        echo ",['{$disp_date}'"; 
			        	echo ", " . intval($ar_summary[$check_date]['fee']);			        	
				        echo "]";
					}          
		?>          
		        ]);
		        var options = {title: '', colors:['red']};
		        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
		        chart.draw(data, options);
		    }
		    </script>	
			<div id="chart_div" style="width:100%; height:300px; border:1px solid black"></div>
		
		</div>
	</div>
	<br>

	<table width=600px class='main-list' cellpadding=0 cellspacing=0>
	<thead>
		<tr>
			<th width=200px>일시</th>
			<th width=200px>적립 건수</th>
			<th width=200px>적립 실적</th>
		</tr>	
		<tr class='col-sum'>
			<th>계</th>
			<th class='cnt'><?=number_format($ar_summary_col['cnt'])?></th>
			<th class='sal'><?=number_format($ar_summary_col['fee'])?></th>
		</tr>
	</thead>
	<tbody>
<?
		for ($i = 1; $i <= $last_day; $i ++)
		{
			$check_date = sprintf("%04d-%02d-%02d", $year, $month, $i);
			
			?>
			<tr class='row-datum week-<?=$week_day?>' onclick=window.location.href='?id=stat-summary-partner-merchant-app-sales-month&partnerid=<?=$partner_id?>&mcode=<?=$mcode?>&date=<?=$check_date?>' style='cursor:pointer'>
				<td><?=$i?>일</td>
				<td class='cnt'><?=number_format($ar_summary[$check_date]['cnt'])?></td>
				<td class='sal'><?=number_format($ar_summary[$check_date]['fee'])?></td>
			</tr>
			<?
		}
?>
	</tbody>
	</table>
			