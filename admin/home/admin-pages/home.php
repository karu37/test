<?
	$sql = "SELECT distinct ip FROM _server_status_t order by ip ASC";
	$result = mysql_query($sql, $conn);
	while ($row = mysql_fetch_assoc($result)) {
		$arr_ips[] = $row['ip'];
	}
?>
	<style>

		table .content td	{border-top: 1px solid #ddd; border-left: 1px solid #ddd}
		table tr.content:last-child td {border-bottom: 1px solid #ddd}
		table tr.content td:last-child {border-right: 1px solid #ddd}

		table .progress 	{line-height:20px; background-color:}
		table .topline 		{line-height:20px; background-color:lightyellow}
	</style>
<?

	for ($i = 0; $i < count($arr_ips); $i ++)
	{
		$db_ip = mysql_real_escape_string($arr_ips[$i]);

		$ar_col_define[] = array('type' => '', 'col' => '<col width=10%></col>', 'title' => '구분');
		$ar_col_define[] = array('type' => '', 'col' => '<col width=10%></col>', 'title' => '번호');
		$ar_col_define[] = array('type' => '', 'col' => '<col width=40%></col>', 'title' => '정보');
		$ar_col_define[] = array('type' => '', 'col' => '<col width=10%></col>', 'title' => '상태');
		$ar_col_define[] = array('type' => 'datetime', 'col' => '<col width=10%></col>', 'title' => '날짜');
		$sql = "SELECT nm, no, info, is_ok, up_date FROM _server_status_t WHERE ip = '{$db_ip}'";
		display_query("서버 {$arr_ips[$i]}", "", $sql, $ar_col_define, $conn);

		echo '<br>';
	}



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
			if ($type == 'date') $data = admin_to_date($data);
			else if ($type == 'number') $data = number_format($data);

			echo "<td>" . $data . "</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
}
?>