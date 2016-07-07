<style>
	#admin-menu-list span 		{font-size:14px; color: #d00}
	#admin-menu-list a 			{font-weight: normal}
	#admin-menu-list .frequent a 	{font-weight: bold; color: #44f}
</style>
<ul id='admin-menu-list' style="min-height: 600px; padding: 10px 0px 10px 10px; line-height:18px">
	<? $idx = 1; ?>
	<li class='cate'><b><?=$idx++?>. 초기 <span>개발</span></b><li>
		<li style='padding-left:15px' class='frequent'>└ <a data-ajax="false" href='?id=partner-list'>파트너 목록</a></li>
			<li style='padding-left:25px' class='frequent'>└ <a data-ajax="false" href='?id=partner-add'>새 파트너 등록</a></li>
		<li style='padding-left:15px' class='frequent'>└ <a data-ajax="false" href='?id=all-merchant-list'>전체 Merchant 목록</a></li>
		<li style='padding-left:15px' class='frequent'>└ <a data-ajax="false" href='?id=all-publisher-list'>전체 Publisher 목록</a></li>
		<li style='padding-left:15px' class='frequent'>└ <a data-ajax="false" href='?id=all-appkey-list'>전체 광고 목록</a></li>
		<li style='padding-left:25px' class='frequent'>└ <a data-ajax="false" href='?id=all-appkey-list-test'>개발용 광고 목록</a></li>
		
	<li class='cate'><b><?=$idx++?>. <span>자체 광고</span>관리</b><li>
		<li style='padding-left:15px' class='frequent'>└ <a data-ajax="false" href='?id=merchant-appkey-list&partnerid=marshmallow&mcode=aline_m'>자체 광고 목록</a></li>
		<li style='padding-left:25px' class='frequent'>└ <a data-ajax="false" href='?id=merchant-campaign-add-app&mcode=aline_m'>새 광고 등록</a></li>
		
	<li class='cate'><b><?=$idx++?>. <span>매출 및 현황</span> 통계</b><li>
		<li style='padding-left:15px' class='frequent'>└ <a data-ajax="false" href='?id=stat-summary-m-sales-month'>M월간 매출</a>
			(<a data-ajax="false" href='?id=stat-summary-m-sales-year'>년</a>,<a data-ajax="false" href='?id=stat-summary-m-sales-day'>일</a>)
		</li>
		<li style='padding-left:15px' class='frequent'>└ <a data-ajax="false" href='?id=stat-summary-p-sales-month'>P월간 매출</a>
			(<a data-ajax="false" href='?id=stat-summary-p-sales-year'>년</a>,<a data-ajax="false" href='?id=stat-summary-p-sales-day'>일</a>)
		</li>
		<li style='padding-left:15px' class='frequent'>└ <a data-ajax="false" href='?id=stat-summary-sales-by-day'>일별 적립 목록</a></li>
	
</ul>
