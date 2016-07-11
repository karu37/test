<?
function admin_date_period($start_date, $end_date) {
	if (!$start_date && !$end_date) return "-";
	if ($start_date == "00-00-00" && $end_date == "00-00-00") return "-";
	return date("Y/m/d", strtotime($start_date)) ."~". date("Y/m/d", strtotime($end_date));
}
function admin_time_period($start_time, $end_time) {
	if (!$start_time && !$end_time) return "-";
	if ($start_time == "00:00:00" && $end_time == "24:00:00") return "-";
	return date("H", strtotime($start_time)) ."시~". date("H", strtotime($end_time))."시";
}

function admin_to_datetime($datetime) {
	if (!$datetime || $datetime == "0000-00-00 00:00:00") return "-";
	$seconds = time() - strtotime($datetime);
	// if ($seconds < 43200) return admin_to_elapsed_time($datetime);
	return date("y/m/d", strtotime($datetime)) . ' <span style="color:blue; font-weight: bold">' . date("H:i:s", strtotime($datetime)) . "</span>";	
	
}
function admin_to_date($datetime) {
	if (!$datetime || $datetime == "0000-00-00 00:00:00") return "-";
	return date("Y/m/d", strtotime($datetime));	
}
function admin_to_time($datetime) {
	if (!$datetime || $datetime == "0000-00-00 00:00:00") return "-";
	return '<span style="color:blue; font-weight: bold">' . date("H:i.s", strtotime($datetime)) . '</span>';	
}
function admin_to_elapsed_time($datetime) {
	if (!$datetime || $datetime == "0000-00-00 00:00:00") return "-";
	$seconds = time() - strtotime($datetime);

	$days = intval($seconds / 86400);
	$hours = intval(($seconds / (60 * 60)) % 24);
	$mins = intval(($seconds / 60) % 60);
	$seconds = intval($seconds % 60);
	
	if ($days > 0) return sprintf("<span style='color:#4a4'>%d일%d시간 전</span>", $days, $hours);
	if ($hours > 0) return sprintf("<span style='color:#44a'>%d:%02d 전</span>", $hours, $mins);
	if ($mins > 0) return sprintf("<span style='color:#a44'>%d분 전</span>", $mins);
	return sprintf("<span style='color:#a44'>%d초 전</span>", $seconds);
}
function admin_to_datetime_period($starttime, $endtime) {
	if (!$starttime || $starttime == "0000-00-00 00:00:00") return "-";
	if (!$endtime || $endtime == "0000-00-00 00:00:00") return "-";
	$seconds = strtotime($endtime) - strtotime($starttime);

	$days = intval($seconds / 86400);
	$hours = intval(($seconds / (60 * 60)) % 24);
	$mins = intval(($seconds / 60) % 60);
	$seconds = intval($seconds % 60);
	
	if ($days > 0) return sprintf("<span style='color:#4a4'>%d일 %d시간</span>", $days, $hours);
	if ($hours > 0) return sprintf("<span style='color:#44a'>%d시간 %02d분</span>", $hours, $mins);
	if ($mins > 0) return sprintf("<span style='color:#a44'>%d분</span>", $mins);
	return sprintf("<span style='color:#a44'>%d초</span>", $seconds);	
}

function admin_substr($txt, $start, $length) {
	$ret = "";
	if ($start > 0) $ret .= "..";
	$ret .= mb_substr($txt, $start, $length, "UTF-8");
	if (mb_strlen($txt, "UTF-8") > $start + $length) $ret .= "..";
	return $ret;
}
function admin_number($num, $max_num) {
	if (!$num) return "-";
	if ($max_num && $num >= $max_num) return "-";
	return number_format($num);	
}
// user 정보 표시 관련
$arr_sex = array('M' => '남자', 'F' => '<span style="color:pink">여자</span>');
function admin_user_usersex($sex) {
	global $arr_sex;
	return $arr_sex[$sex];
}
$arr_type = array('user' => '일반회원');
function admin_user_usertype($type) {
	global $arr_type;
	return $arr_type[$type];
}
$arr_status = array('normal' => '정상', 'bad' => '<span style="color:red">불량</span>', 'jail' => '<span style="color:darkred">제제</span>', 'quit' => '탈퇴');
function admin_user_userstatus($type) {
	global $arr_status;
	return $arr_status[$type];
}
function admin_to_money($money) {
	if ($money == 0) return "-";
	$sz_money = number_format($money) . "원";
	if ($money >= 10000) return "<span style='color:#f00'>{$sz_money}</span>";
	if ($money >= 50000) return "<span style='color:#f00'>{$sz_money}</span>";
	if ($money >= 100000) return "<span style='color:#f00'>{$sz_money}</span>";
	return $sz_money;
}
function admin_to_age($birthday) {
	$age = date("Y") - date("Y", strtotime( $birthday )) + 1;
	if ($age >= 19) return "<span style='color:darkblue'>{$age}세</span>";
	return $age . "세";
}
function admin_from_age($age) {
	return date("Y") - $age + 1;
}

