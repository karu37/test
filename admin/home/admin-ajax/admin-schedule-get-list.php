<?
	$appkey = $_REQUEST['appkey'];
	$db_appkey = mysql_real_escape_string($appkey);

	$sql = "SELECT * FROM al_app_schedule_t WHERE app_key = '{$db_appkey}' order by schedule_date ASC";
	$result = mysql_query($sql, $conn);	
	
	$ar_data = array();
	while($row = mysql_fetch_assoc($result)) {

		$schedule_date = $row['schedule_date'];
		$schedule_count = $row['cnt'];
		$schedule_increment = $row['inc'];
		
		$td_click = "onclick='dlg_campaign_schedule.action.on_click_row(\"{$schedule_date}\", \"{$schedule_count}\", \"{$schedule_increment}\")' style='cursor:pointer'";
		$li_line = "<tr class='list-item'>
						<td {$td_click} align=center height=22px>{$schedule_date}</td>
						<td {$td_click} align=center>{$schedule_count}</td>
						<td {$td_click} align=center>{$schedule_increment}</td>
						<td align=center><a href='#' onclick='dlg_campaign_schedule.action.on_btn_del_schedule(\"{$schedule_date}\")' >삭제</a></td>
					</tr>";	
					
		$ar_data['list'] .= $li_line;
	}
	
	// schedule_date와 cnt, inc 로부터 앞으로 30일 동안의 개수를 구한다.
	for ($i=0; $i < 30; $i++) {
		$sDate = date("Y-m-d", strtotime("+{$i} day"));
		$ar_fields[] = " fn_get_app_scheduled_count('{$db_appkey}', '{$sDate}') as '{$sDate}'";
	}
	
	$sql = "SELECT " . implode(',', $ar_fields);
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	for ($i=0; $i < 30; $i++) {
		
		$sDate = date("Y-m-d", strtotime("+{$i} day"));
		$dispDate = date("m-d", strtotime("+{$i} day"));
		$cnt = intval($row[$sDate]);
		$ar_result[] = "[\"{$dispDate}\",{$cnt}]";
	}
	
	$ar_data['chart_data'] = "[ [\"날짜\", \"개수\"], " . implode(',', $ar_result) . " ]";

	return_die(true, $ar_data);
?>