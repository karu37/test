<?
	$partner_id = $_REQUEST['partnerid'];
	$mcode = $_REQUEST['mcode'];
	$appkey = $_REQUEST['appkey'];
	
	$db_appkey = mysql_real_escape_string($appkey);
	$db_mcode = mysql_real_escape_string($mcode);
	
	$sql = "SELECT * FROM al_app_t WHERE app_key = '{$db_appkey}' AND mcode = '{$db_mcode}'";
	
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
?>
<div>
	<t3 style='height:40px; padding-top:20px'>광고 정보 수정</t3>
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
	<div style='padding: 10px'>
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_modifycampaign()' data-role='button' data-theme='b' data-inline='true' data-mini='true' >변경사항 적용하기</a>
		<a href='?id=campaign-by-appkey-list&appkey=<?=urlencode($row['app_key'])?>' data-theme='b' data-role='button' data-mini='true' data-inline='true'>참가자</a>
		
		<a href='?id=campaign-app-exec-view&appkey=<?=urlencode($row['app_key'])?>' data-role='button' data-theme='b' data-inline='true' data-mini='true' >일자별 수행수</a>
	</div>	
	<hr>
	<div id='app-info' class='ui-grid-a'>
		<div class='ui-block-a'>광고 키</div>
		<div class='ui-block-b'>
			<t3 style='line-height: 48px'><?=$row['app_key']?></t3>
		</div>
		<div class='ui-block-a'>광고 상태</div>
		<div class='ui-block-b'>
			<div style='float:left; display:inline-block'>
				<fieldset id="app-active" class='field-set' data-role="controlgroup" data-type="horizontal" data-mini=true init-value="<?=$row['is_active']?>" data-theme='a'>
			        <input name="app-active" id="app-active-Y" value="Y" type="radio" />
			        <label for="app-active-Y">적립 가능</label>
			        <input name="app-active" id="app-active-N" value="N" type="radio" />
			        <label for="app-active-N">적립 불가</label>
			    </fieldset>		
			</div>
		    <div style='float:left; display:inline-block; padding-top:5px; padding-left:10px'>
			    <a href='#' onclick='<?=$js_page_id?>.action.on_btn_save_activestatus()' data-theme='b' data-role='button' data-mini='true' data-inline='true'>상태 적용</a>			
			</div>
			<div style='clear:both'></div>
		</div>
		<div class='ui-block-a'>플랫폼</div>
		<div class='ui-block-b'>
			<fieldset id="app-platform" class='td-2-item' data-role="controlgroup" data-type="horizontal" data-mini=true init-value="A" readonly >
		        <input name="app-platform" id="app-platform-android" value="A" type="radio" />
		        <label for="app-platform-android">Android App</label>
		    </fieldset>									
		</div>
		<div class='ui-block-a' style='height:50px'>실행 타입</div>
		<div class='ui-block-b' style='height:50px; padding-top:3px'>
        	<div data-role="fieldcontain" style='padding: 0px 0px; border: 0; margin: 0'>
				<select name="app-type" id="app-type" onchange="<?=$js_page_id?>.action.on_change_app_type()" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c'>
					<option value="I" <?=$row['app_exec_type'] == 'I' ? 'selected' : ''?>>설치형</option>
					<option value="E" <?=$row['app_exec_type'] == 'E' ? 'selected' : ''?>>실행형</option>
					<option value="S" <?=$row['app_exec_type'] == 'S' ? 'selected' : ''?>>검색설치형</option>
				</select>
        	</div>
		</div>
		<div class='ui-block-a'>앱 이름</div>
		<div class='ui-block-b'>
			<div style='width:300px; display: inline-block; height: 20px; padding-top: 5px'>
				<input type="text" id="app-title" name="app-title" value='<?=addslashes($row['app_title'])?>'/>
			</div>
		</div>
		<div class='ui-block-a'>Package ID</div>
		<div class='ui-block-b'>
			<div style='width:450px; display: inline-block; height: 20px; padding-top: 5px'>
				
				<div style='float:left; width:330px'>
					<input type="text" id="app-packageid" name="app-packageid" value='<?=addslashes($row['app_packageid'])?>' />
				</div>
				<? if ($row['app_packageid']) { ?>
				<a data-role='button' data-inline='true' data-mini='true' href='https://play.google.com/store/apps/details?id=<?=$row['app_packageid']?>' target=_blank style='float:right'>구글확인</a>
				<? } ?>
				<div style='clear:both'></div>
				
			</div>
		</div>
		<div class='ui-block-a app-keyword-wrapper' style='height: 55px'>검색 키워드</div>
		<div class='ui-block-b app-keyword-wrapper' style='height: 55px'>
			<div style='width:300px; display: block; height: 20px; padding-top: 5px'>
				<input type="text" id="app-keyword" name="app-keyword" value='<?=addslashes($row['app_keyword'])?>' />
			</div>
			<br>
			(리뷰형의 경우 앱에 연령제한이 있는 꼭 입력)
		</div>		
		<div class='ui-block-a' style='height: 55px'>마켓 링크</div>
		<div class='ui-block-b' style='height: 55px'>
			<div style='width:300px; display: block; height: 20px; padding-top: 5px'>
				<input type="text" id="app-execurl" name="app-execurl" value='<?=addslashes($row['app_execurl'])?>' />
			</div>
			<br>
			(고객이 요청한 URL을 꼭 경우해야하는 경우에만 사용 - 사용시 검색키워드 사용불가)
		</div>	
		<div class='ui-block-a' style='height: 100px'>아이콘</div>
		<div class='ui-block-b' style='height: 100px'>
			<div style='width:400px; display: inline-block; height: 20px; padding-top: 5px'>
				<div style='width:80px height: 80px; float: left;'>
					<div style='border: 1px solid #ddd'>
						<img src="<?=$row['app_iconurl']?>" id='img-app-icon' width=80px />
					</div>
				</div>
				<div id='file-upload-div' style='width:300px; float:left; margin-left: 10px; '>
					<input type=file id="upload-image-file" value='파일 업로드' />
					<input type=hidden id="app-image-url" value="<?=$row['app_iconurl']?>" />
				</div>
				<div style='clear:both'></div>
			</div>
		</div>
		<div class='ui-block-a' style='height:100px'>참여 설명</div>
		<div class='ui-block-b' style='height:100px' id='app-exec-desc-wrapper'>
			<div style='width:400px; display: inline-block; padding-top: 5px'>
				<textarea id="app-exec-desc" name="app-exec-desc"><?=$row['app_exec_desc']?></textarea>
			</div>
		</div>
		<div class='ui-block-a' style='height:300px'>광고 설명</div>
		<div class='ui-block-b' style='height:300px' id='app-content-wrapper'>
			<div style='width:400px; display: inline-block; padding-top: 5px'>
				<textarea id="app-content" name="app-content"><?=$row['app_content']?></textarea>
			</div>
		</div>
		<div class='ui-block-a required'>광고원가</div>
		<div class='ui-block-b required'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-merchant-fee" name="app-merchant-fee" value='<?=number_format($row['app_merchant_fee'])?>'/>
			</div>
			<div style='float:left; padding: 15px 10px'>원</div>
			<div style='clear:both'></div>
		</div>
		<div class='ui-block-a required'>총 실행 수</div>
		<div class='ui-block-b required'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-exec-total-cnt" name="app-exec-total-cnt"  init-value='<?=number_format($row['exec_tot_max_cnt'])?>' />
			</div>
			<div style='float:left; padding: 15px 10px'>회</div>
			<div style='clear:both'></div>
		</div>
		
		<div class='ui-block-a'>광고종료</div>
		<div class='ui-block-b'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 2px; float:left'>
				<input type="text" data-role="date" class='td-2-item' name="app-exec-edate" id="app-exec-edate" data-clear-btn=true placeholder='기간없음' value="<?=str_replace('/','-',$row['exec_edate'])?>" />
			</div>
			<div style='float:left; padding: 15px 10px'> 까지 (설정하지 않으면 기간제한 없음)</div>
			<div style='clear:both'></div>
		</div>
		<div class='ui-block-a'>실행시간</div>
		<div class='ui-block-b'>
			<div style='width:60px; display: inline-block; height: 20px; padding-top: 2px; float:left'>
				<div data-role="fieldcontain" style='padding: 0px 0px; border: 0; margin: 0'>
				<select name="app-exec-stime" id="app-exec-stime" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c' init-value='<?=$row['exec_stime'] ? intval(substr($row['exec_stime'], 0, 2)) : 0?>'>
					<?
						for ($i=0; $i < 25; $i++) {
							echo "<option value='{$i}' {$selected}>{$i}</option>\n";
						}
					?>
				</select>
        		</div>
			</div>
			<div style='float:left; padding: 15px 10px'>시 부터 </div>
			<div style='width:60px; display: inline-block; height: 20px; padding-top: 2px; float:left'>
				<div data-role="fieldcontain" style='padding: 0px 0px; border: 0; margin: 0'>
				<select name="app-exec-etime" id="app-exec-etime" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c' init-value='<?=$row['exec_etime'] ? intval(substr($row['exec_etime'], 0, 2)) : 24?>'>
					<?
						for ($i=0; $i < 25; $i++) {
							echo "<option value='{$i}' {$selected}>{$i}</option>\n";
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
				<fieldset id="app-sex" class='td-2-item' data-role="controlgroup" data-type="horizontal" data-mini=true init-value="<?=ifempty($row['app_gender'], 'A')?>">
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
				<input type="number" class='td-2-item' name="app-agefrom" id="app-agefrom" data-clear-btn=true value="<?=$row['app_agefrom']?>" />
			</div>
			<div style='float:left; padding: 14px 10px'>세 부터 </div>
			<div style='width:90px; display: inline-block; height: 20px; padding-top: 6px; float:left'>
				<input type="number" class='td-2-item' name="app-ageto" id="app-ageto" data-clear-btn=true value="<?=$row['app_ageto']?>" />
			</div>
			<div style='float:left; padding: 14px 10px'>세 까지 </div>
			<div style='clear:both'></div>
		</div>

		<div class='ui-block-a'>시간 최대 실행</div>
		<div class='ui-block-b'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-exec-hourly-cnt" name="app-exec-hourly-cnt" data-clear-btn=true init-value='<?=number_format(ifempty($row['exec_hour_max_cnt'], 100000000))?>' />
			</div>
			<div style='float:left; padding: 15px 10px'>회</div>
			<div style='clear:both'></div>
		</div>
				
		<div class='ui-block-a'>일일 최대 실행</div>
		<div class='ui-block-b'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-exec-daily-cnt" name="app-exec-daily-cnt" data-clear-btn=true init-value='<?=number_format(ifempty($row['exec_day_max_cnt'], 100000000))?>' />
			</div>
			<div style='float:left; padding: 15px 10px'>회</div>
			<div style='clear:both'></div>
		</div>

		
		<div class='ui-block-a'>매체사 레벨</div>
		<div class='ui-block-b'>
			<div style='padding-top: 2px'>
	        	<div style='width:160px; display: inline-block; float:left'>
					<select name="app-publisher-level" id="app-publisher-level" init-value='<?=ifempty($row['publisher_level'], '9')?>'>
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
					<input type="text" data-role="date" class='td-2-item' name="level-1-active-date" id="level-1-active-date" data-clear-btn=true value="<?=admin_date("Y-m-d", strtotime($row['level_1_active_date']))?>" />
				</div>
				<div data-role="fieldcontain" style='display: inline-block; padding-top: 2px; border: 0; margin: 0'>
					<select name="level-1-active-time" id="level-1-active-time" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c' init-value='<?=admin_date("H", strtotime($row['level_1_active_date']))?>'>
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
					<input type="text" data-role="date" class='td-2-item' name="level-2-active-date" id="level-2-active-date" data-clear-btn=true value="<?=admin_date("Y-m-d", strtotime($row['level_2_active_date']))?>" />
				</div>
				<div data-role="fieldcontain" style='display: inline-block; padding-top: 2px; border: 0; margin: 0'>
					<select name="level-2-active-time" id="level-2-active-time" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c' init-value='<?=admin_date("H", strtotime($row['level_2_active_date']))?>'>
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
					<input type="text" data-role="date" class='td-2-item' name="level-3-active-date" id="level-3-active-date" data-clear-btn=true value="<?=admin_date("Y-m-d", strtotime($row['level_3_active_date']))?>" />
				</div>
				<div data-role="fieldcontain" style='display: inline-block; padding-top: 2px; border: 0; margin: 0'>
					<select name="level-3-active-time" id="level-3-active-time" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c' init-value='<?=admin_date("H", strtotime($row['level_3_active_date']))?>'>
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
					<input type="text" data-role="date" class='td-2-item' name="level-4-active-date" id="level-4-active-date" data-clear-btn=true value="<?=admin_date("Y-m-d", strtotime($row['level_4_active_date']))?>" />
				</div>
				<div data-role="fieldcontain" style='display: inline-block; padding-top: 2px; border: 0; margin: 0'>
					<select name="level-4-active-time" id="level-4-active-time" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c' init-value='<?=admin_date("H", strtotime($row['level_4_active_date']))?>'>
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
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_modifycampaign()' data-role='button' data-theme='b' data-inline='true' data-mini='true' >변경사항 적용하기</a>
		<a href='?id=campaign-by-appkey-list&appkey=<?=urlencode($row['app_key'])?>' data-theme='b' data-role='button' data-mini='true' data-inline='true'>참가자</a>
		
		<a href='?id=campaign-app-exec-view&appkey=<?=urlencode($row['app_key'])?>' data-role='button' data-theme='b' data-inline='true' data-mini='true' >일자별 수행수</a>
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
			on_change_app_type: function()
			{
				_$(".app-keyword-wrapper").hide();
				if (_$("#app-type").val() == 'S') {
					_$(".app-keyword-wrapper").show();
				}
			},
			on_btn_modifycampaign: function()
			{
				var app_type = _$("#app-type").val();

				// 검색형인 경우 키워드,마켓링크 모두 사용 불가
				if (util.in_array(app_type, ['S'])) {
					if (_$("#app-execurl").val() && _$("#app-keyword").val()) {
						alert('[마켓링크]와 [검색 키워드] 모두 설정해서 사용할 수 없습니다.\n두개 중 하나만 입력하세요');
						return;
					}
				}
				
				// 검색형이 아닌 경우 Keyword Clear
				if (!util.in_array(app_type, ['S'])) {
					_$("#app-keyword").val("");
				}
				
				var ar_param = {
					'mcode' : '<?=$mcode?>',
					'appkey' : '<?=$appkey?>',
					'appplatform' : util.get_item_value(_$("#app-platform")),
					'apptype' : _$("#app-type").val(),
					'apppackageid' : _$("#app-packageid").val(),
					'appkeyword' : _$("#app-keyword").val(),
					'appexecurl' : _$("#app-execurl").val(),
					'apptitle' : _$("#app-title").val(),
					'appimageurl' : _$("#app-image-url").val(),
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
					'appexechourlycnt': util.intval(_$("#app-exec-hourly-cnt").val()),
					'appexecdailycnt' : util.intval(_$("#app-exec-daily-cnt").val()),
					'appexectotalcnt' : util.intval(_$("#app-exec-total-cnt").val()),
					
					'apppublisherlevel': util.get_item_value(_$("#app-publisher-level")),
					
					'level1activedate': _$("#level-1-active-date").val() ? _$("#level-1-active-date").val() + " " + util.get_item_value(_$("#level-1-active-time")) : "",
					'level2activedate': _$("#level-2-active-date").val() ? _$("#level-2-active-date").val() + " " + util.get_item_value(_$("#level-2-active-time")) : "",
					'level3activedate': _$("#level-3-active-date").val() ? _$("#level-3-active-date").val() + " " + util.get_item_value(_$("#level-3-active-time")) : "",
					'level4activedate': _$("#level-4-active-date").val() ? _$("#level-4-active-date").val() + " " + util.get_item_value(_$("#level-4-active-time")) : ""
				};
				alert(util.var_dump(ar_param));
				util.post(get_ajax_url('admin-campaign-app-modify'), ar_param, function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						util.Alert('알림', '수정되었습니다.', function() {
							window.location.reload();
						});	
					} else util.Alert(js_data['msg']);
				});

			},
			on_btn_save_activestatus: function() {
				var ar_param = {
					'mcode' : '<?=$mcode?>',
					'appkey' : '<?=$appkey?>',
					'isactive': util.get_item_value(_$("#app-active"))
				};
				
				util.post(get_ajax_url('admin-campaign-set-active'), ar_param, function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						util.Alert('알림', '수정되었습니다.', function() {
							window.location.reload();
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
