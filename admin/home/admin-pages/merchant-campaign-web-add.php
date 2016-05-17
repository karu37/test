<?
	$partner_id = $_REQUEST['partnerid'];
	$mcode = $_REQUEST['mcode'];
?>
	<style>
		#add_app	.ui-block-a	{height: 45px; line-height:45px; padding-left: 10px; width:100px; border-bottom: 1px solid #ddd; font-weight: bold}
		#add_app	.ui-block-b	{height: 45px; width:500px; border-bottom: 1px solid #ddd}
		
		#add_app	#app-exec-desc-wrapper	.cleditorMain	{height: 85px !important}
		#add_app	#app-exec-desc-wrapper	.cleditorMain iframe	{height: 32px !important}
		
		#add_app	#app-content-wrapper	.cleditorMain	{height: 285px !important}
		#add_app	#app-content-wrapper	.cleditorMain iframe	{height: 232px !important}
	</style>
	<t3 style='height:40px; padding-top:20px'>WEB 방식 광고 등록</t3>
	<hr>
	<div id='add_app' class='ui-grid-a'>
		<div class='ui-block-a'>플랫폼</div>
		<div class='ui-block-b'>
			<fieldset id="app-platform" class='td-2-item' data-role="controlgroup" data-type="horizontal" data-mini=true init-value="W" >
		        <input name="app-platform" id="app-platform-android" value="A" onclick='mvPage("merchant-campaign-app-add", null, {mcode:"<?=$mcode?>"})' type="radio" />
		        <label for="app-platform-android">Android APP 형</label>
		        <input name="app-platform" id="app-platform-web" value="W" type="radio" />
		        <label for="app-platform-web">WEB 형</label>
		    </fieldset>									
		</div>
		<div class='ui-block-a' style='height:50px'>실행 타입</div>
		<div class='ui-block-b' style='height:50px; padding-top:3px'>
        	<div data-role="fieldcontain" style='padding: 0px 0px; border: 0; margin: 0'>
				<select name="app-type" id="app-type" onchange="<?=$js_page_id?>.action.on_change_app_type()" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c'>
					<option value="F">페이스북 좋아요</option>
				</select>
        	</div>
		</div>
		<div class='ui-block-a'>홈 URL</div>
		<div class='ui-block-b'>
			<div style='width:300px; display: block; height: 20px; padding-top: 5px'>
				<form onsubmit="return <?=$js_page_id?>.action.on_btn_search_info()">
				<div style='width:230px; display: inline-block; height: 20px'>
					<input type="text" id="app-homeurl" name="app-homeurl" value='https://m.facebook.com/samsung'/>
				</div>
				<input type=submit data-role="button" data-theme='c' data-theme="b" data-inline='true' data-mini='true' style="margin-top: 15px; margin:0px 5px" value="검색" />
				</form>
				
			</div>
		</div>		
		<div class='ui-block-a'>실행 URL</div>
		<div class='ui-block-b'>
			<div style='width:300px; display: block; height: 20px; padding-top: 5px'>
				<input type="text" id="app-execurl" name="app-execurl" />
			</div>
		</div>		
		
		<div class='ui-block-a'>제목</div>
		<div class='ui-block-b'>
			<div style='width:300px; display: inline-block; height: 20px; padding-top: 5px'>
				<input type="text" id="app-title" name="app-title" />
			</div>
		</div>
		<div class='ui-block-a' style='height: 100px'>아이콘</div>
		<div class='ui-block-b' style='height: 100px'>
			<div style='width:400px; display: inline-block; height: 20px; padding-top: 5px'>
				<div style='width:80px height: 80px; float: left;'>
					<div style='border: 1px solid #ddd'>
						<img src="" id='img-app-icon' width=80px />
					</div>
				</div>
				<div id='file-upload-div' style='width:300px; float:left; margin-left: 10px; '>
					<input type=file id="upload-image-file" value='파일 업로드' />
					<input type=hidden id="app-image-url" value="" />
				</div>
				<div style='clear:both'></div>
			</div>
		</div>
		<div class='ui-block-a' style='height:100px'>참여 설명</div>
		<div class='ui-block-b' style='height:100px' id='app-exec-desc-wrapper'>
			<div style='width:400px; display: inline-block; padding-top: 5px'>
				<textarea id="app-exec-desc" name="app-exec-desc"></textarea>
			</div>
		</div>
		
		<div class='ui-block-a' style='height:300px'>광고 설명</div>
		<div class='ui-block-b' style='height:300px' id='app-content-wrapper'>
			<div style='width:400px; display: inline-block; padding-top: 5px'>
				<textarea id="app-content" name="app-content">
