<style>
	.menu_desc	{color: #888}	
</style>
<ul style="min-height: 600px; padding: 10px; line-height:22px">
	
	<? $idx = 1; ?>
	<li><b><?=$idx++?>. Merchant 메뉴</b><li>
		<li style='padding-left:15px'>└ <a data-ajax="false" href='?id=partner-merchant-sales'>Merchant 실적</a></li>
	<li><b><?=$idx++?>. Publisher 메뉴</b><li>
		<li style='padding-left:15px'>└ <a data-ajax="false" href='?id=partner-publisher-sales'>Publisher 실적</a></li>
		<li style='padding-left:15px'>└ <a data-ajax="false" href='?id=partner-publisher-callback-list'>Publisher 콜백 설정</a></li>
	<li><b><?=$idx++?>. 광고주 계정 관리</b><li>
		<li style='padding-left:15px'>└ <a data-ajax="false" href='?id=partner-guest-sales'>광고주 실적</a></li>
		<li style='padding-left:15px'>└ <a data-ajax="false" href='?id=guest-list'>광고주 계정 목록</a></li>
		<li style='padding-left:15px'>└ <a data-ajax="false" href='?id=guest-regist'>새 광고주 계정</a></li>
	<li><b><?=$idx++?>. 사용자</b><li>
		<li style='padding-left:15px'>└ <a data-ajax="false" href='?id='>광고 참여 검색</a></li>
	
</ul>
