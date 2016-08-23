<?
	$partner_id = $_REQUEST['partnerid'];
	$mcode = $_REQUEST['mcode'];
?>
	<style>
		#add_app	.ui-block-a	{height: 45px; line-height:45px; padding-left: 10px; width:100px; border-bottom: 1px solid #ddd; font-weight: bold}
		#add_app	.ui-block-b	{height: 45px; width:500px; border-bottom: 1px solid #ddd}
		
		#app-content	{height: 80px !important; overflow-y: scroll !important}
		#app-exec-desc 	{height: 180px !important; overflow-y: scroll !important}

		.required			{background-color:lightyellow}
	</style>
	<t3 style='height:40px; padding-top:20px'>WEB 방식 광고 등록</t3>
	<hr>
	<div id='add_app' class='ui-grid-a'>
		<div class='ui-block-a'>플랫폼</div>
		<div class='ui-block-b'>
			<fieldset id="app-platform" class='td-2-item' data-role="controlgroup" data-type="horizontal" data-mini=true init-value="W" >
		        <input name="app-platform" id="app-platform-android" value="A" onclick='mvPage("merchant-campaign-add-app", null, {mcode:"<?=$mcode?>"})' type="radio" />
		        <label for="app-platform-android">Android APP 형</label>
		        <input name="app-platform" id="app-platform-web" value="W" type="radio" />
		        <label for="app-platform-web">기타</label>
		    </fieldset>
		</div>
		<div class='ui-block-a' style='height:50px'>실행 타입</div>
		<div class='ui-block-b' style='height:50px; padding-top:3px'>
        	<div data-role="fieldcontain" style='padding: 0px 0px; border: 0; margin: 0'>
				<select name="app-type" id="app-type" onchange="<?=$js_page_id?>.action.on_change_app_type()" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c'>
					<option value="W">CPA (가입/신청 수행형)</option>
				</select>
        	</div>
		</div>
		<div class='ui-block-a' style='height:65px'>홈 URL</div>
		<div class='ui-block-b' style='height:65px'>
			<div style='width:500px; display: block; height: 20px; padding-top: 5px'>
				<form onsubmit="return <?=$js_page_id?>.action.on_btn_search_info()">
				<div style='width:330px; display: inline-block; height: 20px'>
					<input type="text" id="app-homeurl" name="app-homeurl" value='https://m.facebook.com/samsung'/>
				</div>
				<input type=submit data-role="button" data-theme='c' data-theme="b" data-inline='true' data-mini='true' style="margin-top: 15px; margin:0px 5px" value="검색" />
				</form>
				<div style='color:#aaa'>안드로이드 마켓, 페이스북, 카카오스토리, 인스타그램의 지정 페이지만 검색 가능함.</div>
			</div>
		</div>
		<div class='ui-block-a'>Package ID</div>
		<div class='ui-block-b'>
			<div style='width:400px; display: inline-block; height: 20px; padding-top: 5px'>
				<input type="text" id="app-packageid" name="app-packageid" />
			</div>
		</div>
		<div class='ui-block-a'>실행 URL</div>
		<div class='ui-block-b'>
			<div style='width:400px; display: block; height: 20px; padding-top: 5px'>
				<input type="text" id="app-execurl" name="app-execurl" />
			</div>
		</div>		
		<div class='ui-block-a'>적립URL 메모</div>
		<div class='ui-block-b'>
			<div style='width:400px; display: block; height: 20px; padding-top: 5px'>
				<input type="text" id="app-etc" name="app-etc" />
			</div>
		</div>		
		
		<div class='ui-block-a'>제목</div>
		<div class='ui-block-b'>
			<div style='width:400px; display: inline-block; height: 20px; padding-top: 5px'>
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
		<div class='ui-block-a' style='height:100px'>광고 설명</div>
		<div class='ui-block-b' style='height:100px' id='app-content-wrapper'>
			<div style='width:400px; display: inline-block; padding-top: 5px'>
				<textarea id="app-content" name="app-content"></textarea>
			</div>
		</div>
		
		<div class='ui-block-a' style='height:200px'>적립 방법</div>
		<div class='ui-block-b' style='height:200px' id='app-exec-desc-wrapper'>
			<div style='width:400px; display: inline-block; padding-top: 5px'>
				<textarea id="app-exec-desc" name="app-exec-desc">

