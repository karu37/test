<?
?>
<style>
	#admin-menu-list span 		{font-size:14px; color: #d00}
	#admin-menu-list a 			{font-weight: normal}
	#admin-menu-list .frequent a 	{font-weight: bold; color: #44f}
</style>
<ul id='admin-menu-list' style="min-height: 600px; padding: 10px 0px 10px 10px; line-height:18px">
	<? $idx = 1; ?>
	<li class='cate'><b><?=$idx++?>. 초기 <span>개발</span></b><li>
		<li style='padding-left:15px' class='frequent'>└ <a data-ajax="false" href='?id=partner-list'>업체 목록</a></li>
		<li style='padding-left:15px' class='frequent'>└ <a data-ajax="false" href='?id=partner-add'>새 업체 등록</a></li>
	
</ul>
