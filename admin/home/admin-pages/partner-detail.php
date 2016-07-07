<?
	$partner_id = $_REQUEST['partnerid'];

	$db_partner_id = mysql_real_escape_string($partner_id);
	$sql = "SELECT * FROM al_partner_t WHERE partner_id = '{$db_partner_id}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	
	$partner_name = $row['name'];

	// ---------------------------------------
	// publisher info
	// ---------------------------------------
	$sql = "SELECT a.id, a.type, a.pcode, p.name, p.is_mactive, p.offer_fee_rate, p.level
			FROM al_partner_mpcode_t a 
				INNER JOIN al_publisher_t p ON a.pcode = p.pcode AND a.type = 'P'
			WHERE a.partner_id = '{$db_partner_id}'
			ORDER BY id DESC";
	$result = mysql_query($sql, $conn);
	$ar_publisher_lists = array();
	while( $row = mysql_fetch_assoc($result) ) {
		$ar_publisher_lists[] = $row;
	}
	
	// ---------------------------------------
	// merchant info
	// ---------------------------------------
	$sql = "SELECT a.id, a.type, a.mcode, m.name, m.is_mactive, m.exchange_fee_rate
			FROM al_partner_mpcode_t a 
				INNER JOIN al_merchant_t m ON a.mcode = m.mcode AND a.type = 'M'
			WHERE a.partner_id = '{$db_partner_id}'
			ORDER BY id DESC";
	$result = mysql_query($sql, $conn);
	$ar_merchant_lists = array();
	while( $row = mysql_fetch_assoc($result) ) {
		$ar_merchant_lists[] = $row;
	}
	
