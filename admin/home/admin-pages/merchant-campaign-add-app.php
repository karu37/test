<?
	$partner_id = $_REQUEST['partnerid'];
	$mcode = $_REQUEST['mcode'];
?>
<div>
	<t3 style='height:40px; padding-top:20px'>APP 광고 등록</t3>
	<hr>
	<style>
		#app-info	.ui-block-a	{height: 45px; line-height:45px; padding-left: 10px; width:100px; border-bottom: 1px solid #ddd; font-weight: bold}
		#app-info	.ui-block-b	{height: 45px; width:500px; border-bottom: 1px solid #ddd}
		
		#app-info	#app-exec-desc-wrapper	.cleditorMain	{height: 85px !important}
		#app-info	#app-exec-desc-wrapper	.cleditorMain iframe	{height: 32px !important}
		
		#app-info	#app-content-wrapper	.cleditorMain	{height: 285px !important}
		#app-info	#app-content-wrapper	.cleditorMain iframe	{height: 232px !important}

		.required			{background-color:lightyellow}
		.app-keyword-wrapper					{background-color: lightgreen}
	</style>
	<div id='app-info' class='ui-grid-a'>
		<div class='ui-block-a'>플랫폼</div>
		<div class='ui-block-b'>
			<fieldset id="app-platform" class='td-2-item' data-role="controlgroup" data-type="horizontal" data-mini=true init-value="A" >
		        <input name="app-platform" id="app-platform-android" value="A" type="radio" />
		        <label for="app-platform-android">Android APP 형</label>
		        <input name="app-platform" id="app-platform-web" value="W" onclick='mvPage("merchant-campaign-add-web", null, {mcode:"<?=$mcode?>"})' type="radio" />
		        <label for="app-platform-web">WEB 형</label>
		    </fieldset>									
		</div>
		<div class='ui-block-a' style='height:50px'>실행 타입</div>
		<div class='ui-block-b' style='height:50px; padding-top:3px'>
        	<div data-role="fieldcontain" style='padding: 0px 0px; border: 0; margin: 0'>
				<select name="app-type" id="app-type" onchange="<?=$js_page_id?>.action.on_change_app_type()" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c'>
					<option value="I" selected>설치형</option>
					<option value="E">실행형</option>
					<option value="S">검색설치형</option>
				</select>
        	</div>
		</div>
		<div class='ui-block-a app-search-wrapper'>마켓 검색</div>
		<div class='ui-block-b app-search-wrapper' style='padding-top:3px'>
			<form onsubmit="return <?=$js_page_id?>.action.on_btn_searchmarket()">
			<div style='width:230px; display: inline-block; height: 20px'>
				<input type="text" id="txt-searchmarket" name="txt-searchmarket" />
			</div>
			<input type=submit data-role="button" data-theme='c' data-theme="b" data-inline='true' data-mini='true' style="margin-top: 15px; margin:0px 5px" value="검색" />
			</form>
		</div>
		<div class='ui-block-a'>앱 이름</div>
		<div class='ui-block-b'>
			<div style='width:300px; display: inline-block; height: 20px; padding-top: 5px'>
				<input type="text" id="app-title" name="app-title" />
			</div>
		</div>
		<div class='ui-block-a'>Package ID</div>
		<div class='ui-block-b'>
			<div style='width:300px; display: inline-block; height: 20px; padding-top: 5px'>
				<input type="text" id="app-packageid" name="app-packageid" />
			</div>
		</div>
		<div class='ui-block-a app-keyword-wrapper' style='height: 55px'>검색 키워드</div>
		<div class='ui-block-b app-keyword-wrapper' style='height: 55px'>
			<div style='width:300px; display: inline-block; padding-top: 5px'>
				<input type="text" id="app-keyword" name="app-keyword" />
			</div>
			<br>
		</div>		
		<div class='ui-block-a' style='height: 55px'>마켓 링크</div>
		<div class='ui-block-b' style='height: 55px'>
			<div style='width:300px; display: block; height: 20px; padding-top: 5px'>
				<input type="text" id="app-execurl" name="app-execurl" />
			</div>
			<br>
			(고객이 요청한 URL을 꼭 경우해야하는 경우에만 사용 - 사용시 검색키워드 사용불가)
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
1. 시작하기를 클릭하여 광고에 참여한다.<br>
2. 참여안내페이지가 있을 경우 반드시 해당 안내페이지의 내용을 숙지하시고 참여를 하셔야 합니다.<br>
3. 참여완료 후에 반드시 적립하기를 클릭하여 적립받으세요!
				</textarea>
			</div>
		</div>
		<div class='ui-block-a required'>광고원가</div>
		<div class='ui-block-b required'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-merchant-fee" name="app-merchant-fee" />
			</div>
			<div style='float:left; padding: 15px 10px'>원</div>
			<div style='clear:both'></div>
		</div>
		<div class='ui-block-a required'>총 실행 수</div>
		<div class='ui-block-b required'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-exec-total-cnt" name="app-exec-total-cnt"  init-value='0' />
			</div>
			<div style='float:left; padding: 15px 10px'>회</div>
			<div style='clear:both'></div>
		</div>
		
		
		<div class='ui-block-a'>광고종료</div>
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
				<input type="number" class='td-2-item' name="app-agefrom" id="app-agefrom" data-clear-btn=true value="0" />
			</div>
			<div style='float:left; padding: 14px 10px'>세 부터 </div>
			<div style='width:90px; display: inline-block; height: 20px; padding-top: 6px; float:left'>
				<input type="number" class='td-2-item' name="app-ageto" id="app-ageto" data-clear-btn=true value="100" />
			</div>
			<div style='float:left; padding: 14px 10px'>세 까지 </div>
			<div style='clear:both'></div>
		</div>

		<div class='ui-block-a'>시간 최대 실행</div>
		<div class='ui-block-b'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-exec-hourly-cnt" name="app-exec-hourly-cnt" data-clear-btn=true placeholder='제한 없음' init-value='' />
			</div>
			<div style='float:left; padding: 15px 10px'>회</div>
			<div style='clear:both'></div>
		</div>
		
		<div class='ui-block-a'>일일 최대 실행</div>
		<div class='ui-block-b'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-exec-daily-cnt" name="app-exec-daily-cnt" data-clear-btn=true placeholder='제한 없음' init-value='' />
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
					<input type="text" data-role="date" class='td-2-item' name="level-1-active-date" id="level-1-active-date" data-clear-btn=true placeholder='일정 없음' value="" />
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
					<input type="text" data-role="date" class='td-2-item' name="level-2-active-date" id="level-2-active-date" data-clear-btn=true placeholder='일정 없음' value="" />
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
					<input type="text" data-role="date" class='td-2-item' name="level-3-active-date" id="level-3-active-date" data-clear-btn=true placeholder='일정 없음' value="" />
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
					<input type="text" data-role="date" class='td-2-item' name="level-4-active-date" id="level-4-active-date" data-clear-btn=true placeholder='일정 없음' value="" />
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
		
	</div>
	<div style='padding-top: 20px'>
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_addcampaign()' data-role='button' data-theme='b' data-inline='true' data-mini='true' >등록하기</a>
	</div>
	
	<!-- ----------------------------------------------------------------- -->	
	<div data-role="popup" id="popup-search-keyword" data-overlay-theme="a">
		<div data-role="header" data-theme="a">
	    	<h1>스토어 검색</h1>
		</div>
	    <div data-role="main" id="content">
	        <div style="padding: 10px 10px">
			
			<t4 id='popup-golf-jang' style='line-height:40px'></t4>
			<hr>
			<div class="ui-grid-a" style="padding: 0 20px; width: 500px">
				<div class="ui-block-a" style="width:20%">
					<p3 style='line-height:40px'>검색</p3>
				</div>
				<div class="ui-block-b" style="width:80%;">
					<form onsubmit='return <?=$js_page_id?>.action.on_popup_submit_keyword_search()'>
					<table border=0 cellpadding=0 cellspacing=0 class='no-border'>
					<tr><td>
						<input type="text" id="input-search-keyword" name="input-search-keyword" data-clear-btn="true" value="" />
					</td><td>
						<input type='submit' data-theme='b' data-role="button" data-mini="true" value='검색' />
					</td></tr>
					</table>
					</form>
				</div>
			</div>
			<hr>
			<div class="ui-grid-a" style="padding: 0 20px">
				<div class="ui-block-a" style="width:20%">
					<p3 style='line-height:40px'>검색 결과</p3>
				</div>
				<div class="ui-block-b" style="width:80%">
					<div class="ui-field-contain" style="margin:0; padding:0">
						<style>
							#ctl-keyword-result	li:not(:last-child) {border-bottom: 1px solid #ddd}
							#ctl-keyword-result	li {padding: 5px 5px}
						</style>
						<ul data-role="listview" id="ctl-keyword-result" style='height:400px; overflow-y:scroll' data-inset="true" data-corners="false" init-html="">
			    		</ul>
					</div>
				</div>
			</div>
			<hr>
			<div style='padding: 20px 20% 10px 20%'>
				<a href='#' data-rel='back' data-rel='back' data-role='button' data-mini='true' >닫기</a>
			</div>
	    </div>	
	</div>		
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
			on_btn_searchmarket: function()
			{
				_$("#popup-search-keyword").popup("open");
				_$("#input-search-keyword").val(_$("#txt-searchmarket").val());
				setTimeout(function(){page.action.on_popup_submit_keyword_search();}, 100);
				return false;
			},
			on_change_app_type: function()
			{
				_$(".app-keyword-wrapper").hide();
				_$(".app-iconext-wrapper").hide();
				if (_$("#app-type").val() == 'I' || _$("#app-type").val() == 'E') {
					
				}
				else if (_$("#app-type").val() == 'S') {
					_$(".app-keyword-wrapper").show();
				}

			},
			on_popup_submit_keyword_search: function() 
			{
				var ar_param = {pageid: "<?=$js_page_id?>", q: _$("#input-search-keyword").val()};
				$.mobile.loading("show");
				util.request(get_ajax_url('playstore-search-result', ar_param), function(sz_data) {
					$.mobile.loading("hide");
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						_$("#ctl-keyword-result").html(js_data['data']);
					} else {
						util.Alert(js_data['msg']);
					}
				});
				return false;
			},
			on_select_google_campaign: function(packageid, title, base64_image) {
				
				_$("#app-title").val(title);
				_$("#app-packageid").val(packageid);
				_$("#img-app-icon").data('type', 'base64');	// base64 데이터형식인지, URL인지 구별
				_$("#img-app-icon").attr('src', 'data:image/png;base64,' + base64_image);
				_$("#app-image-url").val(base64_image);			
				
				$("#popup-search-keyword").popup("close");	
			},
			on_btn_addcampaign: function()
			{
				var app_type = _$("#app-type").val();

				// 검색형 / Review형인 경우 키워드,마켓링크 모두 사용 불가
				if (util.in_array(app_type, ['S'])) {
					if (_$("#app-execurl").val() && _$("#app-keyword").val()) {
						alert('[마켓링크]와 [검색 키워드] 모두 설정해서 사용할 수 없습니다.\n두개 중 하나만 입력하세요');
						return;
					}
				}
				
				// 검색형 / Review형이 아닌 경우 Keyword Clear
				if (!util.in_array(app_type, ['S'])) {
					_$("#app-keyword").val("");
				}
				
				var ar_param = {
					'mcode' : '<?=$mcode?>',
					'appplatform' : util.get_item_value(_$("#app-platform")),
					'apptype' : _$("#app-type").val(),
					'apppackageid' : _$("#app-packageid").val(),
					'appkeyword' : _$("#app-keyword").val(),
					'appexecurl' : _$("#app-execurl").val(),
					'apptitle' : _$("#app-title").val(),
					'appimageurl' : _$("#app-image-url").val(),
					'appimagetype' : _$("#img-app-icon").data('type'),		// base64 데이터형식인지, URL인지 구별
					'appexecdesc' : _$("#app-exec-desc").val(),
					'appmarket' : 'P',
					'appcontent' : _$("#app-content").val(),
					'appgender' : util.get_item_value(_$("#app-sex")),
					'appagefrom' : util.intval(_$("#app-agefrom").val()),
					'appageto' : util.intval(_$("#app-ageto").val()),
					'appmerchantfee' : util.intval(_$("#app-merchant-fee").val()),
					'appexecedate' : _$("#app-exec-edate").val(),
					'appexecstime' : _$("#app-exec-stime").val(),
					'appexecetime' : _$("#app-exec-etime").val(),
					'appexechourlycnt': util.intval(_$("#app-exec-hourly-cnt").val(), ""),
					'appexecdailycnt' : util.intval(_$("#app-exec-daily-cnt").val(), ""),
					'appexectotalcnt' : util.intval(_$("#app-exec-total-cnt").val(), ""),
					
					'apppublisherlevel': util.get_item_value(_$("#app-publisher-level")),
					
					'level1activedate': _$("#level-1-active-date").val() ? _$("#level-1-active-date").val() + " " + util.get_item_value(_$("#level-1-active-time")) : "",
					'level2activedate': _$("#level-2-active-date").val() ? _$("#level-2-active-date").val() + " " + util.get_item_value(_$("#level-2-active-time")) : "",
					'level3activedate': _$("#level-3-active-date").val() ? _$("#level-3-active-date").val() + " " + util.get_item_value(_$("#level-3-active-time")) : "",
					'level4activedate': _$("#level-4-active-date").val() ? _$("#level-4-active-date").val() + " " + util.get_item_value(_$("#level-4-active-time")) : ""
				};
				
				if (!util.is_empty(ar_param.appexechourlycnt) && ar_param.appexechourlycnt <= 0) {
					alert('시간 최대 실행 값을 확인하세요 ');
					return;					
				}
				if (!util.is_empty(ar_param.appexecdailycnt) && ar_param.appexecdailycnt <= 0) {
					alert('일일 최대 실행 값을 확인하세요 ');
					return;					
				}
				
				util.post(get_ajax_url('admin-campaign-app-add'), ar_param, function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						util.Alert('알림', '등록되었습니다.', function() {
							mvPage('merchant-campaign-modify', null, {partnerid:'<?=$partner_id?>', mcode:'<?=$mcode?>', appkey:js_data['app_key']});
						});	
					} else util.Alert(js_data['msg']);
				});
				
			},
			on_change_active_date: function(obj, level) {
				var date = $(obj).val();
				if (level <= 1) _$("#level-2-active-date").val(date);
				if (level <= 2) _$("#level-3-active-date").val(date);
				if (level <= 3) _$("#level-4-active-date").val(date);
			},
			on_change_active_time: function(obj, level) {
				var time = $(obj).val();
				if (level <= 1) util.set_item_value(_$("#level-2-active-time"), time);
				if (level <= 2) util.set_item_value(_$("#level-3-active-time"), time);
				if (level <= 3) util.set_item_value(_$("#level-4-active-time"), time);
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
				
		util.set_event_for_input_number(_$("#app-exec-hourly-cnt"), '');
		util.set_event_for_input_number(_$("#app-exec-daily-cnt"), '');
		util.set_event_for_input_number(_$("#app-exec-total-cnt"), '');
		
		
		_$("#level-1-active-date").change(function(){ page.action.on_change_active_date(this, 1); });
		_$("#level-2-active-date").change(function(){ page.action.on_change_active_date(this, 2); });
		_$("#level-3-active-date").change(function(){ page.action.on_change_active_date(this, 3); });
		_$("#level-4-active-date").change(function(){ page.action.on_change_active_date(this, 4); });

		_$("#level-1-active-time").change(function(){ page.action.on_change_active_time(this, 1); });
		_$("#level-2-active-time").change(function(){ page.action.on_change_active_time(this, 2); });
		_$("#level-3-active-time").change(function(){ page.action.on_change_active_time(this, 3); });
		_$("#level-4-active-time").change(function(){ page.action.on_change_active_time(this, 4); });

	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
	
		
		
		