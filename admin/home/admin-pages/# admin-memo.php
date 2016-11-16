<?/*


// Column Sorting
$orderby = "ORDER BY " . ifempty($_REQUEST['orderby'], 'app.id') . " " . ifempty($_REQUEST['order'], 'DESC');
<a href='#' onclick="window.location.href=window.location.href.set_url_param('orderby', 'app.id').set_url_param('order', '<?=($_REQUEST['orderby']=="app.id"&&$_REQUEST['order']=="DESC")?"ASC":"DESC"?>').del_url_param('page')">Idx</a>


//-----------------------------------
ALine - 개발환경 설정
//-----------------------------------

127.0.0.1		admin.aline-soft.kr image.aline-soft.kr
127.0.0.1		www.aline-soft.kr aline-soft.kr partner.aline-soft.kr

<VirtualHost *:80>
	ServerName admin.aline-soft.kr
	ServerAlias admin.aline-soft.kr
	ServerAdmin a@localhost
	DocumentRoot "D:\Develope_Web\www.aline-soft.kr\admin\home"

	<Directory />
		Options FollowSymLinks
		AllowOverride FileInfo
		Require all granted
	</Directory>
</VirtualHost>

<VirtualHost *:80>
	ServerName image.aline-soft.kr
	ServerAlias image.aline-soft.kr
	ServerAdmin a@localhost
	DocumentRoot "D:\Develope_Web\www.aline-soft.kr\image\home"

	<Directory />
		Options FollowSymLinks
		AllowOverride FileInfo
		Require all granted
	</Directory>
</VirtualHost>

//-----------------------------------
ALine - 광고 공급되는 조건
//-----------------------------------

// ----------------------------------------------
* SQL작성시 아래의 Table NickName 고정할 것
  WHERE 조건 작성시 1개가지고 작성할 수 있도록 하기 위함.
// ----------------------------------------------

# ON/OFF flags 존재하는 테이블 목록
	al_partner_t.is_mactive						: [관리자]가 Partner ID 활성/중지/삭제 ( Y/N/D )

	1번 al_app_t.is_mactive						: [관리자]가 해당 광고 활성/중지/삭제 ( Y/N/D/T )	: T는 테스트용 앱 (과정 로깅만하고 나머지는 모두 무시됨)
	2번 al_app_t.is_active						: [Merchant]가 해당 광고 활성/중지 ( Y/N )

	3번 al_merchant_t.is_mactive				: [관리자]가 Merchant Code 활성/테스트/중지/삭제 ( Y/T/N/D )
	4번 al_publisher_t.is_mactive				: [관리자]가 Publisher Code 활성/테스트/중지/삭제 ( Y/T/N/D )

	5번 al_publisher_app_t.is_mactive 			: [관리자]가 Publisher에게 app공급 활성/중지/삭제 ( Y/N/D )
	6번 al_publisher_app_t.publisher_disabled 	: [Publisher]가 광고 받기를 중지 ( Y )

	7번 al_app_t.publisher_level				: Publisher 공급 레벨 지정
			al_publisher_t.level				: 	app의 공급레벨보다 낮은 경우(숫자로는 높은경우) 공급 차단

	8번 al_app_t.is_public_mode					: [Merchant]의 public 모드 설정
			al_publisher_app_t.merchant_disabled: is_public_mode = Y인 경우 참고함 'N'이면 차단
			al_publisher_app_t.merchant_enabled	: is_public_mode = N인 경우 참고함 'Y'이면 차단

	9번 al_merchant_publisher_t.is_mactive		: [관리자]가 지정 Merchant의 광고에 대해 Publisher 제공을 허용/차단

	-- 광고 자체 오픈 시간 조정 (아래조건은 모두 AND)

	 	al_app_t.exec_stime ~ exec_etime		: 광고에 설정된 광고 시작 시간

	 	al_publisher_app_t.active_time			: 광고 활성 시간 - 관리자가 설정함 - 해당 Publisher & 광고를 허용/금지

	 	al_app_t.exec_edate						: end 시간보다 이전일 때에만 동작

		al_publisher_app_t.exec_hour_max_cnt or al_app_t.exec_hour_max_cnt <vs> al_app_exec_stat_t.exec_hour_cnt
		al_publisher_app_t.exec_day_max_cnt or al_app_t.exec_day_max_cnt <vs> al_app_exec_stat_t.exec_day_cnt
	 	al_publisher_app_t.exec_tot_max_cnt or al_app_t.exec_tot_max_cnt <vs> al_app_exec_stat_t.exec_tot_cnt

		# 필터링 정보는 Publisher에게 그대로 전달한다.
			al_app_t.app_gender, app_agefrom, app_ageto

		# al_user_app_t 에 참여 완료/불가 기록 참고해서

# Table NICK - a,b,c,d 까지는 Reserved
	al_app_t			v
	al_publisher_app_t	pa
	al_merchant_t		m
	al_publisher_t		p
	al_app_exec_stat_t	e
	al_user_app_t		u


# Publisher에게 광고 선택시

	al_merchant_t.is_mactive		: 관리자가 Merchant Code 활성/중지/삭제 ( Y/N/D )
	al_publisher_t.is_mactive		: 관리자가 Publisher Code 활성/중지/삭제 ( Y/N/D )

	al_app_t.is_mactive				: 관리자가 해당 광고 활성/중지/삭제 ( Y/N/D )
	al_app_t.is_active				: [Merchant]가 해당 광고 활성/중지

	al_publisher_app_t.is_mactive 	: 관리자가 Publisher에게 app공급 활성/중지/삭제 ( Y/N/D )

		-- 이테이블 정보만 무시 al_publisher_app_t.publisher_active : [Publisher]가 광고 받기를 활성/중지 ( Y/N )

	al_app_t.is_public_mode					: [Merchant]의 public 모드 설정
	al_publisher_app_t.merchant_disabled: is_public_mode = Y인 경우 참고함 'N'이면 차단
	al_publisher_app_t.merchant_enabled	: is_public_mode = N인 경우 참고함 'Y'이면 차단



*/?>