[참여안내]
1. 시작하기를 클릭하여 광고에 참여 후 미션 수행
				</textarea>
			</div>
		</div>
		<div class='ui-block-a required'>매출원가</div>
		<div class='ui-block-b required'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-merchant-fee" name="app-merchant-fee" />
			</div>
			<div style='float:left; padding: 15px 10px'>원 - 판매시 매출로 잡히는 금액</div>
			<div style='clear:both'></div>
		</div>
		<div class='ui-block-a required'>광고원가</div>
		<div class='ui-block-b required'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-tag-price" name="app-tag-price" />
			</div>
			<div style='float:left; padding: 15px 10px'>원 - 일반적으로 매출원가와 같음</div>
			<div style='clear:both'></div>
		</div>
		<div class='ui-block-a required'>총 실행 수</div>
		<div class='ui-block-b required'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-exec-total-cnt" name="app-exec-total-cnt" init-value='0' />
			</div>
			<div style='float:left; padding: 15px 10px'>회</div>
			<div style='clear:both'></div>
		</div>
		<div class='ui-block-a'>주말수행</div>
		<div class='ui-block-b'>
			<div class='ui-block-a' style='width:200px; padding-left: 0px'>
				<fieldset id="app-exec-weekend" class='td-2-item' data-role="controlgroup" data-type="horizontal" data-mini=true init-value="Y">
			        <input name="app-exec-weekend" id="app-exec-weekend-Y" value="Y" type="radio" />
			        <label for="app-exec-weekend-Y">주말수행</label>
			        <input name="app-exec-weekend" id="app-exec-weekend-N" value="N" type="radio" />
			        <label for="app-exec-weekend-N">주말중지</label>
			    </fieldset>
			</div>
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
		
		
		
		<iframe id='home-page' src='about:blank' style='width:100%;height:1'></iframe>
	</div>
	<div style='padding-top: 20px'>
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_addcampaign()' data-role='button' data-theme='b' data-inline='true' data-mini='true' >등록하기</a>
	</div>
	
<div style='border: 1px solid gray; padding: 0 5px; margin-top: 10px'>
<pre><b>A. 실행 URL에 데이터 파라미터 추가하기</b>
	
	<b>1. 값 종류 및 표기</b>
		[IMEI] : IMEI 값
		[ADID] : ADID 값
		[MAC] : MAC 주소 값
		[IP] : IP 주소 값
		[USERDATA] : UserData 값
		
	<b>2. 값을 Encoding하기 (PostFix )</b>
		.B64 : Bas64 Encoding. ex) [ADID.B64] : ADID Base64 Encoding 한 값
		.URL : URL Encoding. ex) [ADID.URL] : ADID를 URL Encoding 한 값
		.MD5 : MD5 Encoding. ex) [ADID.MD5] : ADID를 MD5 Encoding 한 값
		
		* 복합
			[ADID.B64.URL] : ADID를 B64한 후 URL Encoding 한 값
			[USERDATA.URL.URL] : USERDATA를 2번 URL Encoding 한 값
		
	<b>3. 실행 URL 예제</b>
		http://adfree.gmnc.net/elink/ready.php?gid=c1d44a5296482f7c3dbe901b3c14e4b6<span style='color:red;font-weight: bold'>&userdata=[USERDATA.URL]</span>
		market://details?id=com.kt.android.showtouch&referrer=ns_chid%3D3025%26nsw_media_id<span style='color:red;font-weight: bold'>%26userdata%3D[USERDATA.URL.URL]</span>
		http://w2.ohpoint.co.kr/ohpoint/controll.do?path=304&c_id=hanamembers&m_id=marshsoft&referrer=ohc.hanamembers.marshsoft<span style='color:red;font-weight: bold'>&adid=[ADID]&userdata=[USERDATA.URL]</span>
		
<b>B. 실적 호출 주소 규칙</b>
	
	리턴주는 주소 규칙이 http://www.도메인.co.kr/result.asp 를 등록하면
	실제 호출이 ==> http://www.도메인.co.kr/result.asp?<span style='color:red;font-weight: bold'>adkey</span>=??? 인 경우
	
	http://cb.aline-soft.kr/<span style='color:red;font-weight: bold'>adkey</span>/result.json 을 등록함
	실제 호출 http://cb.aline-soft.kr/<span style='color:red;font-weight: bold'>adkey</span>/result.json?<span style='color:red;font-weight: bold'>adkey</span>=??? 로 리턴됨
		