?>
<div>
	<style>
		/* line hover setup using mactive flag */
		.list tr:hover td 				{background:#dff}
		.list tr.mactive-N td 			{background:#999; color:#fff}
		.list tr.mactive-N:hover td 	{background:#888}
		.list tr.mactive-T td 			{background:#f80; color:#000}
		.list tr.mactive-T:hover td 	{background:#f80}
				
		.list tr > * 	{height:25px; line-height:1em; padding: 4px 4px}
		
		.list .btn-td								{padding-left: 0px padding-right: 0px}
		.list .th_status, .list .btn-td .btn-wrapper	{width: 66px}
		.list .btn-td a								{padding:7px 4px; font-size: 10px; letter-spacing:0px; margin: 2px -2px 2px -1px; box-shadow:none;}
		
	</style>
	<t3 style='height:40px; padding-top:20px'><?=$partner_name?></t3>
	
	<table width=800px>
	<tr>
		<td width=50% style='border: 1px solid #ddd; padding: 10px 10px 10px 10px; vertical-align: top'>
			<!-- MERCHANT CODE LIST (대행사 List) -->
			<div style='float:left'>
				<t3>Merchant 목록</t3>
			</div>
			<div style='float:right'>
				<a href='#' onclick='goPage("dlg-merchant-new", null, {partnerid:"<?=$partner_id?>"})' data-role='button' data-theme='b' data-inline='true' data-mini='true' >+ New Merchant</a>
				<a href='#' onclick='mvPage("dlgpage-partner-add-merchant", null, {partnerid:"<?=$partner_id?>"})' data-role='button' data-theme='a' data-inline='true' data-mini='true' >+ Add Merchant</a>
			</div>
			<div style='clear:both'></div>
			<hr>
			<table class='single-line list' cellpadding=0 cellspacing=0 width=100%>
			<thead>
				<tr>
					<th>Idx</th>
					<th width=1px><div class='th_status'>상태</div></th>
					<th>코드</th>
					<th>이름</th>
					<th width=20%px>전환율(%)</th>
					<th width=1px></th>
					<th width=1px></th>
				</tr>
			</thead>
			<tbody>
			<?
				for ($i = 0; $i < count($ar_merchant_lists); $i ++) {
					
					$merchant = $ar_merchant_lists[$i];
					$url_mcode = urlencode($merchant['mcode']);
					$td_onclick = "onclick='window.location.href=\"?id=merchant-appkey-list&partnerid={$partner_id}&mcode={$url_mcode}\"'";
					
					// 현재의 merchant의 active상태 : Y / T / N 만 가능함.					
					$ar_btn_theme = array('a','a','a');
					if ($merchant['is_mactive'] == 'Y') $ar_btn_theme = array('b','a','a');
					else if ($merchant['is_mactive'] == 'T') $ar_btn_theme = array('a','b','a');
					else if ($merchant['is_mactive'] == 'N') $ar_btn_theme = array('a','a','b');
					?>
					<tr style='cursor:pointer' id='line-m-<?=$merchant['mcode']?>' class='mactive-<?=$merchant['is_mactive']?>'>
						<td <?=$td_onclick?>><?=$merchant['id']?></td>
						<td class='btn-td'>
							<div class='btn-wrapper'>
								<a class='btn-m-<?=$merchant['mcode']?> btn-Y' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_merchant_active("<?=$merchant['mcode']?>", "Y")' data-theme='<?=$ar_btn_theme[0]?>' data-role='button' data-mini='true' data-inline='true'>연<br>동</a>
								<a class='btn-m-<?=$merchant['mcode']?> btn-T' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_merchant_active("<?=$merchant['mcode']?>", "T")' data-theme='<?=$ar_btn_theme[1]?>'  data-role='button' data-mini='true' data-inline='true'>개<br>발</a>
								<a class='btn-m-<?=$merchant['mcode']?> btn-N' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_merchant_active("<?=$merchant['mcode']?>", "N")' data-theme='<?=$ar_btn_theme[2]?>'  data-role='button' data-mini='true' data-inline='true'>중<br>지</a>
							</div>
						</td>
						<td <?=$td_onclick?>><?=$merchant['mcode']?></td>
						<td <?=$td_onclick?>><?=$merchant['name']?></td>
						<td <?=$td_onclick?>><?=$merchant['exchange_fee_rate']?></td>
						<td><a href='#' onclick='goPage("dlg-merchant-modify", null, {merchant_code:"<?=$merchant['mcode']?>"})' data-theme='b' data-role='button' data-mini='true' data-inline='true'>정보<br>변경</a></td>
						<td><a href='#' onclick='<?=$js_page_id?>.action.on_btn_delete_partner_merchant_code("<?=$partner_id?>", "<?=$merchant['mcode']?>")' data-theme='a' data-role='button' data-mini='true' data-inline='true'>목록<br>삭제</a></td>
					</tr>
					<?
				}
				
			?>
			</tbody>
			</table>
			
			<div style='padding: 5px; color:#888; background: #eef; font-size:11px; border-radius:0.6em; border: 1px solid #88f'>
				* 연동 상태: 광고 노출 + 적립 가능<br>
				* 개발 상태: 광고 숨김 + 적립 가능<br>
				* 중지 상태: 광고 숨김 + 적립 불가<br>
				* 목록 삭제: Partner의 Merchant로서만 제외 (서비스에 영향 없음)
			</div>
			
			<!-- -------------------------------- -->
		</td>		
	</tr><tr>
		<td><br></td>
	</tr><tr>
		<td width=50% style='border: 1px solid #ddd; padding: 10px 10px 10px 10; vertical-align: top'>
			<!-- PUBLISHER CODE LIST (Brand List) -->
			<div style='float:left'>
				<t3>Publisher 목록</t3>
			</div>
			<div style='float:right'>
				<a href='#' onclick='goPage("dlg-publisher-new", null, {partnerid:"<?=$partner_id?>"})' data-role='button' data-theme='b' data-inline='true' data-mini='true' >+ New Publisher</a>
				<a href='#' onclick='mvPage("dlgpage-partner-add-publisher", null, {partnerid:"<?=$partner_id?>"})' data-role='button' data-theme='a' data-inline='true' data-mini='true' >+ Add Publisher</a>
			</div>
			<div style='clear:both'></div>
			
			<hr>
			<table class='single-line list'  cellpadding=0 cellspacing=0 width=100%>
			<thead>
				<tr>
					<th>Idx</th>
					<th width=1px><div class='th_status'>상태</div></th>
					<th>코드</th>
					<th>이름</th>
					<th>제공가(%)</th>
					<th>그룹</th>
					<th width=1px></th>
					<th width=1px></th>
					<th width=1px></th>
				</tr>
			</thead>
			<tbody>
			<?
				for ($i = 0; $i < count($ar_publisher_lists); $i ++) {
					
					$publisher = $ar_publisher_lists[$i];
					
					$url_pcode = urlencode($publisher['pcode']);
					$td_onclick = "onclick='window.location.href=\"?id=publisher-appkey-list&partnerid={$partner_id}&pcode={$url_pcode}\"'";

					// 현재의 Publisher의 active상태 : Y / T / N 만 가능함.					
					$ar_btn_theme = array('a','a','a');
					if ($publisher['is_mactive'] == 'Y') $ar_btn_theme = array('b','a','a');
					else if ($publisher['is_mactive'] == 'T') $ar_btn_theme = array('a','b','a');
					else if ($publisher['is_mactive'] == 'N') $ar_btn_theme = array('a','a','b');

					?>
					<tr style='cursor:pointer' id='line-p-<?=$publisher['pcode']?>'class='mactive-<?=$publisher['is_mactive']?>'>
						<td <?=$td_onclick?>><?=$publisher['id']?></td>
						<td class='btn-td'>
							<div class='btn-wrapper'>
								<a class='btn-p-<?=$publisher['pcode']?> btn-Y' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_publisher_active("<?=$publisher['pcode']?>", "Y")' data-theme='<?=$ar_btn_theme[0]?>' data-role='button' data-mini='true' data-inline='true'>연<br>동</a>
								<a class='btn-p-<?=$publisher['pcode']?> btn-T' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_publisher_active("<?=$publisher['pcode']?>", "T")' data-theme='<?=$ar_btn_theme[1]?>'  data-role='button' data-mini='true' data-inline='true'>개<br>발</a>
								<a class='btn-p-<?=$publisher['pcode']?> btn-N' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_publisher_active("<?=$publisher['pcode']?>", "N")' data-theme='<?=$ar_btn_theme[2]?>'  data-role='button' data-mini='true' data-inline='true'>중<br>지</a>
							</div>
						</td>
						<td <?=$td_onclick?>><?=$publisher['pcode']?></td>
						<td <?=$td_onclick?>><?=$publisher['name']?></td>
						<td <?=$td_onclick?>><?=$publisher['offer_fee_rate']?></td>
						<td <?=$td_onclick?>><?=$publisher['level']?></td>
						<td><a href='#' onclick='mvPage("publisher-setup-callback", null, {pcode:"<?=$publisher['pcode']?>"})' data-theme='e' data-role='button' data-mini='true' data-inline='true'>콜백<br>설정</a></td>
						<td><a href='#' onclick='goPage("dlg-publisher-modify", null, {publisher_code:"<?=$publisher['pcode']?>"})' data-theme='b' data-role='button' data-mini='true' data-inline='true'>정보<br>변경</a></td>
						<td><a href='#' onclick='<?=$js_page_id?>.action.on_btn_delete_partner_publisher_code("<?=$partner_id?>", "<?=$publisher['pcode']?>")' data-theme='a' data-role='button' data-mini='true' data-inline='true'>목록<br>삭제</a></td>
					</tr>
					<?
				}
				
			?>
			</tbody>
			</table>
			
			<div style='padding: 5px; color:#888; background: #eef; font-size:11px; border-radius:0.6em; border: 1px solid #88f'>
				* 연동 상태: 광고 노출 + 적립 가능<br>
				* 개발 상태: 테스트 광고만 노출<br>
				* 중지 상태: 광고 차단 + 적립 불가<br>
				* 목록 삭제: Partner의 Publisher로서만 제외 (서비스에 영향 없음)				
			</div>			
			<!-- -------------------------------- -->
		<tr>
	</tr>
	</table>		

</div>
			
<script type="text/javascript"> 

var <?=$js_page_id?> = function()
{
	// 외부에서 사용할 (Event Callback)함수 정의
	var _$ = function(selector) { if (!selector) return $("#<?=$page_id?>"); return $("#<?=$page_id?>").find(selector); };
	var page = 
	{			
		action: {
			initialize: function() 
			{
				util.initPage($('#page'));
				_$("div[data-role='popup']").on("popupbeforeposition", function(){ util.initPage($(this)); });
			},
			on_btn_set_publisher_active: function(pcode, status)
			{
				var ar_param = {
					'pcode' : pcode,
					'isactive' : status
				};
				util.post(get_ajax_url('admin-publisher-set-mactive'), ar_param, function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						toast('변경되었습니다.');
						
						$('.btn-p-'+pcode+'.btn-Y').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-p-'+pcode+'.btn-T').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-p-'+pcode+'.btn-N').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-p-'+pcode+'.btn-' + ar_param.isactive).addClass('ui-btn-b ui-btn-up-b').attr('data-theme', 'b');
						
						$('#line-p-'+pcode).removeClassMatch(/mactive\-/g).addClass('mactive-'+status);
					} else util.Alert(js_data['msg']);
				});
			},
			on_btn_set_merchant_active: function(mcode, status)
			{
				var ar_param = {
					'mcode' : mcode,
					'isactive' : status
				};
				util.post(get_ajax_url('admin-merchant-set-mactive'), ar_param, function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						toast('변경되었습니다.');
						$('.btn-m-'+mcode+'.btn-Y').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-m-'+mcode+'.btn-T').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-m-'+mcode+'.btn-N').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-m-'+mcode+'.btn-' + ar_param.isactive).addClass('ui-btn-b ui-btn-up-b').attr('data-theme', 'b');
						
						$('#line-m-'+mcode).removeClassMatch(/mactive\-/g).addClass('mactive-'+status);
					} else util.Alert(js_data['msg']);
				});
			},
			on_btn_delete_partner_publisher_code: function(partner_id, pcode) 
			{
				util.MessageBox('알림', '선택한 매체사를 목록에서 삭제시키겠습니까 ?', function(sel) {
					if (sel == 1) {

						var ar_param = {partnerid: partner_id, 'pcode': pcode};
						util.request(get_ajax_url('admin-partner-delete-publisher', ar_param), function(sz_data) {
							var js_data = util.to_json(sz_data);
							if (js_data['result']) {
								$("#line-p-" + pcode).hide();
								toast('제거되었습니다. (' + pcode + ')');
							} else util.Alert(js_data['msg']);
						});
						
					}
				});
			},
			on_btn_delete_partner_merchant_code: function(partner_id, mcode) 
			{
				util.MessageBox('알림', '선택한 광고사를 목록에서 삭제시키겠습니까 ?', function(sel) {
					if (sel == 1) {
				
						var ar_param = {partnerid: partner_id, 'mcode': mcode};
						util.request(get_ajax_url('admin-partner-delete-merchant', ar_param), function(sz_data) {
							var js_data = util.to_json(sz_data);
							if (js_data['result']) {
								$("#line-m-" + mcode).hide();
								toast('제거되었습니다. (' + mcode + ')');
							} else util.Alert(js_data['msg']);
						});
						
					}
				});
			},
		},
	};		
	
	function setEvents() {
		$(document).on("pageinit", function(){page.action.initialize();} );
	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
