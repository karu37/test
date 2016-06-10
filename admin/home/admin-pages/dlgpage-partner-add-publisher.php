<?
	$partner_id = $_REQUEST['partnerid'];

	$db_partner_id = mysql_real_escape_string($partner_id);
	$sql = "SELECT * FROM al_partner_t WHERE partner_id = '{$db_partner_id}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	$partner_name = $row['name'];

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
	$sql = "SELECT COUNT(*) as cnt FROM al_publisher_t p LEFT OUTER JOIN al_partner_mpcode_t mp ON p.pcode = mp.pcode AND mp.partner_id = '{$db_partner_id}' AND TYPE = 'P' WHERE mp.pcode IS NULL {$where}";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$pages = new Paginator($row['cnt']);
	$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;
	// --------------------------------
	$sql = "SELECT p.* FROM al_publisher_t p 
				LEFT OUTER JOIN al_partner_mpcode_t mp ON p.pcode = mp.pcode AND mp.partner_id = '{$db_partner_id}' AND TYPE = 'P'
			WHERE mp.pcode IS NULL {$where}
			ORDER BY reg_date DESC {$limit}";
	$result = mysql_query($sql, $conn);

?>
<style>
	.list tr:hover td 	{background:#dff}
	.list tr > * 		{height:25px; line-height:1em; padding: 4px 4px}
</style>		

<div style='width:700px'>	
	<t4 style='line-height: 40px'><b3 style='color:darkred'><?=$partner_name?></b3>에 Publisher 추가하기</t4>
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
			<th>Publisher 명</th>
			<th>Publisher 코드</th>
			<th width=1px>상태</th>
		</tr>
	</thead>
	<tbody>
	<?
		while ($publisherapp = mysql_fetch_assoc($result)) {
		
			$ar_btn_theme = array('a','a');
			if ($publisherapp['checked'] == 'N') $ar_btn_theme = array('b','a');			// check == 'N' 이 디폴트 상태임
			else if ($publisherapp['checked'] == 'Y') $ar_btn_theme = array('a','b');

			?>
			<tr style='' id='line-<?=$publisherapp['pcode']?>'>
				<td><?=$publisherapp['name']?></td>
				<td><?=$publisherapp['pcode']?></td>
				<td class='btn-td'>
					<a href='#' onclick='<?=$js_page_id?>.action.on_btn_set_publisher_add("<?=$partner_id?>", "<?=$publisherapp['pcode']?>")' data-theme='<?=$ar_btn_theme[1]?>'  data-role='button' data-mini='true' data-inline='true'>추가</a>
				</td>
			</tr>
			<?
		}
		
	?>
	</tbody>
	</table>
	<!-- -------------------------------- -->	
	<div class='ui-grid-a' style='padding:5px 10px; <?=$pages->num_pages <= 1 ? "display:none" : ""?>'>
		<div class='ui-block-a' style='width:70%; padding-top:5px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>	
	
	<br>
	<div style='padding: 10px; text-align:center'>
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
						partnerid: '<?=$partner_id?>', 
						ref: '<?=urldecode($_REQUEST["ref"])?>',
						searchfor: util.get_item_value($("#search-for")), 
						search: $("#search").val()
				};
				window.location.href = '?' + util.json_to_urlparam(ar_param);
				return false;
			},
			on_btn_set_publisher_add: function(partner_id, pcode) {

				var ar_param = {partnerid: partner_id, 'pcode': pcode};
				util.request(get_ajax_url('admin-partner-add-publisher', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						$("#line-" + pcode).hide();
						toast('추가되었습니다. (' + pcode + ')');
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