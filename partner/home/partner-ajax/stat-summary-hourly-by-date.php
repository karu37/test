<?
	$date = $_REQUEST['date'];
	$date = str_replace('-', '/', $date);
	$sql = "select hr, user_cnt, recomm_autoring_cnt, recomm_user_cnt, quit_cnt 
			FROM summary_user_h_t
			WHERE reg_day = '{$date}'
			order by hr DESC";
	$result = mysql_query($sql, $conn);
	$txt = "";
	while ($row = mysql_fetch_assoc($result)) {
	
		$txt .=	"<tr>
			        <td align=center>{$row['hr']}</td>
					<td align=center>{$row['user_cnt']} / {$row['recomm_autoring_cnt']} / {$row['recomm_user_cnt']} / {$row['quit_cnt']}</td>
				</tr>\n";		
	}

$data = "<table width=100% class='main-list single-line' cellpadding=0 cellspacing=0>
	<thead>
		<tr>
			<th width=4%>시간</th>
			<th width=4%>가입자 수 (총 /오토링/일반추천/탈퇴)</th>
		</tr>	
	</thead>
	<tbody>
		{$txt}
	</tbody>
	</table>";
		
return_die(true, array('data' => $data));
		
?>
