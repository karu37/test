<?
	$mcode = $_REQUEST['mcode'];
	$db_mcode = mysql_real_escape_string($mcode);

	$sql = "SELECT * FROM al_merchant_t WHERE mcode = '{$db_mcode}'";
	$row_merchant = mysql_fetch_assoc(mysql_query($sql, $conn));

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
	$sql = "SELECT COUNT(*) as cnt FROM al_publisher_t p WHERE p.is_mactive <> 'D' {$where}";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$pages = new Paginator($row['cnt']);
	$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;
	// --------------------------------

	$sql = "SELECT p.*, IFNULL(mp2.is_mactive, 'Y') as 'is_mactive', t.name as 'partner_name', t.company as 'partner_company', SUM(s.merchant_cnt) as 'merchant_cnt'
			FROM al_publisher_t p
				LEFT OUTER JOIN al_merchant_publisher_t mp2 ON mp2.mcode = '{$db_mcode}' AND mp2.pcode = p.pcode
					LEFT OUTER JOIN al_partner_mpcode_t pmp ON pmp.pcode = p.pcode AND pmp.type = 'P'
					LEFT OUTER JOIN al_partner_t t ON t.partner_id = pmp.partner_id
					LEFT OUTER JOIN al_summary_sales_h_t s ON s.mcode = 'aline_m' AND s.pcode = p.pcode AND s.reg_day = CURRENT_DATE
			WHERE p.is_mactive IN ('Y','T') {$where}
			GROUP BY p.pcode
			ORDER BY p.reg_date DESC {$limit}";
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
	<t4 style='line-height: 40px'><b3 style='color:darkred'><?=$row_merchant['name'] . " (" . $row_merchant['mcode'] . ")"?></b3> 의 Publisher별 공급 설정</t4>
	<hr>

	<form onsubmit='return <?=$js_page_id?>.action.on_btn_search()'>
		<table border=0 cellpadding=0 cellspacing=0 width=100%>
		<tr><td id='btns-group' valign=top>
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
			<th>Publisher 이름/코드</th>
			<th width=70px>금일적립</th>
			<th>소속 파트너 이름</th>
			<th>소속 파트너 회사</th>
		</tr>
	</thead>
	<tbody>
	<?
		while ($merpub = mysql_fetch_assoc($result)) {

			$ar_btn_theme = array('a','a');
			if ($merpub['is_mactive'] == 'Y') $ar_btn_theme = array('b','a');
			else if ($merpub['is_mactive'] == 'N') $ar_btn_theme = array('a','b');

			?>
			<tr style=''>
				<td class='btn-td'>
					<div class='btn-wrapper'>
						<a class='btn-p-<?=$merpub['pcode']?> btn-Y' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_merchant_publisher_enable("<?=$mcode?>", "<?=$merpub['pcode']?>", "Y")' data-theme='<?=$ar_btn_theme[0]?>' data-role='button' data-mini='true' data-inline='true'>허<br>용</a>
						<a class='btn-p-<?=$merpub['pcode']?> btn-N' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_merchant_publisher_enable("<?=$mcode?>", "<?=$merpub['pcode']?>", "N")' data-theme='<?=$ar_btn_theme[1]?>' data-role='button' data-mini='true' data-inline='true'>차<br>단</a>
					</div>
				</td>
				<td><b><?=$merpub['name']?></b><br><span style='color:#888; line-height: 1.2em'><?=$merpub['pcode']?></span></td>
				<td><?=admin_number($merpub['merchant_cnt'])?></td>
				<td><?=$merpub['partner_name']?></td>
				<td><?=$merpub['partner_company']?></td>
			</tr>
			<?
		}

	?>
	</tbody>
	</table>

	<div style='padding: 5px; color:#888; background: #eef; font-size:11px; border-radius:0.6em; border: 1px solid #88f'>
		* 허용 상태: 지정 Publisher 에게 모든 광고 노출 (다른 조건 충족시)<br>
		* 차단 상태: 지정 Publisher 에게 모든 광고 차단
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
			on_btn_set_merchant_publisher_enable: function(mcode, pcode, active) {

				var ar_param = {'mcode': mcode, 'pcode': pcode, isactive: active};
				util.request(get_ajax_url('admin-merchant-publisher-set-mactive', ar_param), function(sz_data) {
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
