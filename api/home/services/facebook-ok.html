<?
	header("Content-Type: text/html; charset=utf-8");

	include dirname(__FILE__)."/../php_lib/util.php";

	$code = $_REQUEST['c'];
	$dev = $_REQUEST['dev'];
	$param = $_REQUEST['p2'];

	if ($code == 'Y') {

		$is_error = false;
		$error_msg = '';
		do {

			$ar_param = json_decode(base64_decode($param), true);
			if (!$ar_param) {
				$display_txtcolor = 'darkred';
				$display_txt = '요청이 올바르지 않습니다(-1).';
				break;
			}

			$userdata = $ar_param['u'];
			$md5 = $ar_param['m'];
			if (md5('aline-done' . $userdata) != $md5) {
				$display_txtcolor = 'darkred';
				$display_txt = '요청이 올바르지 않습니다(-2).';
				break;
			}

			$conn = dbConn();

			$result = user_app_saving($userdata, $error_msg, $conn);
			if (!$result) {
				$display_txtcolor = 'darkred';
				$display_txt = $error_msg;
			} else {
				$display_txtcolor = 'darkblue';
				$display_txt = '적립 요청이 완료되었습니다.';
			}

		} while(false);

	}
	else if ($code == 'EE') {
		$display_txtcolor = 'darkred';
		$display_txt = '잘못된 페이지 입니다.';
	}
	else if ($code == 'EA') {
		$display_txtcolor = 'darkred';
		$display_txt = '이미 좋아요한 상태입니다.';
	}
	else if ($code == 'EU') {
		$display_txtcolor = 'darkred';
		$display_txt = '알 수 없는 오류입니다.';
	}


// -------------------------------------------------------------------------------------------------------------------------------------------------
// 적립 호출
// -------------------------------------------------------------------------------------------------------------------------------------------------
function user_app_saving($userdata, &$error_msg, $conn)
{
	$g_local['unique_prefix'] = "loc";		// 적립 결과에 Unique 키
	$g_local['timeout_sec'] = 60;			// 시작 / 적립 요청시의 Timeout 초

	// 전달된 파라미터 받아서 표준화 하기

	//## 현재 시간 $ar_time
	$ar_time = mysql_get_time($conn);

	//## unique_key
	$unique_key 	= $g_local['unique_prefix'] . md5($userdata);

	//## userdata ==> aid
	$cb_user_data 	= $userdata;
	$js_data = json_decode(base64_decode(str_replace('*','=',$cb_user_data)), true);

	//## userapp_id
	$userapp_id = $js_data['aid'];

	// ------------------------------------------------

	//## al_user_app_t 정보 로딩 및 m_key 값과 일치하는지 확인
	$db_userapp_id = mysql_real_escape_string($userapp_id);
	$sql = "SELECT a.*,
					p.reward_percent,
					p.callback_url
			FROM al_user_app_t a
					INNER JOIN al_publisher_t p ON a.pcode = p.pcode
					INNER JOIN al_app_t app ON a.app_key = app.app_key
			WHERE a.id = '{$db_userapp_id}'";
	$arr_userapp = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if (!$arr_userapp) {
		$error_msg = '광고 정보가 유효하지 않습니다.(-100)';
		return false;
	}
	//## pcode from db
	$g_log['pcode'] = $arr_userapp['pcode'];
	$g_log['adid'] = $arr_userapp['adid'];
	$g_log['app_key'] = $arr_userapp['app_key'];

	//## 적립하기
	$ar_time = mysql_get_time($conn);
	$ar_reward = callback_reward($arr_userapp['pcode'], $arr_userapp['mcode'], $arr_userapp['app_key'], $arr_userapp['adid'],
							$arr_userapp['merchant_fee'], $arr_userapp['tag_price'], $arr_userapp['publisher_fee'], $unique_key,
							$ar_time, false, $conn);

	//## 적립 실패 처리
	if ($ar_reward['result'] == 'N') {
		if ( in_array($ar_reward['code'], array(-3003, -3001)) )
			$error_msg = "이미 적립된 광고입니다.";
		else
			$error_msg = "적립 오류로 적립이 중지되었습니다.({$ar_reward['code']})<br><br>오류 : " . get_error_msg($ar_reward['code'], "");
		return false;
	}

	// 강제적립된 대상을 적립한 경우 콜백호출하면 안됨 (Y 가 아닌 N 또는 F 인 경우에 호출함)
	if ($ar_reward['callback_done'] != 'Y') {

		// ----------------------------------------------------------------------
		// CALLBACK 파라미터 생성 후 Publisher 콜백 호출
		// ----------------------------------------------------------------------
		$url_param['ad'] = $arr_userapp['app_key'];
		$url_param['price'] = $arr_userapp['publisher_fee'];
		$url_param['reward'] = intval($arr_userapp['publisher_fee'] * $arr_userapp['reward_percent'] / 100);
		$url_param['uid'] = $arr_userapp['uid'];
		$url_param['userdata'] = $arr_userapp['userdata'];
		$url_param['unique'] = $unique_key;
		$req_base_url = $arr_userapp['callback_url'];

		// ----------------------------------------------------------------------
		$start_tm = get_timestamp();

		// MYSQL을 닫은 후 요청이 완료되면 dbPConn()으로 재 연결한다.
		mysql_close($conn);
		$response_data = post($req_base_url, $url_param, $g_local['timeout_sec']);
		$conn = dbConn();

		$ar_resp = json_decode($response_data, true);

		make_action_log("callback-pub-local", ifempty($ar_resp['result'], 'N'), $arr_userapp['pcode'], $arr_userapp['adid'], $arr_userapp['app_key'], null, get_timestamp() - $start_tm, $req_base_url, $url_param, $response_data, $conn);

		// ----------------------------------------------------------------------
		// 리턴 데이터 구성 (리턴 불필요 -- 자체 해결해야 함)
		// 	 callback_done 결과를 al_user_app_t 에 기록하기 실패시 F 로 설정함.
		// ----------------------------------------------------------------------

		if ($ar_resp['result'] == 'Y') $callback_result = 'Y';
		else if (trim($response_data) == "") $callback_result = 'R';		// 아무것도 응답하지 않은 경우 ==> 실패로 보고 재시도함.
		else $callback_result = 'N';

		$db_callback_url = mysql_real_escape_string($req_base_url);
		$db_callback_post = mysql_real_escape_string(json_encode($url_param));
		$db_result = mysql_real_escape_string($callback_result);
		$db_response_data = mysql_real_escape_string($response_data);

		$sql = "UPDATE al_user_app_t SET callback_done = '{$db_result}', callback_url = '{$db_callback_url}', callback_post = '{$db_callback_post}', callback_resp = '{$db_response_data}', callback_time = '{$ar_time['now']}', callback_tried = callback_tried + 1 WHERE id = '{$userapp_id}'";
		mysql_query($sql, $conn);
	}

	return true;
}




?>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, target-densitydpi=medium-dpi, user-scalable=0" />
</head>
<body>

<div style='text-align: center; padding-top:100px'>
	<div style='padding: 5px; font-weight: bold; font-size: 18px; letter-spacing: -0.05em; color: <?=$display_txtcolor?>'><?=$display_txt?></div>
</div>

</body>
</html>
