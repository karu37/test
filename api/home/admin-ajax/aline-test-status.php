<?
	if (!$_REQUEST['adid']) $_REQUEST['adid'] = '0000000000000000-0000-0000-0000-0000';
	
	$pcode = $_REQUEST['pcode'];
	$adid = $_REQUEST['adid'];
	$db_pcode = mysql_real_escape_string($pcode);
	$db_adid = mysql_real_escape_string($adid);

	$ar_time = mysql_get_time($conn);
	
	// $sql = "SELECT * FROM al_user_app_t WHERE adid = '{$db_adid}' ORDER BY id DESC LIMIT 10";
	// $result_ua = mysql_query($sql, $conn);
?>	
<head>
	<style>
		* {font-size:12px; }
		td {padding: 0 2px}
		
		table .content td	{border-top: 1px solid #ddd; border-left: 1px solid #ddd}
		table tr.content:last-child td {border-bottom: 1px solid #ddd}
		table tr.content td:last-child {border-right: 1px solid #ddd}
		
		table .progress 	{line-height:20px; background-color:}
		table .topline 		{line-height:20px; background-color:lightyellow}		
	</style>
</head>
<body>
<?
		$sql = "SELECT a.mcode, a.pcode, b.app_title, a.merchant_fee, a.publisher_fee, a.m_reg_date, a.p_reg_date 
				FROM al_user_app_saving_t a
					INNER JOIN al_app_t b ON a.app_key = b.app_key
				WHERE adid = '{$db_adid}' ORDER BY a.id DESC LIMIT 10";
		$result = mysql_query($sql, $conn);
		
		unset($ar_col_define);
		$ar_col_define[] = array('type' => '', 'col' => '<col width=2%></col>', 'title' => 'M 코드');
		$ar_col_define[] = array('type' => '', 'col' => '<col width=2%></col>', 'title' => 'P 코드');
		$ar_col_define[] = array('type' => '', 'col' => '<col width=4%></col>', 'title' => '제목');
		$ar_col_define[] = array('type' => 'number', 'col' => '<col width=2%></col>', 'title' => 'M가격');
		$ar_col_define[] = array('type' => 'number', 'col' => '<col width=2%></col>', 'title' => 'P가격');
		$ar_col_define[] = array('type' => 'date', 'col' => '<col width=2%></col>', 'title' => 'M일시');
		$ar_col_define[] = array('type' => 'date', 'col' => '<col width=2%></col>', 'title' => 'P일시');
		display_query("ADID기준 적립 상태 - al_user_app_saving_t", "", $sql, $ar_col_define, $conn);

		echo "<br>";
		$sql = "SELECT a.mcode, a.pcode, b.app_title, a.merchant_fee, a.publisher_fee, a.status, a.forced_done, a.action_atime, a.action_btime, a.action_dtime, a.callback_done, a.callback_data, a.reg_date
				FROM al_user_app_t a
					INNER JOIN al_app_t b ON a.app_key = b.app_key
				WHERE adid = '{$db_adid}' ORDER BY a.id DESC LIMIT 10";

		$result = mysql_query($sql, $conn);
		
		unset($ar_col_define);
		$ar_col_define[] = array('type' => '', 'col' => '<col width=2%></col>', 'title' => 'M 코드');
		$ar_col_define[] = array('type' => '', 'col' => '<col width=2%></col>', 'title' => 'P 코드');
		$ar_col_define[] = array('type' => '', 'col' => '<col width=4%></col>', 'title' => '제목');
		$ar_col_define[] = array('type' => 'number', 'col' => '<col width=2%></col>', 'title' => 'M가격');
		$ar_col_define[] = array('type' => 'number', 'col' => '<col width=2%></col>', 'title' => 'P가격');
		$ar_col_define[] = array('type' => '', 'col' => '<col width=2%></col>', 'title' => '상태');
		$ar_col_define[] = array('type' => '', 'col' => '<col width=2%></col>', 'title' => '강제<br>적립');

		$ar_col_define[] = array('type' => 'date', 'col' => '<col width=2%></col>', 'title' => '요청');
		$ar_col_define[] = array('type' => 'date', 'col' => '<col width=2%></col>', 'title' => '확인');
		$ar_col_define[] = array('type' => 'date', 'col' => '<col width=2%></col>', 'title' => '완료');
		$ar_col_define[] = array('type' => '', 'col' => '<col width=2%></col>', 'title' => '적립<br>호출');
		$ar_col_define[] = array('type' => '', 'col' => '<col width=2%></col>', 'title' => '적립<br>리턴');
		
		$ar_col_define[] = array('type' => 'date', 'col' => '<col width=2%></col>', 'title' => '일시');
		display_query("ADID기준 진행 상태 - al_user_app_t", "", $sql, $ar_col_define, $conn);

		echo "<br>";
		$sql = "SELECT b.app_title, a.exec_time, IFNULL(c.start_cnt, 0), IF(exec_time = '{$ar_time['datehour']}', a.exec_hour_cnt, 0), IF(DATE(exec_time) = '{$ar_time['day']}', a.exec_day_cnt, 0), a.exec_tot_cnt,
					IFNULL(pa.exec_hour_max_cnt, b.exec_hour_max_cnt) as 'P-시간제한', 
					IFNULL(pa.exec_day_max_cnt, b.exec_day_max_cnt) as 'P-일일제한', 
					IFNULL(pa.exec_tot_max_cnt, b.exec_tot_max_cnt) as 'P-총제한'
				FROM al_app_exec_stat_t a
					INNER JOIN al_app_t b ON a.app_key = b.app_key
					LEFT OUTER JOIN al_publisher_app_t pa ON a.app_key = pa.app_key AND pa.pcode = '{$db_pcode}'
					LEFT OUTER JOIN al_app_start_stat_t c ON a.app_key = c.app_key AND c.pcode = '{$db_pcode}' AND c.reg_day = '{$ar_time['day']}'
				ORDER BY a.app_key DESC LIMIT 10";
		$result = mysql_query($sql, $conn);
		
		unset($ar_col_define);
		$ar_col_define[] = array('type' => '', 'col' => '<col width=2%></col>', 'title' => '제목');
		$ar_col_define[] = array('type' => 'date', 'col' => '<col width=2%></col>', 'title' => '일/시');
		$ar_col_define[] = array('type' => 'number', 'col' => '<col width=2%></col>', 'title' => '시작수(일일)');
		$ar_col_define[] = array('type' => 'number', 'col' => '<col width=2%></col>', 'title' => '시간당수');
		$ar_col_define[] = array('type' => 'number', 'col' => '<col width=4%></col>', 'title' => '일일수');
		$ar_col_define[] = array('type' => 'number', 'col' => '<col width=2%></col>', 'title' => '총수');
		$ar_col_define[] = array('type' => '', 'col' => '<col width=2%></col>', 'title' => 'P-시간제한');
		$ar_col_define[] = array('type' => '', 'col' => '<col width=4%></col>', 'title' => 'P-일일제한');
		$ar_col_define[] = array('type' => 'number', 'col' => '<col width=2%></col>', 'title' => 'P-총제한');
		display_query("수행 수 (모든 Pub들의 합)- al_app_exec_stat_t", "", $sql, $ar_col_define, $conn);
		

		echo "<br>";
		$sql = "SELECT b.app_title, SUM(a.merchant_cnt), SUM(a.merchant_fee), SUM(a.publisher_cnt), SUM(a.publisher_fee) 
				FROM al_summary_user_sales_h_t a
					INNER JOIN al_app_t b ON a.app_key = b.app_key
				WHERE a.pcode = '{$db_pcode}'
				GROUP BY a.app_key";
		$result = mysql_query($sql, $conn);
		
		unset($ar_col_define);
		$ar_col_define[] = array('type' => '', 'col' => '<col width=2%></col>', 'title' => '제목');
		$ar_col_define[] = array('type' => 'number', 'col' => '<col width=2%></col>', 'title' => 'M-수행수');
		$ar_col_define[] = array('type' => 'number', 'col' => '<col width=2%></col>', 'title' => 'M-매출');
		$ar_col_define[] = array('type' => 'number', 'col' => '<col width=4%></col>', 'title' => 'P-수행수');
		$ar_col_define[] = array('type' => 'number', 'col' => '<col width=2%></col>', 'title' => 'P-매출');
		display_query("Publisher 매출 - al_summary_user_sales_h_t", "", $sql, $ar_col_define, $conn);		
?>
</body>
<?
function display_query($title, $source, $query, $ar_column, $connect)
{
	$res = mysql_query($query, $connect);
	$nFieldCount = mysql_num_fields($res);
	
    echo "<table width=100% class=unit_table width=50 border=0 cellpadding=2 cellspacing=0>";
    if ($ar_column) {
  		for ($i=0; $i < $nFieldCount; $i ++)
			echo $ar_column[$i]['col'];
	}
	echo "<tr><td colspan=20 class=topline><b>$title</b></td></tr>";
	echo "<tr><td colspan=20 class=srcname>$source</td></tr>";
    echo "<tr class=progress>";
	  	for ($i=0; $i < $nFieldCount; $i ++) {
  			$title = $ar_column[$i]['title'];
	  		if (!$title) $title = mysql_field_name($res, $i);
			echo "<td>" . $title . "</td>";
		}
	echo "</tr>";
	
	while($row = mysql_fetch_array($res))
	{
		echo "<tr class='content'>";
		for ($i=0; $i < $nFieldCount; $i ++) {
			$type = $ar_column[$i]['type'];
			
			$data = $row[$i];
			if ($type == 'date') $data = date("m/d H:i:s", strtotime($data));
			else if ($type == 'number') $data = number_format($data);
		
			echo "<td>" . $data . "</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
}
?>