<br><br>				
[참여안내]<br>
1. 시작하기를 클릭하여 광고에 참여<br>
2. 페이스북 로그인 > 좋아요 클릭<br>
3. 적립하기
				</textarea>
			</div>
		</div>
		<div class='ui-block-a'>광고원가</div>
		<div class='ui-block-b'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-merchant-fee" name="app-merchant-fee" />
			</div>
			<div style='float:left; padding: 15px 10px'>원</div>
			<div style='clear:both'></div>
		</div>
		<div class='ui-block-a'>실행기간</div>
		<div class='ui-block-b'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 2px; float:left'>
				<input type="text" data-role="date" class='td-2-item' name="app-exec-edate" id="app-exec-edate" data-clear-btn=true placeholder='기간없음' value="" />
			</div>
			<div style='float:left; padding: 15px 10px'> 까지 (설정하지 않으면 기간제한 없음)</div>
			<div style='clear:both'></div>
		</div>
		<div class='ui-block-a'>실행시간</div>
		<div class='ui-block-b'>
			<div style='width:60px; display: inline-block; height: 20px; padding-top: 2px; float:left'>
				<div data-role="fieldcontain" style='padding: 0px 0px; border: 0; margin: 0'>
				<select name="app-exec-stime" id="app-exec-stime" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c' init-value='0'>
					<?
						for ($i=0; $i < 25; $i++) {
							echo "<option value='{$i}'>{$i}</option>\n";
						}
					?>
				</select>
        		</div>
			</div>
			<div style='float:left; padding: 15px 10px'>시 부터 </div>
			<div style='width:60px; display: inline-block; height: 20px; padding-top: 2px; float:left'>
				<div data-role="fieldcontain" style='padding: 0px 0px; border: 0; margin: 0'>
				<select name="app-exec-etime" id="app-exec-etime" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c' init-value='24'>
					<?
						for ($i=0; $i < 25; $i++) {
							echo "<option value='{$i}'>{$i}</option>\n";
						}
					?>
				</select>
        		</div>
			</div>
			<div style='float:left; padding: 15px 10px'>시</div>
			<div style='clear:both'></div>
		</div>
		<div class='ui-block-a'>성별 필터</div>
		<div class='ui-block-b'>
			<div class='ui-block-a' style='width:200px'>
				<fieldset id="app-sex" class='td-2-item' data-role="controlgroup" data-type="horizontal" data-mini=true init-value="A">
			        <input name="app-sex" id="app-sex-all" value="A" type="radio" />
			        <label for="app-sex-all">전체</label>
			        <input name="app-sex" id="app-sex-man" value="M" type="radio" />
			        <label for="app-sex-man">남자</label>
			        <input name="app-sex" id="app-sex-woman" value="F" type="radio" />
			        <label for="app-sex-woman">여자</label>
			    </fieldset>
			</div>
		</div>
		<div class='ui-block-a'>나이 필터</div>
		<div class='ui-block-b'>
			<div style='width:60px; display: inline-block; height: 20px; padding-top: 6px; float:left'>
				<input type="number" class='td-2-item' name="app-agefrom" data-clear-btn=true id="app-agefrom" value="0" />
			</div>
			<div style='float:left; padding: 14px 10px'>세 부터 </div>
			<div style='width:90px; display: inline-block; height: 20px; padding-top: 6px; float:left'>
				<input type="number" class='td-2-item' name="app-ageto" data-clear-btn=true id="app-ageto" value="100" />
			</div>
			<div style='float:left; padding: 14px 10px'>세 까지 </div>
			<div style='clear:both'></div>
		</div>
		
		<div class='ui-block-a'>시간 최대 실행</div>
		<div class='ui-block-b'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-exec-hourly-cnt" name="app-exec-hourly-cnt" data-clear-btn=true  init-value='100,000,000' />
			</div>
			<div style='float:left; padding: 15px 10px'>회</div>
			<div style='clear:both'></div>
		</div>
				
		<div class='ui-block-a'>일일 최대 실행</div>
		<div class='ui-block-b'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-exec-daily-cnt" name="app-exec-daily-cnt" data-clear-btn=true init-value='100,000,000' />
			</div>
			<div style='float:left; padding: 15px 10px'>회</div>
			<div style='clear:both'></div>
		</div>
		<div class='ui-block-a'>총 실행 수</div>
		<div class='ui-block-b'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-exec-total-cnt" name="app-exec-total-cnt" init-value='0' />
			</div>
			<div style='float:left; padding: 15px 10px'>회</div>
			<div style='clear:both'></div>
		</div>
		
		<div class='ui-block-a'>매체사 레벨</div>
		<div class='ui-block-b'>
			<div style='padding-top: 2px'>
	        	<div style='width:160px; display: inline-block; float:left'>
					<select name="app-publisher-level" id="app-publisher-level" init-value='9'>
						<option value="9">전체</option>
				        <option value="1">1 (자체서비스)</option>
				        <option value="2">2 (전략적 제휴사)</option>
				        <option value="3">3 (제휴사)</option>
				        <option value="4">4 (비추천 제휴사)</option>
				    </select>	        		
		        </div>
			</div>
			<div style='float:left; padding: 15px 10px'>(지정 레벨보다 낮은 경우 광고 공급 차단됨)</div>
			<div style='clear:both'></div>
		</div>
		
		<div class='ui-block-a' style='height:180px'>오픈일정</div>
		<div class='ui-block-b' style='height:180px'>
			<div>
				<div style='width:44px; display: inline-block; height: 20px; padding-top: 2px; float:left; line-height: 40px'>
					레벨 1
				</div>
				<div style='width:100px; display: inline-block; height: 20px; padding-top: 4px; float:left'>
					<input type="text" data-role="date" class='td-2-item' name="level-1-active-date" id="level-1-active-date" data-clear-btn=true value="" />
				</div>
				<div data-role="fieldcontain" style='display: inline-block; padding-top: 2px; border: 0; margin: 0'>
					<select name="level-1-active-time" id="level-1-active-time" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c' init-value=''>
						<?
							echo "<option></option>\n";
							for ($i=0; $i < 24; $i++) {
								$hour = sprintf("%02d", $i);
								echo "<option value='{$hour}'>{$i}시</option>\n";
							}
						?>
					</select>
	    		</div>
				<div style='clear:both'></div>
			</div>
			<div>
				<div style='width:44px; display: inline-block; height: 20px; padding-top: 2px; float:left; line-height: 40px'>
					레벨 2
				</div>
				<div style='width:100px; display: inline-block; height: 20px; padding-top: 4px; float:left'>
					<input type="text" data-role="date" class='td-2-item' name="level-2-active-date" id="level-2-active-date" data-clear-btn=true value="" />
				</div>
				<div data-role="fieldcontain" style='display: inline-block; padding-top: 2px; border: 0; margin: 0'>
					<select name="level-2-active-time" id="level-2-active-time" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c' init-value=''>
						<?
							echo "<option></option>\n";
							for ($i=0; $i < 25; $i++) {
								$hour = sprintf("%02d", $i);
								echo "<option value='{$hour}'>{$i}시</option>\n";
							}
						?>
					</select>
	    		</div>
				<div style='clear:both'></div>
			</div>
			<div>
				<div style='width:44px; display: inline-block; height: 20px; padding-top: 2px; float:left; line-height: 40px'>
					레벨 3
				</div>
				<div style='width:100px; display: inline-block; height: 20px; padding-top: 4px; float:left'>
					<input type="text" data-role="date" class='td-2-item' name="level-3-active-date" id="level-3-active-date" data-clear-btn=true value="" />
				</div>
				<div data-role="fieldcontain" style='display: inline-block; padding-top: 2px; border: 0; margin: 0'>
					<select name="level-3-active-time" id="level-3-active-time" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c' init-value=''>
						<?
							echo "<option></option>\n";
							for ($i=0; $i < 25; $i++) {
								$hour = sprintf("%02d", $i);
								echo "<option value='{$hour}'>{$i}시</option>\n";
							}
						?>
					</select>
	    		</div>
				<div style='clear:both'></div>
			</div>
			<div>
				<div style='width:44px; display: inline-block; height: 20px; padding-top: 2px; float:left; line-height: 40px'>
					레벨 4
				</div>
				<div style='width:100px; display: inline-block; height: 20px; padding-top: 4px; float:left'>
					<input type="text" data-role="date" class='td-2-item' name="level-4-active-date" id="level-4-active-date" data-clear-btn=true value="" />
				</div>
				<div data-role="fieldcontain" style='display: inline-block; padding-top: 2px; border: 0; margin: 0'>
					<select name="level-4-active-time" id="level-4-active-time" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c' init-value=''>
						<?
							echo "<option></option>\n";
							for ($i=0; $i < 25; $i++) {
								$hour = sprintf("%02d", $i);
								echo "<option value='{$hour}'>{$i}시</option>\n";
							}
						?>
					</select>
	    		</div>
				<div style='clear:both'></div>
			</div>
		</div>
		
		
		
		<iframe id='home-page' src='about:blank' style='width:100%;height:1'></iframe>
	</div>
	<div style='padding-top: 20px'>
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_addcampaign()' data-role='button' data-theme='b' data-inline='true' data-mini='true' >등록하기</a>
	</div>
		
