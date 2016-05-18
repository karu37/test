<?/*

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
	al_partner_t.is_mactive			: 관리자가 Partner ID 활성/중지/삭제 ( Y/N/D )
	
	al_merchant_t.is_mactive		: 관리자가 Merchant Code 활성/중지/삭제 ( Y/N/D )
	al_publisher_t.is_mactive		: 관리자가 Publisher Code 활성/중지/삭제 ( Y/N/D )
	
	al_app_t.is_mactive				: 관리자가 해당 광고 활성/중지/삭제 ( Y/N/D )
	al_app_t.is_active				: [Merchant]가 해당 광고 활성/중지
	
	al_publisher_app_t.publisher_active : [Publisher]가 광고 받기를 활성/중지 ( Y/N )
	al_publisher_app_t.is_mactive 	: 관리자가 Publisher에게 app공급 활성/중지/삭제 ( Y/N/D )
	al_publisher_app_t.is_prohibit  : is_public_mode = Y 면 모두 허용 그러나 is_prohibit = Y 면 이것은 금지
	al_publisher_app_t.is_allow		: is_public_mode = N 면 모두 금지 그러나 is_allow = Y 면 이것은 허용
		by	al_app_t.is_public_mode			: 관리자가 설정 ( Y/N )
		
	
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
	
		-- 이테이블 정보만 무시 al_publisher_app_t.publisher_active : [Publisher]가 광고 받기를 활성/중지 ( Y/N )
	
	al_publisher_app_t.is_mactive 	: 관리자가 Publisher에게 app공급 활성/중지/삭제 ( Y/N/D )
	al_publisher_app_t.is_prohibit  : is_public_mode = Y 면 모두 허용 그러나 is_prohibit = Y 면 이것은 금지
	al_publisher_app_t.is_allow		: is_public_mode = N 면 모두 금지 그러나 is_allow = Y 면 이것은 허용
		by	al_app_t.is_public_mode			: 관리자가 설정 ( Y/N )

-- 광고 자체 오픈 시간 조정 (아래조건은 모두 AND)

 	al_publisher_app_t.open_time			: 관리자가 설정함 - 해당 Publisher & 광고를 허용/금지
 	
 	al_app_t.exec_sdate ~ exec_edate
 	al_app_t.exec_stime ~ exec_etime
	al_app_t.exec_hour_max_cnt <vs> al_app_exec_stat_t.live_exec_tmcnt
	
	al_publisher_app_t.exec_day_max_cnt or al_app_t.exec_day_max_cnt <vs> al_app_exec_stat_t.live_exec_cnt
 	al_publisher_app_t.exec_tot_max_cnt or al_app_t.exec_tot_max_cnt <vs> al_app_exec_stat_t.live_exec_totcnt

	# 필터링 정보는 Publisher에게 그대로 전달한다.
		al_app_t.app_gender, app_agefrom, app_ageto

	# al_user_app_t 에 참여 완료/불가 기록 참고해서 

*/?>