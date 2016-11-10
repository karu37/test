<?
	// 요청 URL (pcode = aline)
	//	http://api.aline-soft.kr/ajax-request.php?id=__publisher-ads-list&pcode=autoring_p

	$pcode = $_REQUEST['pcode'];
	$db_pcode = mysql_real_escape_string($pcode);

	// publisher의 mactive 상태 리턴
	$sql = "SELECT is_mactive FROM al_publisher_t WHERE pcode = '{$db_pcode}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	$pub_mactive = $row['is_mactive'];

	// 대상 광고 쿼리
	$ar_time = mysql_get_time($conn);
	$sql = get_query_app_list($pcode, $ar_time, false, ($pub_mactive == 'T'), $conn);
	$result = mysql_query($sql, $conn);

?>
<head>
	<title>A-Line 관리자</title>
	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
	<script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
	<script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
	<style>
		* {font-size:12px; }
		th {padding: 0 4px}
		td {padding: 0 2px}
		.mini-btn 	{padding:2px; font-size:12px}
		.c			{text-align:center}
	</style>
</head>
<body style='background-color: white; overflow-y: hidden'>
	<div style='background-color: white'>
		<table width=100% border=1 cellpadding=0 cellspacing=0>
		<tr>
			<th>Idx</th>
			<th>아이콘</th>
			<th>제공</th>
			<th>제목/광고키</th>
			<th>타입</th>
			<th>M가격</th>
			<th>P가격</th>
			<th>나이제한</th>
			<th>성별제한</th>
			<th>시간제한</th>
		</tr>
		<?
			$i = 0;
			$ar_type_names = array('I' => '<b style="color:blue">설치형</b>', 'E' => '<b style="color:darkgreen">실행형</b>', 'W' => '수행형');
			while ($row = mysql_fetch_assoc($result)) {
				$i++;
				if ($row['tot_not_complished'] != 'Y' || $row['edate_not_expired'] != 'Y') {
					continue;
				}

				// 표시 차단 대상 (위에서 필터링 안된 대상)
				// 금액이 0 인 경우
				if ( intval($row['publisher_fee']) <= 0 ) continue;

				$app_merchant_fee = number_format($row['app_merchant_fee']);
				$publisher_fee = number_format($row['publisher_fee']);

				echo "<tr>";
				echo "<td><div style='text-align:center'>{$i}</div></td>\n";
				echo "<td><div><img src='{$row['app_iconurl']}' width=40px /></div></td>\n";
				echo "<td><div style='text-align:center'>{$row['merchant_name']}</div></td>\n";
				echo "<td><div>{$row['app_title']}<br>{$row['app_key']}</div></td>\n";
				echo "<td><div style='text-align:center'>{$ar_type_names[$row['app_exec_type']]}</div></td>\n";
				echo "<td><div style='text-align:center'>{$app_merchant_fee}</div></td>\n";
				echo "<td><div style='text-align:center'>{$publisher_fee}</div></td>\n";
				echo "<td><div style='text-align:center'>{$row['app_agefrom']} ~ {$row['app_ageto']}</div></td>\n";
				echo "<td><div style='text-align:center'>{$row['app_gender']}</div></td>\n";
				echo "<td><div style='text-align:center'>{$row['exec_stime']} ~ {$row['exec_etime']}</div></td>\n";
				echo "<tr>";

			}
		?>
		</table>
	</div>

<script>

var basic_util = {
	var_dump: function(obj) {
		var out = '';
    	for (var i in obj) {
        	out += i + ": " + obj[i] + "\n";
        }
    	return out;
	},
};
window.var_dump = basic_util.var_dump;
var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}
function base64_encode(str) { if (!str) return null; return Base64.encode(str);}
function base64_decode(str) { if (!str) return null; return Base64.decode(str);}


var page = function(){

	var timer_posting = null;
	var time_pagestart = new Date();
	var n_last_height = 0;
	var fn = {
		init: function() {

			// parent 에게 Height Posting을 한다.
			timer_posting = setInterval(function() {
				var data = {'height': $(document).height()};

				// 높이가 달라진 경우에만 Posting하기
				if (n_last_height != $(document).height()) {
					n_last_height = $(document).height();
					top.window.postMessage(data, 'http://admin.aline-soft.kr');
				}

				if ( new Date() - time_pagestart >= 10 * 1000 ) clearInterval( timer_posting );
			}, 100);
		},

	};

	fn.init();
	return fn;
}();

</script>

</body>
