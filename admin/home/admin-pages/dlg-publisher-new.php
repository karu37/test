<?
	$sql = "SELECT val FROM al_service_config_t WHERE nm = 'publisher_offer_rate'";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	$default_offer_rate = $row['val'];
?>
<div data-role="dialog" id="<?=$page_id?>" data-overlay-theme="a">
	<div data-role="header" data-theme="a">
    	<h1>새 Publisher 등록</h1>
	</div>
    <div data-role="main" id="content">
        <hr>
        <div style="padding: 10px 0">
	        <div style="padding: 5px 20px 0px 20px; text-align:left">
	        	
	        	<t5>Publisher Code<n7> &nbsp; (<span style='color:darkred'>생성 후 변경 불가</span> - API 연동 코드로 사용됨)</n7></t5>
	        	<input type=text id="txt_publisher_code" init-value="" />
				(숫자/영문소문자와 "_" 만 사용, 띄워쓰기 불가, 4 ~ 16자 이내)
	        	<br>
	        	<br>
	        	<t5>Publisher 명</t5>
	        	<input type=text id="txt_publisher_name" init-value="" />
	        	
	        	<br>
	        	<t5>제공가 율(%)</t5>
	        	<div style='width:100px; display: inline-block'>
		        	<input type=text id="txt_offer_fee_rate" init-value="<?=$default_offer_rate?>" />
		        </div>
	        	
	        	<br>
	        	<br>
	        	<t5>소속 그룹 레벨 (1 ~ 10)</t5>
	        	<div style='width:200px; display: inline-block'>
					<select name="select-group-level" id="txt_group_level" init-value='3'>
				        <option value="1">1 (자체서비스)</option>
				        <option value="2">2 (전략적 제휴사)</option>
				        <option value="3">3 (제휴사)</option>
				        <option value="4">4 (비추천 제휴사)</option>
				    </select>	        		
		        </div>
	        	
        	</div>
        	<div style='padding: 10px 20px'>
	        	<a href="#" onclick='<?=$js_page_id?>.action.on_btn_addpublisher()' data-inline='true' data-mini="true" data-role="button" data-theme="b">생성하기</a>
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
	var g_partner_id = '';
	var page = { 
		page_common_init: function(){
			util.initPage(_$()); 
			param = (g_page_param['<?=$page_id?>'] ? g_page_param['<?=$page_id?>'] : {});
		},
		page_event: {
			on_create: function() {
				if (!param['partnerid']) {
					util.Alert('알림', '파트너 정보가 없습니다.', function() {
						$.mobile.back();
					});	
				}	
				g_partner_id = param['partnerid'];
				
				_$("#txt_publisher_code").val(page.action.gen_mpcode());
				
			}	
		},
		action: {
			gen_mpcode: function() {
				// generate mpcode for 16 text length
				var mpcode = "";
				for (var i = 0; i < 10 && mpcode.length < 16; i++)
					mpcode = (parseInt(Math.random()*900000000000000000).toString(15) + parseInt(Math.random()*900000000000000000).toString(15)).substring(0, 16);
				return mpcode;
			},
			on_btn_addpublisher: function() {
				var ar_param = { 
					partnerid: g_partner_id,
					pcode: _$("#txt_publisher_code").val(),
					publishername: _$("#txt_publisher_name").val(),
					offerfeerate: _$("#txt_offer_fee_rate").val(),
					grouplevel: _$("#txt_group_level").val()
				};
				
				if (!ar_param.pcode || !ar_param.publishername) {
					util.Alert('Publisher 명을 입력하세요');
					return	
				}
				
				if (!ar_param.pcode.match(/^[0-9a-z_]{4,16}/g)) {
					alert('Publisher Code는 영문 소문자와 "_" 으로\n띄어쓰기 없이 4 ~ 16자 이내여야 합니다.');
					return;
				}
				
				util.MessageBox('알림', '새로 등록하시겠습니까 ?', function(sel) {
					if (sel == 1) {
				
						util.post(get_ajax_url('admin-publisher-new'), ar_param, function(sz_data) {
							var js_data = util.to_json(sz_data);
							if (js_data['result']) {
								window.location.reload();
							} else {
								util.Alert('오류', js_data['msg']);						
								_$("#txt_publisher_code").val(page.action.gen_mpcode());
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
