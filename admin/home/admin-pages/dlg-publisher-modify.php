<div data-role="dialog" id="<?=$page_id?>" data-overlay-theme="a">
	<div data-role="header" data-theme="a">
    	<h1>Publisher 변경</h1>
	</div>
    <div data-role="main" id="content">
        <hr>
        <div style="padding: 10px 0">
	        <div style="padding: 5px 20px 0px 20px; text-align:left">
	        	
	        	<t5>Publisher Code</t5>
	        	<input type=text id="txt_publisher_code" init-value="" readonly style='background-color:lightyellow'/>
	        	<br>
	        	<br>
	        	<t5>Publisher 명</t5>
	        	<input type=text id="txt_publisher_name" init-value="" />
	        	
	        	<br>
	        	<t5>제공가 율(%)</t5>
	        	<div style='width:100px; display: block'>
		        	<input type=text id="txt_offer_fee_rate" init-value="" />
		        </div>
	        	
	        	<br>
	        	<t5>소속 그룹 레벨 (1 ~ 10)</t5>
	        	<div style='width:200px; display: block'>
					<select name="select-group-level" id="txt_group_level" init-value=''>
				        <option value="1">1 (자체서비스)</option>
				        <option value="2">2 (전략적 제휴사)</option>
				        <option value="3">3 (제휴사)</option>
				        <option value="4">4 (비추천 제휴사)</option>
				    </select>	        		
		        </div>
	        	
        	</div>
        	<div style='padding: 10px 20px'>
	        	<a href="#" onclick='<?=$js_page_id?>.action.on_btn_modifypublisher()' data-inline='true' data-mini="true" data-role="button" data-theme="b">변경하기</a>
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
	var page = { 
		page_common_init: function(){
			util.initPage(_$()); 
			param = (g_page_param['<?=$page_id?>'] ? g_page_param['<?=$page_id?>'] : {});
		},
		page_event: {
			on_create: function() {
				if (!param['publisher_code']) {
					util.Alert('알림', '정보가 없습니다.', function() {
						$.mobile.back();
					});	
				}	
				g_publisher_code = param['publisher_code'];
				
				$.mobile.loading('show');
				var ar_param = {pcode: g_publisher_code};
				util.request(get_ajax_url('admin-publisher-get-info', ar_param), function(sz_data) {
					$.mobile.loading('hide');
					var js_data = util.to_json(sz_data);
					if (js_data['result']) 
					{
						$("#txt_publisher_code").val(g_publisher_code);
						$("#txt_publisher_name").val(js_data['publisher_name']);
						$("#txt_offer_fee_rate").val(js_data['offer_fee_rate']);
						util.set_item_value($("#txt_group_level"), js_data['publisher_level']);
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
			on_btn_modifypublisher: function() {
				var ar_param = { 
					pcode: g_publisher_code,
					publishername: _$("#txt_publisher_name").val(),
					offerfeerate: _$("#txt_offer_fee_rate").val(),
					grouplevel: _$("#txt_group_level").val()
				};
				
				if (!ar_param.publishername) {
					util.Alert('Publisher 명을 입력하세요');
					return	
				}
				
				util.MessageBox('알림', '저장하시겠습니까 ?', function(sel) {
					if (sel == 1) {
				
						util.post(get_ajax_url('admin-publisher-modify'), ar_param, function(sz_data) {
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
	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
