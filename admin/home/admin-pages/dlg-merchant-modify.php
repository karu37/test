<div data-role="dialog" id="<?=$page_id?>" data-overlay-theme="a">
	<div data-role="header" data-theme="a">
    	<h1>Publisher 변경</h1>
	</div>
    <div data-role="main" id="content">
        <hr>
        <div style="padding: 10px 0">
	        <div style="padding: 5px 20px 0px 20px; text-align:left">
	        	
	        	<t5>Publisher Code</t5>
	        	<input type=text id="txt_merchant_code" init-value="" readonly style='background-color:lightyellow'/>
	        	<br>
	        	<br>
	        	<t5>Publisher 명</t5>
	        	<input type=text id="txt_merchant_name" init-value="" />
	        	
	        	<br>
	        	<t5>제공가 전환 비율(%)</t5>
	        	<div style='width:100px; display: block'>
		        	<input type=text id="txt_exchange_fee_rate" init-value="" />
		        </div>	        
	        	부가세 포함금액으로 전달되는 경우 : 91 설정
		        	
	        	
        	</div>
        	<div style='padding: 10px 20px'>
	        	<a href="#" onclick='<?=$js_page_id?>.action.on_btn_modifymerchant()' data-inline='true' data-mini="true" data-role="button" data-theme="b">변경하기</a>
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
	var g_merchant_code = '';
	var page = { 
		page_common_init: function(){
			util.initPage(_$()); 
			param = (g_page_param['<?=$page_id?>'] ? g_page_param['<?=$page_id?>'] : {});
		},
		page_event: {
			on_create: function() {
				if (!param['merchant_code']) {
					util.Alert('알림', '정보가 없습니다.', function() {
						$.mobile.back();
					});	
				}	
				g_merchant_code = param['merchant_code'];
				
				var ar_param = {mcode: g_merchant_code};
				util.request(get_ajax_url('admin-merchant-get-info', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) 
					{
						$("#txt_merchant_code").val(g_merchant_code);
						$("#txt_merchant_name").val(js_data['merchant_name']);
						$("#txt_exchange_fee_rate").val(js_data['exchange_fee_rate']);
						util.set_item_value($("#txt_group_level"), js_data['merchant_level']);
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
			on_btn_modifymerchant: function() {
				var ar_param = { 
					mcode: g_merchant_code,
					merchantname: _$("#txt_merchant_name").val(),
					exchangefeerate: _$("#txt_exchange_fee_rate").val()
				};
				
				if (!ar_param.merchantname) {
					util.Alert('Publisher 명을 입력하세요');
					return	
				}
				if (!ar_param.exchangefeerate) {
					util.Alert('제공가 전환 비율을 입력하세요');
					return;
				}
				
				util.MessageBox('알림', '저장하시겠습니까 ?', function(sel) {
					if (sel == 1) {
				
						util.post(get_ajax_url('admin-merchant-modify'), ar_param, function(sz_data) {
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
