<?
	// http://api.aline-soft.kr/ajax-request.php?id=scheduler-post-uncompleted-callback
	
	$max_try_cnt = 3;				// 최대 재시도
	$min_retry_minutes = 120;		// 재 시도 시간
	$timeout_callback_sec = 20;		// callback timeout

	// 1. get id of failed callback request from the al_user_app_t
	do {
		$ar_info = array();
		try {
			
			begin_trans($conn);	
			$sql = "SELECT id, pcode, adid, app_key, callback_url, callback_post, callback_tried FROM al_user_app_t WHERE callback_tried < {$max_try_cnt} AND callback_time <= date_sub(NOW(), interval {$min_retry_minutes} minute) AND callback_done = 'R' ORDER BY callback_time ASC LIMIT 1 FOR UPDATE";
			// echo $sql . '<br>';
			$row = mysql_fetch_assoc(mysql_query($sql, $conn));
			if ($row && $row['id']) {
				$ar_info = $row;
				
				$sql = "UPDATE al_user_app_t SET callback_time = NOW(), callback_tried = callback_tried + 1 WHERE id = '{$row['id']}'";
				mysql_query($sql, $conn);
				// echo $sql . '<br>';
			} else {
				echo "No more request";	
			}
			commit($conn);
			
		} catch (Exception $e) {
			rollback($conn);
			echo "Exception " . $e->getMessage();	
			break;
		}
		
		if (!$ar_info['id']) break;

		// 요청을 시작한다.
		$url_param = json_decode($ar_info['callback_post'], true);
		$response_data = post($ar_info['callback_url'], $url_param, $timeout_callback_sec);
		$ar_resp = json_decode($response_data, true);
		
/*		
		// debug log output
		echo "url : " . $ar_info['callback_url'] . '<br>';
		echo "param : "; 
		var_dump($url_param);
		echo '<br>';
		echo $response_data;
		exit;
*/
		// retry 로그 남김		
		make_action_log("callback-pub-local-retry", ifempty($ar_resp['result'], 'N'), $ar_info['pcode'], $ar_info['adid'], $ar_info['adid'], $ar_info['app_key'], null, get_timestamp() - $start_tm, $req_base_url, $url_param, $response_data, $conn);
		
		// 최종 상태 갱신
		if ($ar_resp['result'] == 'Y') $callback_result = 'Y';
		else if ($response_data === "") $callback_result = 'R';		// 아무것도 응답하지 않은 경우 ==> 실패로 보고 재시도함.
		else $callback_result = 'N';
		
		// R인데 최대 시도수 이상이면 ==> F로 처리
		if ($callback_result == 'R' && $ar_info['callback_tried'] + 1 >= $max_try_cnt) $callback_result = 'F';
		
		$db_result = mysql_real_escape_string($callback_result);
		$db_response_data = mysql_real_escape_string($response_data);
		
		// 결과값과 응답값을 갱신시킴
		$sql = "UPDATE al_user_app_t SET callback_done = '{$db_result}', callback_resp = '{$db_response_data}', callback_time = NOW() WHERE id = '{$ar_info['id']}'";
		// echo $sql;
		mysql_query($sql, $conn);
			
	} while(false);
?>