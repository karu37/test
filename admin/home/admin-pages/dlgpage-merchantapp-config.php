<?
	$mcode = $_REQUEST['mcode'];
	$appkey = $_REQUEST['appkey'];
	$db_appkey = mysql_real_escape_string($appkey);

	$sql = "SELECT * FROM al_app_t app WHERE app.app_key = '{$db_appkey}' AND app.is_mactive <> 'T'";
	$row_app = @mysql_fetch_assoc(mysql_query($sql, $conn));

	// --------------------------------
	$searchfor = $_REQUEST['searchfor'];
	$search = trim($_REQUEST['search']);
	if (!$searchfor) $searchfor = 'name';
	$db_search = mysql_real_escape_string($search);

	// --------------------------------
	$where = "";
	if ($searchfor == "name" && $search) $where .= " AND p.name LIKE '%{$db_search}%'";
	else if ($searchfor == "code" && $search) $where .= " AND p.pcode LIKE '%{$db_search}%'";

	// --------------------------------
	// Paginavigator initialize	
	$sql = "SELECT COUNT(*) as cnt FROM al_publisher_t p WHERE p.is_mactive = 'Y' {$where}";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$pages = new Paginator($row['cnt']);
	$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;
	// --------------------------------

	$is_public_mode = $row_app['is_public_mode'];
	
	$sql = "SELECT p.*, pa.merchant_disabled, pa.merchant_enabled, IFNULL(IF('$is_public_mode' = 'Y', pa.merchant_disabled, pa.merchant_enabled), 'N') as 'checked'
			FROM al_publisher_t p
				LEFT OUTER JOIN al_publisher_app_t pa ON p.pcode = pa.pcode AND pa.app_key = '{$db_appkey}' 
			WHERE p.is_mactive = 'Y' {$where}
			ORDER BY checked DESC {$limit}";
			
	$result = mysql_query($sql, $conn);

