<?
	$searchfor = $_REQUEST['searchfor'];
	$search = trim($_REQUEST['search']);
	if (!$searchfor) $searchfor = 'title';
	$db_search = mysql_real_escape_string($search);

	$where = "";
	if ($searchfor == "title" && $search) $where .= " AND app.app_title LIKE '%{$db_search}%'";
	if ($searchfor == "packageid" && $search) $where .= " AND app.app_packageid LIKE '{$db_search}%'";

	// ---------------------------------------
	// publisher info
	// ---------------------------------------
	$sql = "SELECT app.* 
			FROM al_app_t app 
				INNER JOIN al_partner_mpcode_t pm ON app.mcode = pm.mcode AND TYPE = 'M'
			WHERE pm.partner_id = '{$db_partner_id}' {$where} ORDER BY id DESC LIMIT 100";
	$result = mysql_query($sql, $conn);
		
?>
	<style>
		/* line hover setup using mactive flag */
		.list tr:hover td 				{background:#eff}
		.list tr > * 	{height:25px; line-height:1em; padding: 4px 4px}
	</style>
	<div>
	<table class='single-line list'  cellpadding=0 cellspacing=0 width=100%>
	<thead>
		<tr>
			<th width=40px></th>
			<th>제목</th>
			<th width=40px></th>
		</tr>
	</thead>
	<tbody>
	<?		
		while ($row = mysql_fetch_assoc($result)) {
			?>			
			<tr style='cursor:pointer' id='line-<?=$row['app_key']?>' class='app-active-<?=$app_active?>'>
				<td><img src='<?=$row['app_iconurl']?>' width=40px style='width:40px;height:40px;overflow:hidden;border-radius:0.5em;border:1px solid #888' /></td>
				<td><div style='text-align:left; padding: 0 5px; color:inherit'><?=$row['app_title']?></div></td>
				<td><a href='#' onclick='<?=$js_page_id?>.action.on_btn_select("<?=$row['app_title']?>", "<?=$row['app_key']?>")' data-role='button' data-mini='true'>선택</a></td>
			</tr>
			<?
		}
	?>		
	</div>
	
<script type="text/javascript"> 

var <?=$js_page_id?> = function()
{
	// 외부에서 사용할 (Event Callback)함수 정의
	var _$ = function(selector) { if (!selector) return $("#<?=$page_id?>"); return $("#<?=$page_id?>").find(selector); };
	var param = {};
	var page = { 
		page_common_init: function(){
			util.initPage(_$()); 
			param = (g_page_param['<?=$page_id?>'] ? g_page_param['<?=$page_id?>'] : {});
		},
		page_event: {
			on_create: function() {}	
		},
		action: {
			on_btn_select: function(title, appkey) {
				top.window.postMessage({cmd:"onselect", 'app_title': title, 'app_key': appkey}, 'http://'+document.domain);
			},
		}
	};
	
	function setEvents() {
		_$().on("pagecreate", function(){page.page_common_init(); page.page_event.on_create();});
	}		

	setEvents(); // Event Attaching		
	return page;
}();
</script>	
