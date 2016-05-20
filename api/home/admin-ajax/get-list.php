<?
	$pub_mactive = get_publisher_info("name", $ar_publisher);
	if (!$is_mactive || $is_mactive == 'D') return_die(false, array('code'=>'1000'), '유효하지 않은 매체코드입니다.');
	
	
	
		
	return_die(true, null, "ok");

?>