</pre>
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
				
				// $("#app-exec-desc").cleditor();
				// $("#app-content").cleditor();				
				
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
					alert('홈 URL을 입력하세요.\n\n[형식]\n안드로이드마켓 : https://play.google.com/store/apps/details?id=a.b.c\n페이스북 : https://m.facebook.com/samsung\n카카오스토리 : https://story.kakao.com/ch/kbs\n인스타그램 : https://www.instagram.com/ceo');
					return false;
				}
				
				$.mobile.loading('show');
				
				// 1. 구글 마켓인 경우 처리
				if (url.indexOf('https://play.google.com') == 0) {
					var ar_info = url.match(/[?&]id\=([^&]*)/i);
					if (ar_info.length < 2) {
						$.mobile.loading('hide');
						alert('URL형식을 확인하세요');	
						return;
					}
					var url = "http://heartoffice.iptime.org:12680/google-store/detail.php?id=" + ar_info[1];
					util.request(url, function(sz_data) {
						var js_data = util.to_json(sz_data);
						
						var profilePic = 'https:' + js_data['image'];
						var title = js_data['title'];
						var description = '';;

						// -----------------------------------------------------------------
						// image to base64 text							
						// -----------------------------------------------------------------
						var ar_param = {url: profilePic};
						util.request(get_ajax_url('get-base64-img', ar_param), function(sz_data) {
							var js_data = util.to_json(sz_data);
							if (js_data['result']) {
								_$("#img-app-icon").data('type', 'base64');
								_$("#img-app-icon").attr('src', 'data:image/png;base64,' + js_data['base64']);
								_$("#app-image-url").val(js_data['base64']);			
							}
						});
						
						_$("#app-packageid").val(ar_info[1]);
						_$("#app-title").val(title);
						_$("#app-content").val(description + _$("#app-content").val()).blur();
						_$("#home-page").attr('src', 'about:blank');						
						
						// 홈 URL (빈 경우에만)
						if (!_$("#app-execurl").val()) _$("#app-execurl").val(_$("#app-homeurl").val());
						$.mobile.loading('hide');
						
					});
				
					return false;				
				}
				// 2. 웹 페이지의 경우 처리
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
							// if (!_$("#app-execurl").val()) _$("#app-execurl").val(_$("#app-homeurl").val());

							if (url.indexOf('https://m.facebook.com') == 0) {
								var profilePic = $(".profilePicContainer .profilePic", frames[0].window.document).attr('style').match(/url\(\"(.*)\"\)/i)[1];
								// var profilePic = $("img[alt*='프로필 사진']", frames[0].window.document).attr('src');
								var title = $("head title", frames[0].window.document).text();
								var description = $('meta[name="description"]', frames[0].window.document).attr('content');
							} else if (url.indexOf('https://story.kakao.com') == 0) {
								var profilePic = $('meta[property="og:image"]', frames[0].window.document).attr('content');
								var title = $('meta[property="og:title"]', frames[0].window.document).attr('content');
								var description = '';
							} else if (url.indexOf('https://www.instagram.com') == 0) {
								var profilePic = $('meta[property="og:image"]', frames[0].window.document).attr('content');
								var title = $('meta[property="og:title"]', frames[0].window.document).attr('content');
								var description = $('meta[property="og:description"]', frames[0].window.document).attr('content');
							}

							// image to base64 text							
							var ar_param = {url: profilePic};
							util.request(get_ajax_url('get-base64-img', ar_param), function(sz_data) {
								var js_data = util.to_json(sz_data);
								if (js_data['result']) {
									_$("#img-app-icon").data('type', 'base64');
									_$("#img-app-icon").attr('src', 'data:image/png;base64,' + js_data['base64']);
									_$("#app-image-url").val(js_data['base64']);			
								}
							});
							
							_$("#app-title").val(title);
							_$("#app-content").val(ifempty(description,'') + _$("#app-content").val()).blur();
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
					'apppackageid' : _$("#app-packageid").val(),
					'appexecurl' : _$("#app-execurl").val(),
					'appetc' : _$("#app-etc").val(),
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
					'apptagprice' : util.intval(_$("#app-tag-price").val()),
					'appexecweekend' : util.get_item_value(_$("#app-exec-weekend")),
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
		_$("#app-merchant-fee").change(function(){ _$("#app-tag-price").val(_$("#app-merchant-fee").val());});

		util.set_event_for_input_number(_$("#app-merchant-fee"));
		util.set_event_for_input_number(_$("#app-tag-price"));

		util.set_event_for_input_number(_$("#app-agefrom"), '0');
		util.set_event_for_input_number(_$("#app-ageto"), '100');
				
		util.set_event_for_input_number(_$("#app-exec-hourly-cnt"), '');
		util.set_event_for_input_number(_$("#app-exec-daily-cnt"), '');
		util.set_event_for_input_number(_$("#app-exec-total-cnt"), '');
	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
	
		
		
		