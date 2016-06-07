<?
// ---------------------------------------
// $site_define 로딩하기
// ---------------------------------------

	$default_file = dirname(__FILE__).'/../../../site-definition-default.php';
	$site_file = dirname(__FILE__).'/../../../site-definition.php';
	if (file_exists($site_file)) $file = $site_file; else $file = $default_file;
	
	$txt = file_get_contents($file);
	if (preg_match('/\/\*(.*)\*\//usim', $txt, $matched)) {
		eval($matched[1]);
	}
	
// ---------------------------------------

// util_ 로 시작하는 모든 php include함.
include_ex( dirname(__FILE__)."/util_*.php" );

// include by wildcard
function include_ex( $files ) {
	foreach (glob($files) as $filename){ include $filename; }
}

// if empty then return ""
function ifempty($p1, $p2) {
	return $p1 == "" ? $p2 : $p1;
}

// 6.18(목) 13:54, 6.3(금) 9:30 (YmdHis vs YnjGis)
function to_displaytime($datetime, $type = 0){
	$time = strtotime($datetime);
	$week_kor=array('일','월','화','수','목','금','토');
	$week_num = date("w", $time);
	$time_week = $week_kor[$week_num];
	
	if ($type == 0) return date("n.d({$time_week}) G:i", $time);
	if ($type == 1) return date("n.d G:i", $time);
	if ($type == 10) return date("m/d H:i", $time);
	if ($type == 11) return date("y/m/d H:i", $time);
	if ($type == 12) return date("Y/m/d H:i", $time);
};
function to_displaydate($datetime, $type = 0){
	$time = strtotime($datetime);
	$week_kor=array('일','월','화','수','목','금','토');
	$week_num = date("w", $time);
	$time_week = $week_kor[$week_num];
	if ($type == 0) return date("n.d({$time_week})", $time);
	if ($type == 1) return date("n.d", $time);
	if ($type == 10) return date("m/d", $time);
	if ($type == 11) return date("y/m/d", $time);
	if ($type == 12) return date("Y/m/d", $time);
};

function to_phoneno($telno) {
	if (strlen($telno) <= 8) {
		if (preg_match('/^([\d]+?)([\d]{4})$/', $telno, $matched)) return $matched[1] . '-' . $matched[2];
		return $telno;
	} else if (strlen($telno) <= 11) {
		if (preg_match('/^(02)([\d]{3,4})([\d]{4})$/', $telno, $matched)) return $matched[1] . '-' . $matched[2] . '-' . $matched[3];
		if (preg_match('/^([\d]{3})([\d]{3,4})([\d]{4})$/', $telno, $matched)) return $matched[1] . '-' . $matched[2] . '-' . $matched[3];
		return $telno;
	} else if (substr($telno, 0, 3) == "+82") {
		if (preg_match('/^(\+822)([\d]{3,4})([\d]{4})$/', $telno, $matched)) return $matched[1] . '-' . $matched[2] . '-' . $matched[3];
		if (preg_match('/^(\+82\d{2})([\d]{3,4})([\d]{4})$/', $telno, $matched)) return $matched[1] . '-' . $matched[2] . '-' . $matched[3];
		return $telno;
	} 
	if (preg_match('/^(.{2,}?)([\d]{3,4})([\d]{4})$/', $telno, $matched)) return $matched[1] . '-' . $matched[2] . '-' . $matched[3];
	return $telno;		
}	

function to_html($txt) {
	return str_replace("\n", "<br>", htmlspecialchars($txt));
}
function from_html($txt) {
	return htmlspecialchars_decode(str_replace("<br>", "\n", $txt));
}

function get_timestamp() 
{
    list($usec, $sec) = explode(" ", microtime());
    return round(((float)$usec + (float)$sec) * 1000);
}

// 사용중인 Charset이 UTF-8인 경우 UTF-8로 설정해야 함.
function Ux33Encoding($szText, $key = "garden", $textCharSet="utf-8")
{
	if (preg_match("/utf-8/i", $textCharSet)) 	
		$szText = iconv("UTF-8", "EUC-KR", $szText);
	
	$arCode = array(); 
	$keyLength = strlen($key); 
	$keyFactor = 1;
	for($i = 0;$i < $keyLength ; $i++) $arCode[$i] = $key[$i]; 
	for($i = 0;$i < $keyLength ; $i++) $keyFactor = ($keyFactor * ord($key[$i])) % 0xFF + 1;
	
	$nLength = strlen($szText);
	for ($i=0; $i < $nLength; $i++)
	{
		$nChar = ord($szText[$i]);
		$nAddedChar = ($nChar + $keyFactor + ord($arCode[$i%$keyLength]) + (($i+1)*$nLength)) % 256;
		$szEncoded .= sprintf("%02x", $nAddedChar);
	}
	$retLength = strlen($szEncoded);
	$retValue=1;
	for ($i=0; $i < $retLength; $i++) $retValue = ($retValue * ord($szEncoded[$i])) % 0xFFFF + 1;
	$szEncoded .= sprintf("%04x", $retValue);	
	return $szEncoded;
}

