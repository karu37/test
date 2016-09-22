<?
	// http://admin.aline-soft.kr/admin_index.php?id=publisher-ads-list&pcode=autoring_p
	$pcode = $_REQUEST['pcode'];
	
	$db_pcode = mysql_real_escape_string($pcode);
	$sql = "SELECT name FROM al_publisher_t WHERE pcode = '{$db_pcode}'";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$merchant_name = $row['name'];
	
	$url = "http://api.aline-soft.kr/ajax-request.php?id=__publisher-ads-list&pcode={$pcode}";
?>
<t4><?=$merchant_name?>의 실시간 광고 제공 목록</t4>
<hr>
<iframe id='ads-list' src='<?=$url?>' style='width:100%; height:1000px; margin-top: 10px;' /></iframe>
<script>
	window.addEventListener("message", function(e) {
		
		if (e && e.data && e.data.height) {
			$("#ads-list").height(e.data.height);
		}
	}, false);
</script>
