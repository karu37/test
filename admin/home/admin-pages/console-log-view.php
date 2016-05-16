<?
	$param_prefix = $_REQUEST['prefix'];
	$param_auto = $_REQUEST['auto'];
	$param_did = $_REQUEST['did'];
	$day = $_REQUEST['day'];
	
	$db_console_log_t = "console_log_t";
	if ($day && date("Ymd") != date("Ymd", strtotime($day))) $db_console_log_t = "autoring_backup.console_log_t_" . date("Ymd", strtotime($day));
	
	$where = "";
	if ($param_prefix) $where .= " AND a.prefix LIKE '{$param_prefix}%'";
	if ($param_did) $where .= " AND a.did = '$param_did'";

	// --------------------------------
	// Paginavigator initialize	
	$sql = "SELECT COUNT(*) as 'cnt' FROM {$db_console_log_t} a WHERE 1=1 $where ORDER BY id DESC";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$pages = new Paginator($row['cnt']);
	$limit = "LIMIT " . $pages->limit_start . "," . $pages->limit_end;
	// --------------------------------

	$sql = "SELECT a.* FROM {$db_console_log_t} a WHERE 1=1 $where ORDER BY a.id DESC {$limit}";
echo $sql;	
	$result = mysql_query($sql, $conn);
?>
	<t4 style='line-height: 40px'>Console Log</t4>
	<hr>
	<div style="display:block; padding-top:20px; padding-left: 10px; font-size:22px; color: blue; font-weight: bold">총 : <?=number_format($pages->total_items)?> 건</div>
	<div class='ui-grid-a' style='padding:5px 10px; <?=$pages->num_pages <= 1 ? "display:none" : ""?>'>
		<div class='ui-block-a' style='width:70%; padding-top:5px'><?=$pages->display_pages()?></div>
		<div class='ui-block-b' style='width:30%; text-align:right'><?=$pages->display_jump_menu() . $pages->display_items_per_page()?></div>
	</div>
	<hr>
	<div>
				<div style='display:inline-block; width:70px; vertical-align:top; padding-top:12px; padding-left: 20px;'>자동 : </div>
				<div style="display:inline-block">
					<ul class='ul-horz' controgroup='field-contain' data-type="horizontal">
						<li><a href='#' onclick="window.location.href=util.url_add_param(document.location.href, 'auto', 'A')" data-role="button" data-mini="true" data-inline="true" class="<? if($param_auto == 'A') echo "ui-btn-active"; ?>" style='margin-left:0;margin-right:0;'>AUTOREFRESH - ON</a></li>
						<li><a href='#' onclick="window.location.href=util.url_add_param(document.location.href, 'auto', '')"  data-role="button" data-mini="true" data-inline="true" class="<? if($param_auto != 'A') echo "ui-btn-active"; ?>" style='margin-left:0;margin-right:0;'>AUTOREFRESH - OFF</a></li>
						<li><a href='#' data-role="button" data-mini="true" data-inline="true" onclick='window.location.href=util.url_add_param("<?=$_SERVER['REQUEST_URI']?>", "did", "")'>전체보기</a></li>
					</ul>
				</div>
	</div>	
	<hr>
	<div>
			<div style='display:inline-block; width:70px; vertical-align:top; padding-top:12px; padding-left: 20px;'>날짜 선택 : </div>
			<div style="display:inline-block">
				<div class='ui-grid-a'>
					<div class='ui-block-a' style='width:120px; padding-top:5px'><input type="text" data-role="date" id='param-date' data-clear-btn='true' value="<?=$day?>"/></div>
					<div class='ui-block-b' style='width:300px; padding-left:5px'>
						<a href='#' onclick="$('#param-date').val('<?=date("Y-m-d", strtotime($day. " -1 day"))?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'>이전날</a>
						<a href='#' onclick="$('#param-date').val('<?=date("Y-m-d")?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'>오늘</a>
						<a href='#' onclick="$('#param-date').val('<?=date("Y-m-d", strtotime($day. " +1 day"))?>').trigger('change'); return false;" data-role='button' data-mini='true' data-inline='true'>다음날</a>
					</div>
				</div>
				<script>$('#param-date').change(function(){ window.location.href=g_admin_util.set_url_param("<?=$_SERVER['REQUEST_URI']?>", "day", $(this).val()); });</script>
			</div>
	</div>			
	<hr>
	<br>
	<style>
		#ctl-main-list	td {padding: 2px 2px; font-family: Sans-Serif; font-size:10px}
	</style>
	<table id='ctl-main-list' class='single-line' cellpadding=0 cellspacing=0 width=100%>
	<thead>
		<tr>
			<th width=1%>IDX</th>
			<th width=3%>Nick</th>
			<th width=50%>LOG</th>
		</tr>	
	</thead>
	<tbody>
		<?
		while ($row = mysql_fetch_assoc($result)) {
			$id = $row['id'];
		?>
			<tr>
				<td valign=top><?=$row['id']?></td>
				<td valign=top>
					<?=admin_user_nicklink($row['user_id'], $row['user_nick'])?>
					<br>
					<?=$row['ip']?><br>
					<nobr><?=substr($row['reg_date'],11) . ' (' . admin_to_elapsed_time($row['reg_date']) . ')'?></nobr><br>
					<nobr><a href='#' onclick='<?=$js_page_id?>.action.on_click_deviceid("<?=$row['did']?>")'><?=$row['did'] . ' ' . $row['model']?></a></nobr>
					<br><?=$row['ver']?>
				</td>
				<td style='text-align:left'><?=to_html($row['log'] ? $row['log'] : $row['prefix'])?></td>
			</tr>
		<?
		}
		?>
	</tbody>
	</table>
	
	<div style='padding: 0px'>
	</div>
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
				
				var param_auto = "<?=$param_auto ?>";
				if (param_auto == 'A') {
					setInterval(function() {
						document.location.reload();
					}, 3000);	
				}
				
			},
			on_btn_refund: function(listid, trid) {
				util.MessageBox('알림', '환불처리 하시겠습니까 ?', function(sel) {
					if (sel == 1) {
						var ar_param = {userid: '<?=$user_id?>', trid: trid};
						util.request(get_ajax_url('admin-set-store-refund', ar_param), function(sz_data) {
							var js_data = util.to_json(sz_data);
							if (js_data['result']) {
								$("#list-" + listid).removeClass("refund-N").addClass("refund-Y");
								$("#list-status-" + listid).html('환불');
								$("#list-refundbtn-" + listid).html('');
								toast('환불되었습니다.');						
							} else util.Alert(js_data['msg']);
						});
					}
				});
			},
			on_click_deviceid: function(did) {
				window.location.href = util.url_add_param("<?=$_SERVER['REQUEST_URI']?>", "did", did);
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