// 사용중인 Charset이 UTF-8인 경우 UTF-8로 설정해야 함.
function Ux33Decoding($szText, $key = "garden", $OutCharSet="utf-8")
{
	$szEncoded=substr($szText, 0, strlen($szText)-4);
	$retLength = strlen($szEncoded);
	$retValue=1;
	for ($i=0; $i < $retLength; $i++) $retValue = ($retValue * ord($szEncoded[$i])) % 0xFFFF + 1;
	if (sprintf("%04x", $retValue) != substr($szText, strlen($szText)-4, 4)) return "";
	$szText = $szEncoded;
	
	$arCode = array(); 
	$keyLength = strlen($key); 
	$keyFactor = 1;
	for($i = 0;$i < $keyLength; $i++) $arCode[$i] = $key[$i]; 
	for($i = 0;$i < $keyLength; $i++) $keyFactor = ($keyFactor * ord($key[$i])) % 0xFF + 1;
	
	$nLength = strlen($szText);
	$nDecodedLen = $nLength / 2 ;
	for ($i=0, $j=0; $i < $nLength; $i+=2, $j++)
	{
		$nChar = intval(substr($szText, $i, 2), 16);
		$szDecoded .= sprintf("%c", ($nChar - $keyFactor + 0x10000 - ord($arCode[$j%$keyLength]) - (($j+1)*$nDecodedLen)) % 256);
	}
	
	if (preg_match("/utf-8/i", $OutCharSet)) 	// UTF-8, utf-8
		$szDecoded = iconv("EUC-KR", "UTF-8", $szDecoded);
	
	return $szDecoded;
}

function return_die($result, $object = null, $msg = null, $error_sql = null) {
	global $sql, $conn;
	if (!$object) $object = array();
	array_walk($object,function(&$item){if ($item === null) $item="";});
	$object['result'] = $result;
	if ($msg) $object['msg'] = $msg;
	
	// code로부터 오류 메시지를 설정함.
	set_error_msg($object);
	
	echo json_encode($object, JSON_UNESCAPED_UNICODE);
	
	if (!$result && @mysql_errno($conn)) {
		if ($error_sql == null) $error_sql = $sql;
		$db_sql = mysql_real_escape_string($error_sql);
		$db_errno = mysql_real_escape_string(@mysql_errno($conn));
		$db_error = mysql_real_escape_string(@mysql_error($conn));
		$sql = "INSERT INTO _error_sql_log (`type`, `sql`, errno, error, reg_date) VALUES('M', '{$db_sql}', '{$db_errno}', '{$db_error}', NOW());";
		@mysql_query($sql, $conn);
	}
	
	make_visit_log($result, $object, null, null, null, null, null, $conn);
	die();
}

function make_visit_log($result, $ar_response, $elapsed_time = null, $pageid = null, $adid = null, $req_url = null, $ar_post_param = null, $conn = null) {
	
	_make_log($result, $ar_response, $elapsed_time, $pageid, $adid, $req_url, $ar_post_param, $conn, 'V');
}
function make_action_log($result, $ar_response, $elapsed_time = null, $pageid = null, $adid = null, $req_url = null, $ar_post_param = null, $conn = null) {
	_make_log($result, $ar_response, $elapsed_time, $pageid, $adid, $req_url, $ar_post_param, $conn, 'A');
}

function _make_log($result, $ar_response, $elapsed_time = null, $pageid = null, $adid = null, $req_url = null, $ar_post_param = null, $conn = null, $tb_target = 'V')
{
	global $dev_mode, $_start_api_tm;
	
	if ($elapsed_time === null) $elapsed_time = get_timestamp() - $_start_api_tm;
	if ($pageid === null) $pageid = $_REQUEST['id'];
	if ($adid === null) $adid = $_REQUEST['adid'];
	if ($req_url === null) $req_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	if ($ar_post_param === null) $ar_post_param = $_POST;

	$db_result = @mysql_real_escape_string($result);
	$db_pageid = @mysql_real_escape_string($pageid);
	$db_adid = @mysql_real_escape_string($adid);
	$db_req_url = @mysql_real_escape_string($req_url);
	$db_remote_addr = @mysql_real_escape_string($_SERVER['REMOTE_ADDR']);
	$db_elapsed_time = @mysql_real_escape_string($elapsed_time);
	
	$db_post = '';
	if ($ar_post_param) {
		if (gettype($ar_post_param) == 'string')
			$db_post = @mysql_real_escape_string($ar_post_param);
		else if (gettype($ar_post_param) == 'array') {
			foreach($ar_post_param as $key => $val) {
				$db_post .= @mysql_real_escape_string("{$key}={$val}\n");
			}
		}
	}
	
	$db_response = '';
	if ($ar_response) {
		if (gettype($ar_response) == 'string') {
			$db_response = @mysql_real_escape_string($ar_response);
		}
		else if (gettype($ar_response) == 'array') {
			foreach($ar_response as $key => $val) {
				$db_response .= @mysql_real_escape_string("{$key}={$val}\n");
			}
		}
	}
	
	if ($tb_target == 'A') $tbname = 'site_action_log';
	else $tbname = 'site_visit_log';
	
	$sql = "INSERT INTO {$tbname} (result, pageid, adid, requrl, ip, elapsed, post, response)
			VALUES ('$db_result', 
					'$db_pageid',
					'$db_adid',
					'$db_req_url',
					'$db_remote_addr',
					'$db_elapsed_time',
					'$db_post',
					'$db_response');";
	mysql_query($sql, $conn);	
}

function post($url, $ar_post_param, $timeout_sec = 60)
{
	$options = array(
		'http' => array(
			'header' => "Content-type: application/x-www-form-urlencoded\r\n",
			'method' => 'POST',
			'content' => http_build_query($ar_post_param),
			'timeout' => $timeout_sec,
			),
		);
	
	$context = stream_context_create($options);
	$result = file_get_contents($url, false, $context);	
	return $result;
}

function add_script($script) {
	global $post_script;
	$post_script .= ($script . "\n");
}

function is_default($value, $default_value) {
	if ( $default_value && (!$value || $value == $default_value) ) return true;
	if ( !$default_value && !$value ) return true;
	return false;
}

function concat_url($url_body, $uri) {
	return $url_body . (strrpos($url_body, '?') === false ? '?' : '&') . $uri;
}
?>