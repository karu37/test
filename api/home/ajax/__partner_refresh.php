<?
	echo "Refresh : " . $_SERVER['SERVER_ADDR'] . '<br>';

	//-----------------------------------------------------
	// 특정 주기대로 자동 업데이트 함.
	include dirname(__FILE__).'/_partner_sucomm.php';
	update_sucomm_app(true, $conn);
	echo "sucom OK<br>";
	//-----------------------------------------------------
	
	//-----------------------------------------------------
	// 특정 주기대로 자동 업데이트 함.
	include dirname(__FILE__).'/_partner_ohc.php';
	update_ohc_app(true, $conn);
	echo "OHC OK<br>";
	//-----------------------------------------------------
	
	//-----------------------------------------------------
	// 특정 주기대로 자동 업데이트 함.
	include dirname(__FILE__).'/_partner_appang.php';
	update_ohc_appang(true, $conn);
	echo "APPANG OK<br>";
	//-----------------------------------------------------
	
?>