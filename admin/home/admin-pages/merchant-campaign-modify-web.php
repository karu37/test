<div>
	<t3 style='height:40px; padding-top:20px'>광고 정보 수정</t3>
	<hr>
	<style>
		#app-info	.ui-block-a	{height: 45px; line-height:45px; padding-left: 10px; width:100px; border-bottom: 1px solid #ddd; font-weight: bold}
		#app-info	.ui-block-b	{height: 45px; width:500px; border-bottom: 1px solid #ddd}
		
		#app-content	{height: 80px !important; overflow-y: scroll !important}
		#app-exec-desc 	{height: 180px !important; overflow-y: scroll !important}

		.required			{background-color:lightyellow}
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
			<fieldset id="app-platform" class='td-2-item' data-role="controlgroup" data-type="horizontal" data-mini=true init-value="W" readonly >
		        <input name="app-platform" id="app-platform-android" value="W" type="radio" />
		        <label for="app-platform-android">WEB</label>
		    </fieldset>									
		</div>
		<div class='ui-block-a' style='height:50px'>실행 타입</div>
		<div class='ui-block-b' style='height:50px; padding-top:3px'>
        	<div data-role="fieldcontain" style='padding: 0px 0px; border: 0; margin: 0'>
				<select name="app-type" id="app-type" onchange="<?=$js_page_id?>.action.on_change_app_type()" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c'>
					<option value="F" <?=$row['app_exec_type'] == 'F' ? 'selected' : ''?>>페이스북 좋아요</option>
				</select>
        	</div>
		</div>
		
		<div class='ui-block-a'>홈 URL</div>
		<div class='ui-block-b'>
			<div style='width:300px; display: block; height: 20px; padding-top: 5px'>
				<form onsubmit="return <?=$js_page_id?>.action.on_btn_search_info()">
				<div style='width:230px; display: inline-block; height: 20px'>
					<input type="text" id="app-homeurl" name="app-homeurl" value='<?=$row['app_homeurl']?>'/>
				</div>
				<input type=submit data-role="button" data-theme='c' data-theme="b" data-inline='true' data-mini='true' style="margin-top: 15px; margin:0px 5px" value="검색" />
				</form>
				
			</div>
		</div>		
		<div class='ui-block-a'>실행 URL</div>
		<div class='ui-block-b'>
			<div style='width:300px; display: block; height: 20px; padding-top: 5px'>
				<input type="text" id="app-execurl" name="app-execurl" value='<?=$row['app_execurl']?>' />
			</div>
		</div>		
		
		<div class='ui-block-a'>제목</div>
		<div class='ui-block-b'>
			<div style='width:300px; display: inline-block; height: 20px; padding-top: 5px'>
				<input type="text" id="app-title" name="app-title" value='<?=$row['app_title']?>' />
			</div>
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
		<div class='ui-block-a' style='height:100px'>광고 설명</div>
		<div class='ui-block-b' style='height:100px' id='app-content-wrapper'>
			<div style='width:400px; display: inline-block; padding-top: 5px'>
				<textarea id="app-content" name="app-content"><?=$row['app_content']?></textarea>
			</div>
		</div>
		<div class='ui-block-a' style='height:200px'>적립 방법</div>
		<div class='ui-block-b' style='height:200px' id='app-exec-desc-wrapper'>
			<div style='width:400px; display: inline-block; padding-top: 5px'>
				<textarea id="app-exec-desc" name="app-exec-desc"><?=$row['app_exec_desc']?></textarea>
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
				<input type="text" id="app-exec-total-cnt" name="app-exec-total-cnt" init-value='<?=number_format($row['exec_tot_max_cnt'])?>' />
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
				<input type="text" id="app-exec-hourly-cnt" name="app-exec-hourly-cnt" data-clear-btn=true placeholder='제한 없음' init-value='<?=number_format(ifempty($row['exec_hour_max_cnt'], ""))?>' />
			</div>
			<div style='float:left; padding: 15px 10px'>회</div>
			<div style='clear:both'></div>
		</div>
				
		<div class='ui-block-a'>일일 최대 실행</div>
		<div class='ui-block-b'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-exec-daily-cnt" name="app-exec-daily-cnt" data-clear-btn=true placeholder='제한 없음' init-value='<?=number_format(ifempty($row['exec_day_max_cnt'], ""))?>' />
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
					<input type="text" data-role="date" class='td-2-item' name="level-1-active-date" id="level-1-active-date" data-clear-btn=true placeholder='일정 없음' value="<?=admin_date("Y-m-d", strtotime($row['level_1_active_date']))?>" />
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
					<input type="text" data-role="date" class='td-2-item' name="level-2-active-date" id="level-2-active-date" data-clear-btn=true placeholder='일정 없음' value="<?=admin_date("Y-m-d", strtotime($row['level_2_active_date']))?>" />
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
					<input type="text" data-role="date" class='td-2-item' name="level-3-active-date" id="level-3-active-date" data-clear-btn=true placeholder='일정 없음' value="<?=admin_date("Y-m-d", strtotime($row['level_3_active_date']))?>" />
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
					<input type="text" data-role="date" class='td-2-item' name="level-4-active-date" id="level-4-active-date" data-clear-btn=true placeholder='일정 없음' value="<?=admin_date("Y-m-d", strtotime($row['level_4_active_date']))?>" />
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
				
				// $("#app-exec-desc").cleditor();
				// $("#app-content").cleditor();
				
				page.action.on_change_app_type();
			},
			on_change_app_type: function()
			{
			},
			on_btn_modifycampaign: function()
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
				
		util.set_event_for_input_number(_$("#app-exec-hourly-cnt"), '');
		util.set_event_for_input_number(_$("#app-exec-daily-cnt"), '');
		util.set_event_for_input_number(_$("#app-exec-total-cnt"), '');

	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
