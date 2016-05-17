<?
	$sz_url = $_REQUEST['url'];
	$content = file_get_contents($sz_url);
// 	$content = admin_get_mobile_url($sz_url);
	if (!$content) return_die(false, null, '이미지 정보를 얻지 못했습니다.');
	
	$ar_data['base64'] = base64_encode($content);
	return_die(true, $ar_data);
?>
