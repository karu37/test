<?
	// 테스트 : http://api.aline-soft.kr/services/facebook.html?p=eyJ1IjoiZXlKaGFXUWlPaUl4TURrNE9ETXpJbjA9IiwibSI6IjMyZWQxY2FjNmU2YmZiMmJhMDQ0ZTYwZWI3ODk3OWVmIn0%3D&dev=1
	header("Content-Type: text/html; charset=utf-8");

	include dirname(__FILE__)."/../php_lib/util.php";

	$dev = $_REQUEST['dev'];
	$param = $_REQUEST['p'];

	$is_error = false;
	$error_msg = '';
	do {

		$ar_param = json_decode(base64_decode($param), true);
		if (!$ar_param) {
			$is_error = true;
			$error_msg = '요청이 올바르지 않습니다(-1).';
			break;
		}

		$userdata = $ar_param['u'];
		$md5 = $ar_param['m'];
		if (md5('aline' . $userdata) != $md5) {
			$is_error = true;
			$error_msg = '요청이 올바르지 않습니다(-2).';
			break;
		}

		$conn = dbConn();

		if (!$conn) {
			$is_error = true;
			$error_msg = '서비스 연결이 원활하지 않습니다.';
			break;
		}

		$js_data = json_decode(base64_decode(str_replace('*','=',$userdata)), true);
		$userapp_id = $js_data['aid'];

		$db_userapp_id = mysql_real_escape_string($userapp_id);

		// 이미 적립된 상태인지 확인 및 app_execurl 얻어오기
		$sql = "SELECT a.id, a.status, a.permanent_fail, b.app_execurl, b.is_active, b.is_mactive FROM al_user_app_t a INNER JOIN al_app_t b ON a.app_key = b.app_key WHERE a.id = '{$db_userapp_id}'";
		$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
		if (!$row) {
			$is_error = true;
			$error_msg = '참여 시작 정보가 없습니다(-3).';
			break;
		}
		if ($row['status'] == 'D') {
			$is_error = true;
			$error_msg = '이미 적립된 상태입니다.';
			break;
		}
		if ($row['permanent_fail'] == 'Y') {
			$is_error = true;
			$error_msg = '더 이상 참여할 수 없습니다.';
			break;
		}
		if (!$row['app_execurl']) {
			$is_error = true;
			$error_msg = '잘못된 참여 정보입니다(-4).';
			break;
		}
		if ($row['is_active'] != 'Y') {
			$is_error = true;
			$error_msg = '이미 종료된 광고입니다.';
			break;
		}
		if (!$dev && $row['is_mactive'] != 'Y') {
			$is_error = true;
			$error_msg = '광고가 임시 중단된 상태입니다.';
			break;
		}

		// m.facebook.com ==> www.facebook.com 으로 바꿔서 처리하도록 한다.
		$siteurl = $row['app_execurl'];
		$siteurl = str_replace("m.facebook.com", "www.facebook.com", $siteurl);

		// 전달받은 param1
		$param1 = urlencode($param);

		// done에게 전달하는 param2
		$ar_param2 = array();
		$ar_param2['u'] = base64_encode(json_encode(array('aid' => strval($userapp_id))));
		$ar_param2['m'] = md5('aline-done' . $ar_param2['u']);
		$param2 = urlencode(base64_encode(json_encode($ar_param2)));

	} while(false);
?>

<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, target-densitydpi=medium-dpi, user-scalable=0" />
</head>
<body>
<? if (!$is_error) { ?>
<div id="fb-root"></div>

<script>
	var alike = {
		site: null,
		state: null,
		init: function(url) {
			alike.site = alike.rtrim(url);
			alike.state = 'U';
		},
		like: function(resp) {
			if (alike.rtrim(resp) != alike.site) {
				window.location.replace('facebook-ok.php?c=EE');
			}
			else if (alike.state == 'N') {
				window.location.replace('facebook-ok.php?c=EA');
			}
			else if (alike.state == 'U') {
				window.location.replace('facebook-ok.php?c=Y&p2=<?=$param2?>');
			}
			else {
				window.location.replace('facebook-ok.php?c=EU');
			}
		},
		unlike: function(resp) {
			if (alike.rtrim(resp) == alike.site)
				alike.state = 'N';
		},
		rtrim: function(txt) {
			var ar_txt = txt.match(/^(.*?)(\/?|\/?\?.*)$/);
			if (!ar_txt) return txt;
			return ar_txt[1];
		},
	};

	alike.init('<?=$siteurl?>');
</script>
<script type='text/javascript'>//<![CDATA[

	window.addEventListener('load', function() {

		window.fbAsyncInit = function() {
			FB.init({
				appId: '770588606402454',
				status: true,
				cookie: true,
				xfbml: true,
				oauth: true
			});
			FB.Event.subscribe('edge.create', alike.like);
			FB.Event.subscribe('edge.remove', alike.unlike);
		};
		(function(d) {
			var js, id = 'facebook-jssdk';
			if (d.getElementById(id)) {
				return;
			}
			js = d.createElement('script');
			js.id = id;
			js.async = true;
			js.src = "//connect.facebook.net/ko_KR/all.js";
			d.getElementsByTagName('head')[0].appendChild(js);
		}(document));

	});//]]>

</script>

	<div style='text-align: center; padding-top:100px'>
		<div style='padding: 5px; font-weight: bold; font-size: 18px; letter-spacing: -0.05em; color: darkblue'>페이지 좋아요를 클릭해 주세요</div>
		<div class="fb-page" data-href="<?=$siteurl?>" data-tabs="timeline" data-width="250px" data-height="70px" data-small-header="false" data-adapt-container-width="true" data-hide-cover="true" data-show-facepile="true"><blockquote cite="https://www.facebook.com/samsung" class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/samsung"></a></blockquote></div>
	</div>

<? } else { ?>


	<div style='text-align: center; padding-top:100px'>
		<div style='padding: 5px; font-weight: bold; font-size: 18px; letter-spacing: -0.05em; color: red'>오류가 발생되었습니다.</div>
		<div style='padding: 5px; font-weight: bold; font-size: 13px; letter-spacing: -0.05em; color: black'>오류 : <?=$error_msg?></div>
	</div>

<? } ?>
</body>

</html>