<script type="text/javascript"> 

var <?=$js_page_id?> = function()
{
	// 외부에서 사용할 (Event Callback)함수 정의
	var _$ = function(selector) { if (!selector) return $("#<?=$page_id?>"); return $("#<?=$page_id?>").find(selector); };
	var page = 
	{			
		action: {
			initialize: function() 
			{
				util.initPage($('#page'));
				_$("div[data-role='popup']").on("popupbeforeposition", function(){ util.initPage($(this)); });
				
				$("#app-exec-desc").cleditor();
				$("#app-content").cleditor();				
				
				page.action.on_change_app_type();
			},
			on_change_app_type: function()
			{
				if (_$("#app-type").val() == 'F') {
				}
			},
			on_btn_search_info: function()
			{
				var url =  _$("#app-homeurl").val();
				if (!url) {
					alert('홈 URL을 입력하세요.');
					return false;
				}
				
				if (url.indexOf('https://m.facebook.com') != 0) {
					alert('모바일용 홈 URL을 넣으세요. https://m.facebook.com/ ... ');
					return false;
				}
				
				$.mobile.loading('show');
				// facebook인 경우 홈에서 제목/아이콘/참여설명을 가져온다.
				// https://m.facebook.com/samsung
				
				var ar_param = {'url': url};
				var request_url = get_ajax_url('get-url', ar_param);
				_$("#home-page").attr('src', request_url);
				
				var startTimer = (new Date()).getTime();
				var timer = setInterval(function() {
					
					if ((new Date()).getTime() - startTimer > 20*1000) 
					{
						$.mobile.loading('hide');
						clearInterval(timer);
						alert('요청 시간 초과')
					} 
					else 
					{
						if (frames[0].window.location.href == request_url && frames[0].window.document.readyState == 'complete') {
							$.mobile.loading('hide');
							clearInterval(timer);
							
							// 홈 URL (빈 경우에만)
							if (!_$("#app-execurl").val()) _$("#app-execurl").val(_$("#app-homeurl").val());

							// 프로필 이미지 가져오기							
							var profilePic = $(".profilePicContainer .profilePic", frames[0].window.document).attr('style').match(/url\(\"(.*)\"\)/i)[1];
							var ar_param = {url: profilePic};
							util.request(get_ajax_url('get-base64-img', ar_param), function(sz_data) {
								var js_data = util.to_json(sz_data);
								if (js_data['result']) {
									_$("#img-app-icon").data('type', 'base64');		// base64 데이터형식인지, URL인지 구별
									_$("#img-app-icon").attr('src', 'data:image/png;base64,' + js_data['base64']);
									_$("#app-image-url").val(js_data['base64']);			
								}
							});
							
							// 제목
							var title = $("head title", frames[0].window.document).text();
							_$("#app-title").val(title);
							
							// 사이트 인사말
							var description = '';
								var desc_html = $(".decelerateChildren", frames[0].window.document).html();
								desc_html = desc_html.replace(/<\div/gi, '<br><div');
								var desc_text = g_admin_util.strip_tags(desc_html.replace(/<\/span\>/gi, '</span><br>'), '<br>');
								var arr_find = desc_text.match(/^정보(.*?)(다른 사람들이|좋아요|https?\:\/\/)/); 
								if (arr_find) description = arr_find[1];
								
							_$("#app-content").val(description + _$("#app-content").val()).blur();
							
							_$("#home-page").attr('src', 'about:blank');
						}
					}
				}, 1000);

				return false;
			},
			on_btn_addcampaign: function()
			{
				var app_type = _$("#app-type").val();

				var ar_param = {
					'mcode' : '<?=$mcode?>',
					'appkey' : '<?=$appkey?>',
					'appplatform' : util.get_item_value(_$("#app-platform")),
					'apptype' : _$("#app-type").val(),
					'apphomeurl' : _$("#app-homeurl").val(),
					'appexecurl' : _$("#app-execurl").val(),
					'apptitle' : _$("#app-title").val(),
					'appimageurl' : _$("#app-image-url").val(),
					'appimagetype' : _$("#img-app-icon").data('type'),		// base64 데이터형식인지, URL인지 구별
					'appexecdesc' : _$("#app-exec-desc").val(),
					'appmarket' : 'W',
					'appcontent' : _$("#app-content").val(),
					'appgender' : util.get_item_value(_$("#app-sex")),
					'appagefrom' : util.intval(_$("#app-agefrom").val()),
					'appageto' : util.intval(_$("#app-ageto").val()),
					'appmerchantfee' : util.intval(_$("#app-merchant-fee").val()),
					'appexecedate' : _$("#app-exec-edate").val(),
					'appexecstime' : _$("#app-exec-stime").val(),
					'appexecetime' : _$("#app-exec-etime").val(),
					'appexechourlycnt': util.intval(_$("#app-exec-hourly-cnt").val()),
					'appexecdailycnt' : util.intval(_$("#app-exec-daily-cnt").val()),
					'appexectotalcnt' : util.intval(_$("#app-exec-total-cnt").val()),

					'apppublisherlevel': util.get_item_value(_$("#app-publisher-level")),
					
					'level1activedate': _$("#level-1-active-date").val() ? _$("#level-1-active-date").val() + " " + util.get_item_value(_$("#level-1-active-time")) : "",
					'level2activedate': _$("#level-2-active-date").val() ? _$("#level-2-active-date").val() + " " + util.get_item_value(_$("#level-2-active-time")) : "",
					'level3activedate': _$("#level-3-active-date").val() ? _$("#level-3-active-date").val() + " " + util.get_item_value(_$("#level-3-active-time")) : "",
					'level4activedate': _$("#level-4-active-date").val() ? _$("#level-4-active-date").val() + " " + util.get_item_value(_$("#level-4-active-time")) : ""
				};
				
				if (ar_param.apphomeurl.indexOf('https://m.facebook.com') != 0) {
					alert('모바일용 홈 URL을 넣으세요. https://m.facebook.com/ ... ');
					return false;
				}
				if (ar_param.appexecurl.indexOf('https://www.facebook.com') == 0) {
					alert('모바일용 홈 URL을 넣으세요. https://m.facebook.com/ ... ');
					return false;
				}

				util.post(get_ajax_url('admin-campaign-app-add'), ar_param, function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						util.Alert('알림', '등록되었습니다.', function() {
							mvPage('merchant-campaign-web-modify', null, {partnerid:'<?=$partner_id?>', mcode:'<?=$mcode?>', appkey:js_data['app_key']});
						});	
					} else util.Alert(js_data['msg']);
				});
				
			},	

		},
		upload: {
		
			on_change_file: function(e) {
				
				var data = new FormData();
				data.append('file', _$("#upload-image-file")[0].files[0]);
				data.append('width', 150);
				data.append('height', 150);
				$.ajax({
					url: get_ajax_url('_upload_file', {pos: 'app-images'}),
					type: 'POST',
					data: data,
					cache: false,
					processData: false,
					contentType: false,
					success: function(sz_data, textStatus, jqXHR) {
						var js_data = util.to_json(sz_data);
						_$("#img-app-icon").data('type', 'url');		// base64 데이터형식인지, URL인지 구별
						_$("#img-app-icon").attr('src', js_data['url']);
						_$("#app-image-url").val(js_data['url']);
					}, 
					error: function(jqXHR, textStatus, erroThrown) {
						alert('파일 업로드 실패 - 오류가 계속 되면 관리자에게 문의 바랍니다. \n\n오류 : ' + textStatus);
					}
				});
				
			},
		},		
	};		
	
	function setEvents() {
		$(document).on("pageinit", function(){page.action.initialize();} );

		_$("#upload-image-file").change(page.upload.on_change_file);

		util.set_event_for_input_number(_$("#app-merchant-fee"));

		util.set_event_for_input_number(_$("#app-agefrom"), '0');
		util.set_event_for_input_number(_$("#app-ageto"), '100');
				
		util.set_event_for_input_number(_$("#app-exec-hourly-cnt"), '100,000,000');
		util.set_event_for_input_number(_$("#app-exec-daily-cnt"), '100,000,000');
		util.set_event_for_input_number(_$("#app-exec-total-cnt"), '100,000,000');
	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
	
		
		
		