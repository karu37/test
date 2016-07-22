<style>
	.menu_desc	{color: #888}	
</style>
<ul style="min-height: 600px; padding: 10px; line-height:22px">
	
	<? $idx = 1; ?>
	<li><b><?=$idx++?>. Merchant 메뉴</b><li>
		<li style='padding-left:15px'>└ <a data-ajax="false" href='?id=partner-merchant-list'>Merchant 목록</a></li>
		<li style='padding-left:25px' class='menu_desc'>&middot; Merchant별 실적 조회</li>
	<li><b><?=$idx++?>. Publisher 메뉴</b><li>
		<li style='padding-left:15px'>└ <a data-ajax="false" href='?id=partner-publisher-list'>Publisher 목록</a></li>
		<li style='padding-left:25px' class='menu_desc'>&middot; Publisher별 실적 조회</li>
		<li style='padding-left:25px' class='menu_desc'>&middot; 연동 콜백 설정</li>
	<li><b><?=$idx++?>. 광고주 계정 관리</b><li>
		<li style='padding-left:15px'>└ <a data-ajax="false" href='?id=guest-list'>광고주 계정 목록</a></li>
		<li style='padding-left:25px' class='menu_desc'>&middot; 광고주별 실적 조회</li>
		<li style='padding-left:15px'>└ <a data-ajax="false" href='?id=guest-regist'>새 광고주 계정</a></li>
	<li><b><?=$idx++?>. 사용자</b><li>
		<li style='padding-left:15px'>└ <a data-ajax="false" href='?id='>광고 참여 검색</a></li>
	
</ul>