function admin_get_url($url, $referer){ //html 페이지 받아오기
	include_once dirname(__FILE__).'/snoopy_ex.php';
	
	$agent = 'Mozilla/5.0 (Windows NT 6.1; rv:43.0) Gecko/20100101 Firefox/43.0';
	$snoopy = new SnoopyEx();
	$snoopy->get_url('https://play.google.com', '', $agent);
	$html=$snoopy->get_url($url, 'https://play.google.com', $agent);
	return $html;
}

function get_array($result, $object = null, $msg = null) 
{
	if (!$object) $object = array();
	$object['result'] = $result;
	if ($msg) $object['msg'] = $msg;
	return $object;
}

function make_visit_log_url($elapsed, $req_file, $request_name, $url, $result, $conn) 
{
	global $g_user_id;
	
	$db_list_id = @mysql_real_escape_string($_REQUEST['listid']);
	$db_user_id = @mysql_real_escape_string($g_user_id);
	$db_page_id = @mysql_real_escape_string($request_name);
	$db_error_msg = "";
	$db_file_name = @mysql_real_escape_string($req_file);
	$db_remote_addr = @mysql_real_escape_string($_SERVER['REMOTE_ADDR']);
	$db_host = "";
	$db_user_agent = "";
	$db_request_uri = @mysql_real_escape_string($url);
	$db_post = "";
	$db_result = @mysql_real_escape_string($result);
	
	$sql = "INSERT INTO site_visit_log (error, userip, userid, reqfile, pageid, listid, uagent, host, url, post, result, delay, msg) 
			VALUES (
				'N',
				'{$db_remote_addr}', 
				'{$db_user_id}', 
				'{$db_file_name}', 
				'{$db_page_id}', 
				'{$db_list_id}',
				'{$db_user_agent}', 
				'{$db_host}', 
				'{$db_request_uri}', 
				'{$db_post}', 
				'{$db_result}', 
				'{$elapsed}',
				'{$db_error_msg}'
			);";
	@mysql_query($sql, $conn);
}


function admin_display_query($title, $source, $query, $ar_column, $connect)
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
	
	$idx = 0;
	while($row = mysql_fetch_array($res))
	{
		$idx ++;
		echo "<tr class='c_info'>";
		for ($i=0; $i < $nFieldCount; $i ++) {
			$type = $ar_column[$i]['type'];
			
			$data = $row[$i];
			if ($type == 'id') $data = admin_user_idlink($data);
			else if ($type == 'order') $data = number_format($idx);
			else if ($type == 'date') $data = admin_to_date($data);
			else if ($type == 'datetime') $data = admin_to_datetime($data);
			else if ($type == 'number') $data = number_format($data);
			else if ($type == 'model') $data = admin_to_devicesearch($data);
			else if ($type == 'multi-model') {
				$arr_data = explode(',', $data);
				$cnt = count($arr_data);
				for ($j = 0; $j < count($arr_data); $j++)
					$arr_data[$j] = admin_to_devicesearch($arr_data[$j]);
				$data = implode(',', $arr_data);
			}
			
			$align = $ar_column[$i]['align'];
			if (!$align) $align = 'center';
		
			echo "<td style='text-align:{$align}'><div style='word-break: break-all;'>" . $data . "</div></td>";
		}
		echo "</tr>";
	}
	echo "</table>";
}
function admin_to_devicesearch($model) {
	$url_model = urlencode($model);
	return "<a href='https://www.google.co.kr/search?q={$url_model}+사양&ie=utf-8' target=_blank>{$model}</a>\n";
}

?>