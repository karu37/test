<?
	$param_type = $_REQUEST['type'];
	$pcode = $_REQUEST['pcode'];
	if (!$param_type) $param_type = 'A';
	
	$where = "";
	if ($pcode) $where .= " AND a.pcode = '{$pcode}'";
	if ($param_type == 'D') $where .= " AND a.status = '{$param_type}'";
	$app_key = $_REQUEST['appkey'];
	$db_app_key = mysql_real_escape_string($app_key);
	
	// --------------------------------
	// Generate Download
	if ($_REQUEST['download'] == 'Y') {
		// header('Content-Type: text/html; charset=utf-8');
		header('Content-Type: application/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=data.txt');		
		
		$sql = "SELECT a.adid AS 'ADID',
					SUBSTRING_INDEX(a.account, ',', 1) AS '아이디', 
					a.status AS '상태', 
					a.model AS '적립기기',
					a.imei AS 'IMEI',
					a.ip AS 'IP',
					a.action_dtime AS '적립일시',
					b.name as '매체사'
				FROM al_user_app_t a LEFT OUTER JOIN al_publisher_t b ON a.pcode = b.pcode
				WHERE a.app_key = '{$db_app_key}' {$where}";
		$result = mysql_query($sql, $conn);		
		$fields = mysql_num_fields ( $result );
		for ( $i = 0; $i < $fields; $i++ ) {
		    $header .= mysql_field_name( $result , $i ) . "\t";
		}
		
		while( $row = mysql_fetch_row( $result ) ) {
		    $line = '';
		    foreach( $row as $value ) {                                            
		        if ( ( !isset( $value ) ) || ( $value == "" ) ) {
		            $value = "\t";
		        } else {
		            $value = str_replace( '"' , '""' , $value );
		            $value = '"' . $value . '"' . "\t";
		        }
		        $line .= $value;
		    }
		    $data .= trim( $line ) . "\n";
		}
		$data = str_replace( "\r" , "" , $data );
		echo $header . "\n" . $data;
		// echo mb_convert_encoding($header . "\n" . $data, "UTF-8", "EUC-KR");
		die();
	}
	// --------------------------------
	// Generate Download
	if ($_REQUEST['download'] == 'ADID') {
		// header('Content-Type: text/html; charset=utf-8');
		header('Content-Type: application/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=data-adid.txt');		
		
		$sql = "SELECT a.adid AS 'ADID',
					a.action_dtime AS '적립일시'
				FROM al_user_app_t a 
				WHERE a.app_key = '{$db_app_key}' AND a.status = 'D'";
		$result = mysql_query($sql, $conn);		
		$fields = mysql_num_fields ( $result );
		for ( $i = 0; $i < $fields; $i++ ) {
		    $header .= mysql_field_name( $result , $i ) . "\t";
		}
		
		while( $row = mysql_fetch_row( $result ) ) {
		    $line = '';
		    foreach( $row as $value ) {
		        if ( ( !isset( $value ) ) || ( $value == "" ) ) {
		            $value = "\t";
		        } else {
		            $value = str_replace( '"' , '""' , $value );
		            $value = '"' . $value . '"' . "\t";
		        }
		        $line .= $value;
		    }
		    $data .= trim( $line ) . "\n";
		}
		$data = str_replace( "\r" , "" , $data );
		echo $header . "\n" . $data;
		// echo mb_convert_encoding($header . "\n" . $data, "UTF-8", "EUC-KR");
		die();
	}
		
	// --------------------------------
	// Paginavigator initialize	
	$sql = "SELECT COUNT(*) as cnt FROM al_user_app_t a WHERE a.app_key = '{$db_app_key}' {$where}";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$pages = new Paginator($row['cnt']);
	$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;
	// --------------------------------

	$sql = "SELECT mcode, app_title FROM al_app_t WHERE app_key = '{$db_app_key}'";
	$row_app = @mysql_fetch_assoc(mysql_query($sql, $conn));

	$sql = "SELECT a.*, SUBSTRING_INDEX(a.account, ',', 1) AS 'account_id', b.name as 'publisher_name'
			FROM al_user_app_t a LEFT OUTER JOIN al_publisher_t b ON a.pcode = b.pcode
			WHERE a.app_key = '{$db_app_key}' {$where} 
			ORDER BY id DESC {$limit}";
	$result = mysql_query($sql, $conn);

