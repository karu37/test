<?
	$date = $_REQUEST['date'];
	if (!$date) $date = date("Y-m-d");
	
	$year = date("Y", strtotime($date));
	$month = date("m", strtotime($date));
	
	// 해당월의 마지막 일을 얻어옴
	$sql = "SELECT DAY(LAST_DAY('{$year}-{$month}-01')) as 'last_day'";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$last_day = intval($row['last_day']);
		
	$ar_key_names = array();
	
	$sql = "SELECT a.pcode, sum(a.publisher_cnt) as 'cnt', sum(a.publisher_fee) as 'fee', reg_day, b.name FROM al_summary_sales_d_t a INNER JOIN al_publisher_t b ON a.pcode = b.pcode WHERE reg_day >= '{$year}-{$month}-01' AND reg_day <= LAST_DAY('{$year}-{$month}-01') GROUP by a.pcode, reg_day";
	$result = mysql_query($sql, $conn);
	while ($row = mysql_fetch_assoc($result)) {

		$ar_key_names[$row['name']] = true;
		
		$col_name = $row['name'];
		$ar_summary[$row['reg_day']][$col_name] = $row;
		
		// ROW별 합
		$ar_summary_row[$row['reg_day']]['cnt'] += $row['cnt'];
		$ar_summary_row[$row['reg_day']]['fee'] += $row['fee'];
		
		// COL별 합
		$ar_summary_col[$col_name]['cnt'] += $row['cnt'];
		$ar_summary_col[$col_name]['fee'] += $row['fee'];
		
		// TOTAL 합
		$ar_summary_all['cnt'] += $row['cnt'];
		$ar_summary_all['fee'] += $row['fee'];
	}
?>
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
	
	<t4 style='line-height: 40px'>월간 매출 현황</t4>
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
						<a href='#' onclick="$('#param-date').val('<?=date("Y-m-d", strtotime("$year-$month-01". " -1 month"))?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'><<</a>
						<a href='#' onclick="$('#param-date').val('<?=date("Y-m-d")?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'>이번달</a>
						<a href='#' onclick="$('#param-date').val('<?=date("Y-m-d", strtotime("$year-$month-01". " 1 month"))?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'>>></a>
					</div>
				</div>
				<script>$('#param-date').change(function(){ window.location.href=g_admin_util.set_url_param(location.href, "date", $(this).val()); });</script>
			</div>
		</div>
		<div class='ui-block-b' style='text-align:right'>
			<a href='?id=stat-summary-p-sales-year&date=<?=$date?>' data-role='button' data-inline='true' data-mini='true'>연간 매출</a>
			<a href='?id=stat-summary-p-sales-month&date=<?=$date?>' data-role='button' data-inline='true' data-mini='true'>월간 매출</a>
			<a href='?id=stat-summary-p-sales-day&date=<?=$date?>' data-role='button' data-inline='true' data-mini='true'>일간 매출</a>
		</div>
	</div>
	<hr>
	<br>
	<div>
		<t3><?=$year?>년 <?=$month?>월 매출 현황</t3>
	</div>	
	
	<br>
	<div style='width:1100px'>
		<div style='width:600px; float:left'>
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
			        	echo ", " . intval($ar_summary_row[$check_date]['fee']); 
				        echo "]";
					}          
		?>          
		        ]);
		        var options = {title: '', colors:['red']};
		        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
		        chart.draw(data, options);
		    }
		    </script>	
			<div id="chart_div" style="width:600px; height:300px; border:1px solid black"></div>
		
		</div>
		<div style='width:400px; float:left'>
			
		    <script type="text/javascript">
		      google.charts.setOnLoadCallback(drawChart2);
		      function drawChart2() {
		        var data = google.visualization.arrayToDataTable([
		<?          
			        	echo "['종류', '년간 매출']";
			        	foreach($ar_key_names as $key => $val) {
				        	echo ",['{$key}', " . intval($ar_summary_col[$key]['fee']) . "]";
			        	}
		?>          
		        ]);
		        var options = {title: '', width:400, height:300};
		        var chart = new google.visualization.PieChart(document.getElementById('chart_pie_div'));
		        chart.draw(data, options);
		    }
		    </script>		
			<div id="chart_pie_div" style="width:400px; height:300px; border:1px solid black"></div>
			
		</div>
		<div style='clear:both'></div>
	</div>
	<br>

	<table width=100% class='main-list' cellpadding=0 cellspacing=0>
	<thead>
		<tr>
			<th rowspan=2 width=1%>시</th>
			
			<th colspan=2 width=4%>합</th>
<?
        	foreach($ar_key_names as $key => $val) {
	        	echo "\n<th colspan=2 width=4%>{$key}</th>";
        	}
?>			

		</tr>	
		<tr class='title-line2'>
			<th>건수</th><th>매출</th>
<?			
        	foreach($ar_key_names as $key => $val) {
				echo "\n<th>건수</th><th>매출</th>";
        	}
?>		
		</tr>	
		<tr class='col-sum'>
			<th><div style='width:35px'>계</div></th>
			<th class='cnt'><?=number_format($ar_summary_all['cnt'])?></th>
			<th class='sal'><?=number_format($ar_summary_all['fee'])?></th>
<?
        	foreach($ar_key_names as $key => $val) {
				echo "\n<th class='cnt'>" . number_format($ar_summary_col[$key]['cnt']) . "</th>
						<th class='sal'>" . number_format($ar_summary_col[$key]['fee']) . "</th>";
        	}
?>
		</tr>
			
	</thead>
	<tbody>
<?
		for ($i = 1; $i <= $last_day; $i ++)
		{
			$check_date = sprintf("%04d-%02d-%02d", $year, $month, $i);
			
			?>
			<tr class='row-datum week-<?=$week_day?>' onclick=window.location.href='?id=stat-summary-p-sales-day&date=<?=$check_date?>' style='cursor:pointer'>
				<td><?=$i?>일</td>
				<td class='cnt'><?=number_format($ar_summary_row[$check_date]['cnt'])?></td>
				<td class='sal'><?=number_format($ar_summary_row[$check_date]['fee'])?></td>
<?			
	        	foreach($ar_key_names as $key => $val) {
					echo "\n<td class='cnt'>" . number_format($ar_summary[$check_date][$key]['cnt']) . "</td>
							<td class='sal'>" . number_format($ar_summary[$check_date][$key]['fee']) . "</td>";
	        	}			
?>	        	
			</tr>
			<?
		}
?>
	</tbody>
	</table>

	<br>
    <script type="text/javascript">
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['일시' 
<?          
        	foreach($ar_key_names as $key => $val) {
		        	echo ", '" . $key . "'"; 
		    }
?>          
          ]
<?          
			for ($i = 1; $i <= $last_day; $i ++)
			{			
				$disp_date = $i;
				$check_date = sprintf("%04d-%02d-%02d", $year, $month, $i);
				
		        echo ",['{$disp_date}'"; 
	        	foreach($ar_key_names as $key => $val) {
		        	echo ", " . intval($ar_summary[$check_date][$key]['fee']); 
	        	}
		        echo "]";
			}          
?>          
        ]);
        var options = {title: '', colors:['#f00', '#0f0', '#00f', '#f80', '#f08', '#08f', '#88f']};
        var chart = new google.visualization.LineChart(document.getElementById('chart_partner_div'));
        chart.draw(data, options);
    }
    </script>		
    * 파트너별 그래프
	<div id="chart_partner_div" style="width:100%; height:600px; border:1px solid black"></div>
	<br>
			