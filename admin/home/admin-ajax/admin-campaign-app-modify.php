<?
	$mcode          	   = $_REQUEST['mcode'];
	$appkey          	   = $_REQUEST['appkey'];
	$app_platform          = $_REQUEST['appplatform'];
	$app_type              = $_REQUEST['apptype'];
	$app_packageid         = $_REQUEST['apppackageid'];
	$app_keyword           = $_REQUEST['appkeyword'];
	$app_homeurl           = $_REQUEST['apphomeurl'];
	$app_execurl           = $_REQUEST['appexecurl'];
	$app_etc           	   = $_REQUEST['appetc'];
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
	
	
	if ($mcode =="" || $appkey =="" || $app_platform == "" || $app_type == "" || $app_title == "" || $app_image_url == "" || $app_exec_desc == "" || $app_exec_stime == "" || $app_exec_etime == "") return_die(false, null, '정보를 모두 입력해 주십시요.');
	if ($app_platform == 'A' && !$app_packageid) return_die(false, null, '패키지 정보가 없습니다.');
	
	$app_exec_stime        = sprintf("%02d:00:00", $app_exec_stime);
	$app_exec_etime        = sprintf("%02d:00:00", $app_exec_etime);
	
	$db_mcode          		= mysql_real_escape_string($mcode);
	$db_appkey          	= mysql_real_escape_string($appkey);
	$db_app_platform          = mysql_real_escape_string($app_platform);
	$db_app_type              = mysql_real_escape_string($app_type);
	$db_app_packageid         = mysql_real_escape_string($app_packageid);
	$db_app_keyword           = mysql_real_escape_string($app_keyword);
	$db_app_homeurl           = mysql_real_escape_string($app_homeurl);
	$db_app_execurl           = mysql_real_escape_string($app_execurl);
	$db_app_etc           	= mysql_real_escape_string($app_etc);
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

	$sql = "UPDATE al_app_t
			SET 
				app_title = '$db_app_title', 
				app_content = '$db_app_content', 
				app_iconurl = '$db_app_image_url', 
				app_packageid = IF('$db_app_packageid' <> '', '$db_app_packageid', NULL), 
				app_keyword = IF('$db_app_keyword' <> '', '$db_app_keyword', NULL),
				app_homeurl = IF('$db_app_homeurl' <> '', '$db_app_homeurl', NULL),
				app_execurl = IF('$db_app_execurl' <> '', '$db_app_execurl', NULL), 
				app_etc = IF('$db_app_etc' <> '', '$db_app_etc', NULL), 
		
				app_platform = '$db_app_platform',
				app_gender = IF('$db_app_gender' <> 'A', '$db_app_gender', NULL), 
				app_agefrom = IF('$db_app_agefrom' <> '0' OR '$db_app_ageto' <> '100', '$db_app_agefrom', NULL),
				app_ageto = IF('$db_app_agefrom' <> '0' OR '$db_app_ageto' <> '100', '$db_app_ageto', NULL),
				app_exec_type = '$db_app_type', 
				app_exec_desc = '$db_app_exec_desc', 
				app_market = '$db_app_market', 
				app_merchant_fee = '$db_app_merchant_fee', 
				app_tag_price = '$db_app_tag_price', 
				exec_weekend = '$db_app_exec_weekend',
				exec_edate = IF('$db_app_exec_edate' <> '', '$db_app_exec_edate', NULL), 
				exec_stime = IF('$db_app_exec_stime' <> '00:00:00' OR '$db_app_exec_etime' <> '24:00:00', '$db_app_exec_stime', NULL), 
				exec_etime = IF('$db_app_exec_stime' <> '00:00:00' OR '$db_app_exec_etime' <> '24:00:00', '$db_app_exec_etime', NULL), 
				exec_hour_max_cnt = IF('$db_app_exec_hourly_cnt' <> '', '$db_app_exec_hourly_cnt', NULL), 
				exec_day_max_cnt = IF('$db_app_exec_daily_cnt' <> '', '$db_app_exec_daily_cnt', NULL), 
				exec_tot_max_cnt = IF('$db_app_exec_total_cnt' <> '', '$db_app_exec_total_cnt', NULL), 
				
				publisher_level = IF('$db_app_publisher_level' <> '9', '$db_app_publisher_level', NULL),
				level_1_active_date = IF('$db_app_level_1_active_date' <> '', '$db_app_level_1_active_date', NULL),
				level_2_active_date = IF('$db_app_level_2_active_date' <> '', '$db_app_level_2_active_date', NULL),
				level_3_active_date = IF('$db_app_level_3_active_date' <> '', '$db_app_level_3_active_date', NULL),
				level_4_active_date = IF('$db_app_level_4_active_date' <> '', '$db_app_level_4_active_date', NULL),
				up_date = NOW() 
			WHERE app_key = '{$db_appkey}'";
 	mysql_execute($sql, $conn);

 	return_die(true, $ar_data);

?>