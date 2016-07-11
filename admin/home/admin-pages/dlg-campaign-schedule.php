<div data-role="dialog" id="<?=$page_id?>" data-overlay-theme="a">
	<div data-role="header" data-theme="a">
    	<h1>광고 스케쥴 설정</h1>
	</div>
    <div data-role="main" id="content">
        <hr>
        <div style="padding: 10px 0">

			<b2 id='app_title' init-html=""></b2>

			<style>
				#schedule-table th {background-color: #ffe}
				#schedule-list td {border-top: 1px solid #ddd}
				#schedule-list .list-item:hover	{background-color:#fee}
			</style>			
			
			<div style='padding: 0 30px'>
				<table border=0 cellpadding=0 cellspacing=0>
				<tr><td>스케쥴일</td><td><div style='padding-left: 10px'> <input type="text" data-role="date" name="date" id="date" init-value="<?=date("Y-m-d")?>" /> </div></td></tr>
				<tr><td>수행 개수</td><td><div style='padding-left: 10px; width: 100px'> <input type="number" id="cnt" name="cnt" init-value="" /> </div></td></tr>
				<tr><td>이후 증감</td><td><div style='padding-left: 10px; width: 100px'> <input type="number" id="inc" name="inc" init-value="0" /> </div></td></tr>
				</table>
		        <a href='#' onclick="<?=$js_page_id?>.action.on_btn_add_schedule()" data-role='button' data-mini='true' data-theme='b'>일정 추가/갱신</a>
		    </div>
	        <div style='min-height:100px; border: 1px solid gray; margin: 10px'>
				<table id='schedule-table' width=100% border=0 cellpadding=0 cellspacing=0>
				<thead>
				<tr>
					<th align=center width=20% height=28px>스케쥴 날짜</th>
					<th align=center width=10%>일일 개수</th>
					<th align=center width=10%>증감 개수</th>
					<th align=center width=10%></th>
				</tr>
				</thead>
				<tbody id='schedule-list' init-html="">
				</tbody>
				</table>
		    </div>
		    
		    <div style='padding: 0 10px; margin: 0 auto'>
				<div id="chart_div" style="height:200px; border:1px solid black"></div>
			</div>

	        <div class='ui-grid-a' style='margin: 10px'>
	        	<div class='ui-block-a'><a href="#" onclick='<?=$js_page_id?>.action.on_btn_reset_schedule()' data-role="button" data-mini='true' data-theme='b'>스케쥴 리셋</a></div>
	        	<div class='ui-block-b'><a href="#" onclick='<?=$js_page_id?>.action.on_btn_close()' data-role="button" data-mini='true' >닫기</a></div>
	        </div>
	    </div>
    </div>	
</div>

<script type="text/javascript"> 

var <?=$js_page_id?> = function()
{
	// 외부에서 사용할 (Event Callback)함수 정의
	var _$ = function(selector) { if (!selector) return $("#<?=$page_id?>"); return $("#<?=$page_id?>").find(selector); };
	var param = {};
	
	var m_appkey = '';
	
	var m_bLoaded_chart = false;
	var m_chart_data = null;
	
	var page = { 
		page_common_init: function(){
			util.initPage(_$()); 
			param = (g_page_param['<?=$page_id?>'] ? g_page_param['<?=$page_id?>'] : {});
		},
		page_event: {
			on_create: function() {
				m_appkey = param['appkey'];
				
				page.action.refresh_list();
			}	
		},
		action: {
			refresh_list: function() {
				var ar_param = {appkey: m_appkey};
				util.request(get_ajax_url('admin-schedule-get-list', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						_$("#schedule-list").html(util.ifempty(js_data['list'], ""));
						page.action.update_chart(js_data['chart_data']);
					}
				});
			},
			on_click_row: function(date, cnt, inc) {
				_$("#date").val(date);
				_$("#cnt").val(cnt);
				_$("#inc").val(inc);
			},
			on_btn_add_schedule: function() {
				var ar_param = {appkey: m_appkey,
						date: _$("#date").val(),
						cnt: _$("#cnt").val(),
						inc: _$("#inc").val(),
					};
				if (!ar_param.date || !ar_param.cnt || !ar_param.inc) { util.Alert("정보를 모두 입력하세요."); return; }
				
				util.request(get_ajax_url('admin-schedule-set', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						_$("#schedule-list").html(util.ifempty(js_data['list'], ""));
						page.action.update_chart(js_data['chart_data']);
						toast('추가되었습니다.');
					} else {
						util.Alert(js_data['msg']);
					}
				});
			},
			on_btn_del_schedule: function(sch_date) {
				var ar_param = {appkey: m_appkey, date: sch_date};
				if (!ar_param.date) { util.Alert("정보를 모두 입력하세요."); return; }
				
				util.request(get_ajax_url('admin-schedule-del', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						_$("#schedule-list").html(util.ifempty(js_data['list'], ""));
						page.action.update_chart(js_data['chart_data']);
						toast('삭제되었습니다.');
					} else {
						util.Alert(js_data['msg']);
					}
				});
			},
			on_btn_reset_schedule: function() {
				var ar_param = {appkey: m_appkey};
				util.MessageBox('알림', '스케쥴을 리셋하겠습니까 ?', function(sel) {
					if (sel == 1) {
						util.request(get_ajax_url('admin-schedule-reset', ar_param), function(sz_data) {
							var js_data = util.to_json(sz_data);
							if (js_data['result']) {
								_$("#schedule-list").html(util.ifempty(js_data['list'], ""));
								page.action.update_chart(js_data['chart_data']);
								toast('리셋되었습니다.');
							} else {
								util.Alert(js_data['msg']);
							}
						});
					}
				});
			},
			on_btn_close: function() {
				util.MessageBox('알림', '스케쥴 설정을 종료하겠습니까 ?', function(sel) {
					if (sel == 1) {
						$.mobile.back();
						window.location.reload();
					}	
				});
			},
			on_loaded_charts: function() {
				m_bLoaded_chart = true;
				if (m_chart_data) {
					page.action.update_chart(m_chart_data);
					m_chart_data = null;
				}
			},
			update_chart:function(chart_array) {
				if (!m_bLoaded_chart) {
					m_chart_data = chart_array;
					return;
				}

				var js_array = JSON.parse(chart_array);
				var data = google.visualization.arrayToDataTable(js_array);
		        var options = {title: '', colors:['red'], legend: {position: 'none'}};
		        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
		        chart.draw(data, options);
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

<script type="text/javascript">
	google.charts.setOnLoadCallback(<?=$js_page_id?>.action.on_loaded_charts);
</script>
