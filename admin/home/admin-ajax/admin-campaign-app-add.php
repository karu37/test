<?
	$mcode          	   = $_REQUEST['mcode'];
	$app_platform          = $_REQUEST['appplatform'];
	$app_type              = $_REQUEST['apptype'];
	$app_packageid         = $_REQUEST['apppackageid'];
	$app_keyword           = $_REQUEST['appkeyword'];
	$app_homeurl           = $_REQUEST['apphomeurl'];
	$app_execurl           = $_REQUEST['appexecurl'];
	$app_title             = $_REQUEST['apptitle'];
	$app_image_type        = $_REQUEST['appimagetype'];
	$app_image_url         = $_REQUEST['appimageurl'];
	$app_exec_desc         = $_REQUEST['appexecdesc'];
	$app_market            = $_REQUEST['appmarket'];
	$app_content           = $_REQUEST['appcontent'];
	
	$app_gender				= $_REQUEST['appgender'];
	$app_agefrom			= $_REQUEST['appagefrom'];
	$app_ageto				= $_REQUEST['appageto'];
	
	$app_merchant_fee      = $_REQUEST['appmerchantfee'];
	$app_tag_price         = $_REQUEST['apptagprice'];
	$app_exec_weekend      = $_REQUEST['appexecweekend'];
	$app_exec_edate        = $_REQUEST['appexecedate'];
	$app_exec_stime        = $_REQUEST['appexecstime'];
	$app_exec_etime        = $_REQUEST['appexecetime'];
	$app_exec_hourly_cnt   = $_REQUEST['appexechourlycnt'];
	$app_exec_daily_cnt    = $_REQUEST['appexecdailycnt'];
	$app_exec_total_cnt    = $_REQUEST['appexectotalcnt'];
	
	$app_publisher_level 	= $_REQUEST['apppublisherlevel'];
	$app_level_1_active_date 	= $_REQUEST['level1activedate'];
	$app_level_2_active_date 	= $_REQUEST['level2activedate'];
	$app_level_3_active_date 	= $_REQUEST['level3activedate'];
	$app_level_4_active_date 	= $_REQUEST['level4activedate'];
	
	
	if ($mcode =="" || $app_platform == "" || $app_type == "" || $app_title == "" || $app_image_url == "" || $app_exec_desc == "" || $app_exec_stime == "" || $app_exec_etime == "") return_die(false, null, '정보를 모두 입력해 주십시요.');
	if ($app_platform == 'A' && !$app_packageid) return_die(false, null, '패키지 정보가 없습니다.');
	
	$app_exec_stime        = sprintf("%02d:00:00", $app_exec_stime);
	$app_exec_etime        = sprintf("%02d:00:00", $app_exec_etime);
	
	$db_mcode          		= mysql_real_escape_string($mcode);
	$db_app_platform          = mysql_real_escape_string($app_platform);
	$db_app_type              = mysql_real_escape_string($app_type);
	$db_app_packageid         = mysql_real_escape_string($app_packageid);
	$db_app_keyword           = mysql_real_escape_string($app_keyword);
	$db_app_homeurl           = mysql_real_escape_string($app_homeurl);
	$db_app_execurl           = mysql_real_escape_string($app_execurl);
	$db_app_title             = mysql_real_escape_string($app_title);
	$db_app_image_url         = mysql_real_escape_string($app_image_url);
	$db_app_exec_desc         = mysql_real_escape_string($app_exec_desc);
	$db_app_market            = mysql_real_escape_string($app_market);
	$db_app_content           = mysql_real_escape_string($app_content);
	
	$db_app_gender          = mysql_real_escape_string($app_gender);
	$db_app_agefrom         = mysql_real_escape_string($app_agefrom);
	$db_app_ageto           = mysql_real_escape_string($app_ageto);
		
	$db_app_merchant_fee      = mysql_real_escape_string($app_merchant_fee);
	$db_app_tag_price      = mysql_real_escape_string($app_tag_price);
	$db_app_exec_weekend        = mysql_real_escape_string($app_exec_weekend);
	$db_app_exec_edate        = mysql_real_escape_string($app_exec_edate);
	$db_app_exec_stime        = mysql_real_escape_string($app_exec_stime);
	$db_app_exec_etime        = mysql_real_escape_string($app_exec_etime);
	$db_app_exec_hourly_cnt   = mysql_real_escape_string($app_exec_hourly_cnt);
	$db_app_exec_daily_cnt    = mysql_real_escape_string($app_exec_daily_cnt);
	$db_app_exec_total_cnt    = mysql_real_escape_string($app_exec_total_cnt);

	$db_app_publisher_level 	= mysql_real_escape_string($app_publisher_level);
	$db_app_level_1_active_date = mysql_real_escape_string($app_level_1_active_date);
	$db_app_level_2_active_date = mysql_real_escape_string($app_level_2_active_date);
	$db_app_level_3_active_date = mysql_real_escape_string($app_level_3_active_date);
	$db_app_level_4_active_date = mysql_real_escape_string($app_level_4_active_date);

	// Unique 한 app_key 생성 md5(packageid + title + random value)
	do {
		$app_key = "LOC".md5($app_package_id . $app_title . rand(0, 1000000));
		$db_app_key = mysql_real_escape_string($app_key);
		
		$sql = "SELECT id FROM app_t WHERE app_key = '{$db_app_key}'";
		$row = mysql_fetch_assoc(mysql_query($sql, $conn));
		if (!$row['id']) break;
	} while(true);
	
	// $app_image_url 이 base64 이미지 데이터인 경우로 처리함.
	if ($app_image_type == "base64") 
	{
		$image_data = base64_decode($app_image_url);
		$db_imagedata = mysql_real_escape_string($image_data);
		
		$sql = "INSERT al_image_data_t (app_key, data) VALUES ('{$db_app_key}', '{$db_imagedata}')";
		mysql_execute($sql, $conn);
		$id = mysql_insert_id();
		
		$url = "http://image.aline-soft.kr/campaign/{$id}.jpg";
		$db_app_image_url = mysql_real_escape_string($url);
	}	
	
	// insert
	$sql = "INSERT INTO al_app_t (
		app_key, 
		mcode, 
		app_title, 
		app_content, 
		app_iconurl, 
		app_packageid, 
		app_keyword,
		app_homeurl,
		app_execurl, 

		app_platform,
		app_gender, 
		app_agefrom,
		app_ageto,
		app_exec_type, 
		app_exec_desc, 
		app_market, 
		app_merchant_fee, 
		app_tag_price, 
		
		exec_weekend,
		exec_edate, 
		exec_stime, 
		exec_etime, 
		
		exec_hour_max_cnt, 
		exec_day_max_cnt, 
		exec_tot_max_cnt, 
		publisher_level,
		level_1_active_date,
		level_2_active_date,
		level_3_active_date,
		level_4_active_date,
		is_mactive,
		last_active_time, 
		up_date, 
		reg_date
	) VALUES (
		'$db_app_key', 
		'$db_mcode', 
		'$db_app_title', 
		'$db_app_content', 
		'$db_app_image_url', 
		IF('$db_app_packageid' <> '', '$db_app_packageid', NULL),
		IF('$db_app_keyword' <> '', '$db_app_keyword', NULL),
		IF('$db_app_homeurl' <> '', '$db_app_homeurl', NULL),
		IF('$db_app_execurl' <> '', '$db_app_execurl', NULL),

		'$db_app_platform',
		IF('$db_app_gender' <> 'A', '$db_app_gender', NULL),
		IF('$db_app_agefrom' <> '0' OR '$db_app_ageto' <> '100', '$db_app_agefrom', NULL),
		IF('$db_app_agefrom' <> '0' OR '$db_app_ageto' <> '100', '$db_app_ageto', NULL),
		'$db_app_type', 
		'$db_app_exec_desc', 
		'$db_app_market', 
		'$db_app_merchant_fee', 
		'$db_app_tag_price', 
		
		'$db_app_exec_weekend',
		IF('$db_app_exec_edate' <> '', '$db_app_exec_edate', NULL),
		IF('$db_app_exec_stime' <> '00:00:00' OR '$db_app_exec_etime' <> '24:00:00', '$db_app_exec_stime', NULL),
		IF('$db_app_exec_stime' <> '00:00:00' OR '$db_app_exec_etime' <> '24:00:00', '$db_app_exec_etime', NULL),
		
		IF('$db_app_exec_hourly_cnt' <> '', '$db_app_exec_hourly_cnt', NULL),
		IF('$db_app_exec_daily_cnt' <> '', '$db_app_exec_daily_cnt', NULL),
		IF('$db_app_exec_total_cnt' <> '', '$db_app_exec_total_cnt', NULL),
		IF('$db_app_publisher_level' <> '9', '$db_app_publisher_level', NULL),
		IF('$db_app_level_1_active_date' <> '', '$db_app_level_1_active_date', NULL),
		IF('$db_app_level_2_active_date' <> '', '$db_app_level_2_active_date', NULL),
		IF('$db_app_level_3_active_date' <> '', '$db_app_level_3_active_date', NULL),
		IF('$db_app_level_4_active_date' <> '', '$db_app_level_4_active_date', NULL),
		
		'N',
		NOW(), 
		NOW(), 
		NOW()
	);";

 	mysql_execute($sql, $conn);

	// 시간당 설치 개수 설정
	if ($app_type == 'R' || $app_type == 'F' ) {
	 	$sql = "INSERT app_auto_config_t (app_key, hourly_limit) VALUES ('{$db_app_key}', '{$db_autolaunch_hourly_limit}')";
	 	mysql_execute($sql, $conn);
	 }
	 
 	$ar_data['app_key'] = $app_key;
 	return_die(true, $ar_data);

?>