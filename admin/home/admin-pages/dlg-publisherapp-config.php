<style>
	#<?=$page_id?>	.ui-dialog-contain	{max-width:300px}
</style>
<div data-role="dialog" id="<?=$page_id?>" data-overlay-theme="a">
	<div data-role="header" data-theme="a">
    	<h1>Publisher App 설정</h1>
	</div>
    <div data-role="main" id="content">
        <hr>
        <div style="padding: 10px 0">
	        <div style="padding: 5px 20px 0px 20px; text-align:left">
	        	
	        	<t5>Publisher Code</t5>
	        	<div style='display: block'>
		        	<input type=text id="txt_publisher_code" init-value="" readonly style='background-color:lightyellow'/>
		        </div>
	        	<br>
	        	
	        	<t5>지정가격 (원)</t5>
	        	<div>
		        	<div style='width:70px; display: block; float:left; padding-top:3px'>
			        	<input type=text id="txt_offer_fee" init-value="" />
			        </div>
		        	<div style='width:70px; display: block; float:left'>
			        	<a href='#' onclick='$("#txt_offer_fee").val("")' data-role='button' data-mini='true' data-inline='true'>설정 취소</a>
			        </div>
			        <div style='clear:both'></div>
			    </div>
		        
	        	<br>
	        	<t5>제공가 율(%)</t5>
	        	<div>
		        	<div style='width:70px; display: block; float:left; padding-top:3px'>
			        	<input type=text id="txt_offer_fee_rate" init-value="" />
			        </div>
		        	<div style='width:70px; display: block; float:left'>
			        	<a href='#' onclick='$("#txt_offer_fee_rate").val("")' data-role='button' data-mini='true' data-inline='true'>설정 취소</a>
			        </div>
			        <div style='clear:both'></div>
			    </div>
	        	
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
						$("#txt_offer_fee").val(js_data['app_offer_fee']);
						$("#txt_offer_fee_rate").val(js_data['app_offer_fee_rate']);
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
					offerfee: _$("#txt_offer_fee").val(),
					offerfeerate: _$("#txt_offer_fee_rate").val()
				};
				
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
	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
