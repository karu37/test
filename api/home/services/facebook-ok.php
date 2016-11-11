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

			$result = simple_user_app_saving($userdata, $error_msg, $conn);
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

		// --------------------------------------
		// FAIL SAVING
		// --------------------------------------
		do {

			$ar_param = json_decode(base64_decode($param), true);
echo 'A';
			if (!$ar_param) break;

			$userdata = $ar_param['u'];
			$md5 = $ar_param['m'];
echo 'B';
			if (md5('aline-done' . $userdata) != $md5) break;

			$conn = dbConn();

echo 'C';
			simple_user_app_saving_fail($userdata, '-4001', $display_txt, $conn);

		} while(false);

	}
	else if ($code == 'EU') {
		$display_txtcolor = 'darkred';
		$display_txt = '알 수 없는 오류입니다.';
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
