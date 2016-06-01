<?
	// 요청 URL (pcode = aline)
	//	http://api.aline-soft.kr/ajax-request.php?id=get-list&pcode=aline&is_web=Y
	
	$pub_mactive = get_publisher_info("reward_percent", $ar_publisher);
	if (!$pub_mactive || $pub_mactive == 'D') return_die('N', array('code'=>'-100', 'type'=>'E-REQUEST'), '유효하지 않은 매체코드입니다.');

	$reward_percent = $ar_publisher['reward_percent'];
	$pcode = $_REQUEST['pcode'];
	$is_web = ($_REQUEST['is_web'] == 'Y');

	$ar_time = mysql_get_time($conn);
	$sql = get_query_app_list($pcode, $ar_time, false, $conn);
	$result = mysql_query($sql, $conn);

	// ///////////////////////////////////////
	if ($is_web) {
		echo "<style>* {font-size:12px; } td {padding: 0 2px}</style><table border=1 cellpadding=0 cellspacing=0>";
	}
	// ///////////////////////////////////////

	unset($arr_data);
	$arr_data['result'] = 'Y';
	$arr_data['code'] = '1';
	$arr_data['msg'] = '성공';
	
	$arr_inactive = array();
	while ($row = mysql_fetch_assoc($result)) {

		// exec_tot_max_cnt 가 초과한 대상은 is_active ==> "N" 으로 변경한다.
		// exec_edate 가 지난 경우에도 is_active ==> "N" 으로 변경
		if ($row['tot_not_complished'] != 'Y' || $row['edate_not_expired'] != 'Y') {
			$arr_inactive[] = "'" . $row['app_key'] . "'";
			continue;
		}

		// 표시 차단 대상 (위에서 필터링 안된 대상)
		// 금액이 0 인 경우
		if ( intval($row['publisher_fee']) <= 0 ) continue;	
		
		// ///////////////////////////////////////
		if ($is_web) {
			echo "<tr>";
			echo "<td><img src='{$row['app_iconurl']}' width=40px /></td>";
			echo "<td>{$row['app_title']}</td>";
			echo "<td>{$row['app_key']}</td>";
			echo "<td>{$row['app_exec_type']}</td>";
			echo "<td>{$row['app_merchant_fee']}</td>";
			echo "<td>{$row['publisher_fee']}</td>";
			echo "<td>{$row['app_agefrom']} ~ {$row['app_ageto']}</td>";
			echo "<td>{$row['app_gender']}</td>";
			echo "<td>{$row['exec_stime']} ~ {$row['exec_etime']}</td>";
			echo "<td>{$row['edate_expired']}, {$row['tot_complished']}</td>";
			echo "<tr>";
			continue;
		}
		// ///////////////////////////////////////
		
		unset($item);
		$item['ad'] = $row['app_key'];
		$item['title'] = str_replace("\n", "<br>\n", htmlspecialchars($row['app_title']));
		$item['type'] = $row['app_exec_type'];
		$item['desc'] = $row['app_exec_desc'];
		$item['content'] = $row['app_content'];
		$item['icon'] = $row['app_iconurl'];

		$item['reward'] = floor($row['publisher_fee'] * $reward_percent / 100);
		$item['price'] = $row['publisher_fee'];
		
		$item['platform'] = $row['app_platform'];
		if ($row['app_market']) $item['market'] = $row['app_market'];
		if ($row['app_packageid']) $item['package'] = $row['app_packageid'];
		if ($row['app_scheme']) $item['scheme'] = $row['app_scheme'];
		
		if ($row['app_agefrom']) $item['age_from'] = $row['app_agefrom'];
		if ($row['app_ageto']) $item['age_to'] = $row['app_ageto'];
		if ($row['app_sex']) $item['gender'] = $row['app_sex'];
		
		$arr_data['items'][] = $item;
	}
	
	// Expire되거나, 모두 달성된 광고 is_active ==> 'N' 시키기
	if (count($arr_inactive) > 0) {
		$sql = "UPDATE al_app_t SET is_active = 'N' WHERE is_active <> 'N' AND app_key IN ( " . implode(",", $arr_inactive) . ")";
		mysql_executE($sql, $conn);
	}
	
	// ///////////////////////////////////////
	if ($is_web) {
		echo "</table>";
		exit;
	}
	// ///////////////////////////////////////
	
	
	return_die('Y', $arr_data);
	
?>