?>
	<style>
		.list tr:hover td 	{background:#dff}
		.list tr > * 	{height:25px; line-height:1em; padding: 4px 4px}
				
		.list .btn-td								{padding-left: 0px padding-right: 0px}
		.list .btn-td .btn-wrapper					{width: 46px}
		.list .btn-td a								{padding:7px 4px; font-size: 10px; letter-spacing:0px; margin: 2px -2px 2px -1px; box-shadow:none;}
	</style>
<div style='width:900px'>	
	<t4 style='line-height: 40px'><b3 style='color:darkred'><?=$row_app['app_title']?></b3> 의 Publisher별 설정</t4>
	<hr>

	<form onsubmit='return <?=$js_page_id?>.action.on_btn_search()'>
		<table border=0 cellpadding=0 cellspacing=0 width=100%>
		<tr><td id='btns-group' valign=top>
        	<table class='line-height-15px' border=0 cellpadding=0 cellspacing=3px>
			<tr>
				<td valign=top align=right><t4 style='padding-top:6px; line-height:22px'>광고명 :<t4></td>
				<td valign=center><t3 style='padding-top: 4px'><?=$row_app['app_title']?></t3></td>
				<td></td>
			</tr><tr>		
				<td align=right><t4>관리자 차단 :<t4></td>
				<td>
					<div class='ui-block-a' style='width:150px'>
						<fieldset class='fieldset-nopadding' id="app-active" data-role="controlgroup" data-type="horizontal" data-mini=true init-value="<?=$row_app['is_mactive']?>" >
					        <input name="app-active" id="app-active-Y" value="Y" type="radio" />
					        <label for="app-active-Y">정상</label>
					        <input name="app-active" id="app-active-N" value="N" type="radio" />
					        <label for="app-active-N">중지</label>
					    </fieldset>
					</div>
					<div class='ui-block-b' style='padding-top:5px'>
						<a href='#' onclick='<?=$js_page_id?>.action.on_btn_save_app_active()' data-role='button' data-theme='a' data-transition="none" data-inline='true' data-mini='true'>상태 적용</a>
					</div>
				</td>
				<td>
					<div style='padding: 5px; color:#888; background: #eef; font-size:11px; border-radius:0.6em; border: 1px solid #88f'>
						* 정상 상태: 이 광고 노출<br>
						* 중지 상태: 이 광고 차단
					</div>						
				</td>
			</tr><tr>		
				<td align=right><t4>공개 모드 :<t4></td>
				<td>
					<div class='ui-block-a' style='width:150px'>
						<fieldset class='fieldset-nopadding' id="app-publicmode" data-role="controlgroup" data-type="horizontal" data-mini=true init-value="<?=$row_app['is_public_mode']?>" >
					        <input name="app-publicmode" id="app-publicmode-Y" value="Y" type="radio" />
					        <label for="app-publicmode-Y">공개</label>
					        <input name="app-publicmode" id="app-publicmode-N" value="N" type="radio" />
					        <label for="app-publicmode-N">비공개</label>
					    </fieldset>
					</div>
					<div class='ui-block-b' style='padding-top:5px'>
						<a href='#' onclick='<?=$js_page_id?>.action.on_btn_save_publicmode()' data-role='button' data-theme='a' data-transition="none" data-inline='true' data-mini='true'>상태 적용</a>
					</div>
				</td>
				<td>
					<div style='padding: 5px; color:#888; background: #eef; font-size:11px; border-radius:0.6em; border: 1px solid #88f'>
						* 공개 상태: 지정외에 모두 노출<br>
						* 비공개 상태: 지정외에 모두 차단
					</div>						
				</td>				
			</tr>
			</table>				
		</td><td valign=top align=right>
			
			<div style='width:300px; padding-top:10px; text-align: left'>
				<fieldset id="search-for" class='td-2-item' data-role="controlgroup" data-type="horizontal" style='margin-top: 3px;' data-mini=true init-value="<?=$searchfor?>" >
			        <input name="search-for" id="search-for-name" value="name" type="radio" />
			        <label for="search-for-name">Publisher 명</label>
			        <input name="search-for" id="search-for-code" value="code" type="radio" />
			        <label for="search-for-code">Publisher 코드</label>
			    </fieldset>	
			    <div class='ui-grid-a' style='padding:2px 0px; width: 300px; margin: 0 0 0 auto'>
			    	<div class='ui-block-a' style='width:200px'><input type=text name=search id=search data-clear-btn='true' value="<?=$_REQUEST['search']?>"  style='line-height: 25px;'/></div>
					<div class='ui-block-b' style='width:100px'><a href='#' onclick='<?=$js_page_id?>.action.on_btn_search()' data-role='button' data-mini='true'>검색</a></div>
				</div>
			</div>
			
		</td></tr></table>
	</form>
	<hr>
	
	<div style="display:block; padding-top:20px; padding-left: 10px; font-size:22px; color: blue; font-weight: bold">총 : <?=number_format($pages->total_items)?> 건</div>
	<div class='ui-grid-a' style='padding:5px 10px; <?=$pages->num_pages <= 1 ? "display:none" : ""?>'>
		<div class='ui-block-a' style='width:70%; padding-top:5px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>
	
	<table class='single-line list'  cellpadding=0 cellspacing=0 width=100%>
	<thead>
		<tr>
			<th width=1px>상태</th>
			<th>Publisher 명</th>
			<th>Publisher 코드</th>
		</tr>
	</thead>
	<tbody>
	<?
		if ($is_public_mode == 'Y') {
			$status_default = '기<br>본';
			$status_selected = '차<br>단';
		} else {
			$status_default = '기<br>본';
			$status_selected = '허<br>용';
		}
	
		while ($publisherapp = mysql_fetch_assoc($result)) {
		
			$ar_btn_theme = array('a','a');
			if ($publisherapp['checked'] == 'N') $ar_btn_theme = array('b','a');			// check == 'N' 이 디폴트 상태임
			else if ($publisherapp['checked'] == 'Y') $ar_btn_theme = array('a','b');

			?>
			<tr style=''>
				<td class='btn-td'>
					<div class='btn-wrapper'>
						<a class='btn-p-<?=$publisherapp['pcode']?> btn-Y' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_publisher_active("<?=$mcode?>", "<?=$publisherapp['pcode']?>", "<?=$appkey?>", "<?=$is_public_mode?>", "Y")' data-theme='<?=$ar_btn_theme[0]?>' data-role='button' data-mini='true' data-inline='true'><?=$status_default?></a>
						<a class='btn-p-<?=$publisherapp['pcode']?> btn-N' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_publisher_active("<?=$mcode?>", "<?=$publisherapp['pcode']?>", "<?=$appkey?>", "<?=$is_public_mode?>", "N")' data-theme='<?=$ar_btn_theme[1]?>'  data-role='button' data-mini='true' data-inline='true'><?=$status_selected?></a>
					</div>
				</td>
				<td><?=$publisherapp['name']?></td>
				<td><?=$publisherapp['pcode']?></td>
			</tr>
			<?
		}
		
	?>
	</tbody>
	</table>
	
	<div style='padding: 5px; color:#888; background: #eef; font-size:11px; border-radius:0.6em; border: 1px solid #88f'>
		<? if ($is_public_mode == 'Y') { ?>
		* 기본 상태: 지정 Publisher 노출<br>
		* 차단 상태: 지정 Publisher 차단
		<? } else { ?>
		* 기본 상태: 지정 Publisher 차단<br>
		* 허용 상태: 지정 Publisher 노출
		<? } ?>
	</div>		
	<!-- -------------------------------- -->	
	<div class='ui-grid-a' style='padding:5px 10px; <?=$pages->num_pages <= 1 ? "display:none" : ""?>'>
		<div class='ui-block-a' style='width:70%; padding-top:5px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>	

	<br>	
	<div style='padding: 10px; text-align: center'>
  		<a href='<?=urldecode($_REQUEST['ref'])?>' data-role='button' data-mini='true' data-inline='true'>완료</a>
	</div>	

	
</div>

<script type="text/javascript"> 
 
var <?=$js_page_id?> = function()
{
	// 외부에서 사용할 (Event Callback)함수 정의
	var ar_merchant_active = {'Y':'정상', 'T':'테스트', 'N':'중지'};
	var page = 
	{			
		action: {
			initialize: function() {
				util.initPage($('#page'));
				$("div[data-role='popup']").on("popupbeforeposition", function(){ util.initPage($(this)); });
			},
			
			on_btn_search: function() {
				var ar_param = {
						id: '<?=$page_id?>', 
						mcode: '<?=$mcode?>', 
						appkey: '<?=$appkey?>', 
						searchfor: util.get_item_value($("#search-for")), 
						search: $("#search").val()
				};
				window.location.href = '?' + util.json_to_urlparam(ar_param);
				return false;
			},
			on_btn_save_app_active: function() {

				var ar_param = {mcode: '<?=$mcode?>', appkey: '<?=$appkey?>', isactive: util.get_item_value($("#app-active"))};
				util.request(get_ajax_url('admin-merchantapp-set-mactive', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						toast('저장되었습니다. (' + ar_merchant_active[ar_param.isactive] + ')');
					} else util.Alert(js_data['msg']);
				});
				
			},
			on_btn_save_publicmode: function() {

				var ar_param = {mcode: '<?=$mcode?>', appkey: '<?=$appkey?>', publicmode: util.get_item_value($("#app-publicmode"))};
				
				util.MessageBox('알림', '공개모드를 변경하면 기존에 설정한 Publisher별 활성/비활성이 모두 초기화 됩니다.\n\n설정을 변경하시겠습니까 ?', function(sel) {
					
					if (sel == 1) {
						util.request(get_ajax_url('admin-merchantapp-set-publicmode', ar_param), function(sz_data) {
							var js_data = util.to_json(sz_data);
							if (js_data['result']) {
								toast('변경되었습니다.');
								setTimeout(function(){window.location.reload();}, 300);
							} else util.Alert(js_data['msg']);
						});
					}
					
				});
				
			},
			on_btn_set_publisher_active: function(mcode, pcode, appkey, publicmode, active) {
				
				var ar_param = {'mcode': mcode, 'pcode': pcode, 'appkey': appkey, 'publicmode': publicmode, isactive: active};
				util.request(get_ajax_url('admin-merchantapp-publisher-set-mactive', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						$('.btn-p-'+pcode+'.btn-Y').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-p-'+pcode+'.btn-N').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-p-'+pcode+'.btn-' + ar_param.isactive).addClass('ui-btn-b ui-btn-up-b').attr('data-theme', 'b');
						toast('저장되었습니다. (' + ar_merchant_active[ar_param.isactive] + ')');
					} else util.Alert(js_data['msg']);
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
