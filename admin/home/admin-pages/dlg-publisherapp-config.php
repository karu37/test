<style>
	#<?=$page_id?>	.ui-dialog-contain	{max-width:450px}
</style>
<div data-role="dialog" id="<?=$page_id?>" data-overlay-theme="a">
	<div data-role="header" data-theme="a">
    	<h1>광고의 Publisher별 설정</h1>
	</div>
    <div data-role="main" id="content">
        <hr>
        <div style="padding: 10px 0">
	        <div style="padding: 5px 20px 0px 20px; text-align:left">
	        	<table width=100% border=0 cellpadding=0 cellspacing=0>
        		<tr>
        			<td>
			        	<t5>Publisher Code</t5>
			        </td>
			        <td>
			        	<div style='display: block'>
				        	<input type=text id="txt_publisher_code" init-value="" readonly style='background-color:lightyellow'/>
				        </div>
				    </td>
				</tr><tr>
					<td>	        	
	        			<t5 style='padding-top:10px'>지정가격 (원)</t5>
	        		</td>
	        		<td>
			        	<div>
				        	<div style='width:100px; display: block; float:left; padding-top:3px'>
					        	<input type=text id="txt_offer_fee" placeholder="설정없음" init-value="" />
					        </div>
				        	<div style='width:70px; display: block; float:left'>
					        	<a href='#' onclick='$("#txt_offer_fee").val("")' data-role='button' data-mini='true' data-inline='true'>설정 취소</a>
					        </div>
					        <div style='clear:both'></div>
					    </div>
				    </td>
				</tr><tr>
					<td>	        	
			        	<t5 style='padding-top:10px'>제공가 율(%)</t5>
	        		</td>
	        		<td>
			        	<div>
				        	<div style='width:100px; display: block; float:left; padding-top:3px'>
					        	<input type=text id="txt_offer_fee_rate" placeholder="설정없음" init-value="" />
					        </div>
				        	<div style='width:70px; display: block; float:left'>
					        	<a href='#' onclick='$("#txt_offer_fee_rate").val("")' data-role='button' data-mini='true' data-inline='true'>설정 취소</a>
					        </div>
					        <div style='clear:both'></div>
					    </div>
				    </td>
				</tr><tr>
					<td>	        	
			        	<t5 style='padding-top:10px'>오픈일</t5>
	        		</td>
	        		<td>
			        	<div>
				        	<div style='width:100px; display: block; float:left; padding-top:3px'>
					        	<input type="text" data-role="date" name="txt_active_time" id="txt_active_time" data-clear-btn=true placeholder='일정 없음' value="" />
					        </div>
					        <div style='width:90px; float:left'>
								<div data-role="fieldcontain" style='padding: 0px 0px; border: 0; margin: 0; float:left'>
								<select name="txt_active_time_hour" id="txt_active_time_hour" data-inline='true' data-mini='true' data-native-menu="true" data-theme='a' init-value=''>
									<?
										echo "<option></option>\n";
										for ($i=0; $i < 25; $i++) {
											echo "<option value='{$i}'>{$i}시</option>\n";
										}
									?>
								</select>
				        		</div>
					        </div>
				        	<div style='width:70px; display: block; float:left'>
					        	<a href='#' onclick='$("#txt_active_time").val(""); util.set_item_value($("#txt_active_time_hour"))' data-role='button' data-mini='true' data-inline='true'>설정 취소</a>
					        </div>
					        <div style='clear:both'></div>
					    </div>
				    </td>
				</tr><tr>
					<td>	        	
			        	<t5 style='padding-top:10px'>시간 실행 수량 제한</t5>
	        		</td>
	        		<td>
			        	<div>
				        	<div style='width:100px; display: block; float:left; padding-top:3px'>
					        	<input type=text id="txt_exec_hour_max_cnt" name="txt_exec_hour_max_cnt" placeholder="설정없음" init-value="" />
					        </div>
				        	<div style='width:70px; display: block; float:left'>
					        	<a href='#' onclick='$("#txt_exec_hour_max_cnt").val("")' data-role='button' data-mini='true' data-inline='true'>설정 취소</a>
					        </div>
					        <div style='clear:both'></div>
					    </div>
				    </td>
				</tr><tr>					<td>	        	
			        	<t5 style='padding-top:10px'>일일 실행 수량 제한</t5>
	        		</td>
	        		<td>
			        	<div>
				        	<div style='width:100px; display: block; float:left; padding-top:3px'>
					        	<input type=text id="txt_exec_day_max_cnt" name="txt_exec_day_max_cnt" placeholder="설정없음" init-value="" />
					        </div>
				        	<div style='width:70px; display: block; float:left'>
					        	<a href='#' onclick='$("#txt_exec_day_max_cnt").val("")' data-role='button' data-mini='true' data-inline='true'>설정 취소</a>
					        </div>
					        <div style='clear:both'></div>
					    </div>
				    </td>
				</tr><tr>
					<td>	        	
			        	<t5 style='padding-top:10px'>총 실행 수량 제한</t5>
	        		</td>
	        		<td>
			        	<div>
				        	<div style='width:100px; display: block; float:left; padding-top:3px'>
					        	<input type=text id="txt_exec_tot_max_cnt" name="txt_exec_tot_max_cnt" placeholder="설정없음" init-value="" />
					        </div>
				        	<div style='width:70px; display: block; float:left'>
					        	<a href='#' onclick='$("#txt_exec_tot_max_cnt").val("")' data-role='button' data-mini='true' data-inline='true'>설정 취소</a>
					        </div>
					        <div style='clear:both'></div>
					    </div>
					</td>
				</tr>
				</table>
	        	
        	</div>
        	<div style='padding: 20px 20px 10px 20px'>
	        	<a href="#" onclick='<?=$js_page_id?>.action.on_btn_save_publisherapp()' data-inline='true' data-mini="true" data-role="button" data-theme="b">저장하기</a>
	        	<a href="#" data-rel='back' data-inline='true' data-mini="true" data-role="button" data-theme="a">취소</a>
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
	var g_publisher_code = '';
	var g_app_key = '';
	
	var page = { 
		page_common_init: function(){
			util.initPage(_$()); 
			param = (g_page_param['<?=$page_id?>'] ? g_page_param['<?=$page_id?>'] : {});
		},
		page_event: {
			on_create: function() {
				if (!param['pcode'] || !param['appkey']) {
					util.Alert('알림', '정보가 없습니다.', function() {
						$.mobile.back();
					});	
				}	
				g_publisher_code = param['pcode'];
				g_app_key = param['appkey'];
				
				var ar_param = {pcode: g_publisher_code, appkey: g_app_key};
				util.request(get_ajax_url('admin-publisherapp-get-info', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) 
					{
						$("#txt_publisher_code").val(g_publisher_code);
						$("#txt_offer_fee").val(util.number_format(js_data['app_offer_fee'], '', '0'));
						$("#txt_offer_fee_rate").val(js_data['app_offer_fee_rate']);
						
						$("#txt_exec_hour_max_cnt").val(util.number_format(js_data['exec_hour_max_cnt'], '', '0'));
						$("#txt_exec_day_max_cnt").val(util.number_format(js_data['exec_day_max_cnt'], '', '0'));
						$("#txt_exec_tot_max_cnt").val(util.number_format(js_data['exec_tot_max_cnt'], '', '0'));
						
						if (js_data['active_time']) {
							$("#txt_active_time").val(js_data['active_time'].split(' ')[0]);
							var hour = util.intval(js_data['active_time'].split(' ')[1].split(':')[0]);
							util.set_item_value($("#txt_active_time_hour"), hour);
						} else {
							$("#txt_active_time").val("");
							util.set_item_value($("#txt_active_time_hour"), "");
						}
					} 
					else 
					{
						util.Alert('알림', js_data['msg'], function() {
							$.mobile.back();
						});
					}
				});
			}	
		},
		action: {
			on_btn_save_publisherapp: function() {
				var ar_param = { 
					pcode: g_publisher_code,
					appkey: g_app_key,
					offerfee: util.intval(_$("#txt_offer_fee").val(), ''),
					offerfeerate: _$("#txt_offer_fee_rate").val(),
					
					activetime: _$("#txt_active_time").val() ? _$("#txt_active_time").val() + " " + util.get_item_value(_$("#txt_active_time_hour")) : "",
					exechourmaxcnt: util.intval(_$("#txt_exec_hour_max_cnt").val(), ''),
					execdaymaxcnt: util.intval(_$("#txt_exec_day_max_cnt").val(), ''),
					exectotmaxcnt: util.intval(_$("#txt_exec_tot_max_cnt").val(), '')
				};
alert(util.var_dump(ar_param));
				util.MessageBox('알림', '저장하시겠습니까 ?', function(sel) {
					if (sel == 1) {
				
						util.post(get_ajax_url('admin-publisherapp-set-modify'), ar_param, function(sz_data) {
							var js_data = util.to_json(sz_data);
							if (js_data['result']) {
								window.location.reload();
							} else {
								util.Alert('오류', js_data['msg']);						
							}
						});
						
					}
				});
				
			},
		}
	};
	
	function setEvents() {
		_$().on("pagecreate", function(){page.page_common_init(); page.page_event.on_create();});
		
		util.set_event_for_input_number(_$("#txt_offer_fee"));
		util.set_event_for_input_number(_$("#txt_exec_day_max_cnt"));
		util.set_event_for_input_number(_$("#txt_exec_tot_max_cnt"));
		
	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