?>
	<style>
		.main-list tr	{line-height:1.2em; height: 40px}
		.main-list span 	{ padding: 5px 10px }
	</style>
	<t4 style='line-height: 40px'>[<a href='?id=merchant-campaign-modify&mcode=<?=$row_app['mcode']?>&appkey=<?=$app_key?>' target=_blank><?=$row_app['app_title']?></a>] 참여 내역</t4>
	<hr>
		<div style='display:inline-block; width:40px; vertical-align:top; padding-top:12px; padding-left: 20px;'><b>상태</b></div>
		<div style="display:inline-block">
			<ul class='ul-horz' controgroup='field-contain' data-type="horizontal">
				<li><a href="#" onclick='window.location.href=util.url_add_param("<?=$_SERVER['REQUEST_URI']?>", "type", "A")' data-role="button" data-mini="true" data-inline="true" class="<? if($param_type == 'A') echo "ui-btn-active"; ?>" style='margin-left:0;margin-right:0;'>전체</a></li>				
				<li><a href="#" onclick='window.location.href=util.url_add_param("<?=$_SERVER['REQUEST_URI']?>", "type", "D")' data-role="button" data-mini="true" data-inline="true" class="<? if($param_type == 'D') echo "ui-btn-active"; ?>" style='margin-left:0;margin-right:0;'>적립완료</a></li>
				<li><div style='width:100px'></div></li>
				<li><a href="admin_index_ajax.php?id=merchant-campaign-user-list&appkey=<?=$app_key?>&type=<?=$param_type?>&download=Y" data-role="button" data-mini="true" data-inline="true" style='margin-left:0;margin-right:0;'>현재 목록 다운로드</a></li>
				<li><a href="admin_index_ajax.php?id=merchant-campaign-user-list&appkey=<?=$app_key?>&type=<?=$param_type?>&download=ADID" data-role="button" data-mini="true" data-inline="true" style='margin-left:0;margin-right:0;'>완료자 ADID 다운로드</a></li>
			</ul>
		</div>
	<hr>
	<div style="display:block; padding-top:20px; padding-left: 10px; font-size:22px; color: blue; font-weight: bold">총 : <?=number_format($pages->total_items)?> 건</div>
	<div class='ui-grid-a' style='padding:5px 10px; <?=$pages->num_pages <= 1 ? "display:none" : ""?>'>
		<div class='ui-block-a' style='width:70%; padding-top:5px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>
	<hr>
	<br>
	<table class='main-list single-line' cellpadding=0 cellspacing=0 width=100%>
	<thead>
		<tr>
	        <th align=center width=2%>Idx</th>
	        <th align=center width=3%>매체사</th>
	        <th align=center width=10%>ADID</th>
	        <th align=center width=5%>참여시간</th>
	        <th align=center width=5%>완료시간</th>
	        <th align=center width=2%>계정/IMEI</th>
	        <th align=center width=2%>제브모</th>
			<th width=5%>상태</th>
	        <th align=center width=2%>매출 원가</th>
	        <th align=center width=2%>광고 원가</th>
		</tr>	
	</thead>
	<style>
		.status-d	{background-color: lightyellow}
		.status-b	{background-color: #e8ffff}
	</style>
	<tbody>
		<?
		$arr_status = array('A' => '참여하기 실행', 'B' => '<span style="font-weight:bold; color:darkred">적립하기 실행</span>', 'D' => '적립완료', 'F' => '적립실패');
		
		while ($row = mysql_fetch_assoc($result)) 
		{
			echo "<tr class='status-{$row['status']}'>
		        <td align=center>{$row['id']}</td>
		        <td align=center>{$row['publisher_name']}</td>
		        <td align=center>{$row['adid']}</td>
		        <td align=center>".admin_to_datetime($row['action_atime'])."</td>
		        <td align=center>".admin_to_datetime($row['action_dtime'])."</td>
		        <td align=center>{$row['account_id']}<br>{$row['imei']}</td>
		        <td align=center>{$row['manufacturer']}<br>{$row['brand']}<br>{$row['model']}</td>
				<td>".($row['forced_done'] == 'Y' ? '<b>강제 적립</b>' : $arr_status[$row['status']])."</td>
		        <td align=center>{$row['merchant_fee']}</td>
		        <td align=center>{$row['publisher_fee']}</td>
			</tr>";
		}
		?>
	</tbody>
	</table>

	<hr>
	<div style='padding:10px' class='ui-grid-a'>
		<div class='ui-block-a' style='width:70%; padding-top:20px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>
		
<script type="text/javascript"> 

var <?=$js_page_id?> = function()
{
	// 외부에서 사용할 (Event Callback)함수 정의
	var page = 
	{			
		action: {
			initialize: function() {
				util.initPage($('#page'));
				$("div[data-role='popup']").on("popupbeforeposition", function(){ util.initPage($(this)); });
			},
		},
	};		
	function setEvents() {
		$(document).on("pageinit", function(){ 
			page.action.initialize();} );
	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
