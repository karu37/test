<?
	// getting execution count and total count from live stat table
	$db_appkey = mysql_real_escape_string($appkey);
	
	$sql = "SELECT DATE(a.exec_time) reg_day, IF(DATE(a.exec_time)=CURRENT_DATE, a.exec_day_cnt, 0) cnt, a.exec_tot_cnt totcnt
				FROM al_app_exec_stat_t a 
				WHERE a.app_key = '{$db_appkey}'";
 	$result = mysql_query($sql, $conn);
 	$row_today = @mysql_fetch_assoc(mysql_query($sql, $conn));

?>
<div>
	<t3 style='height:40px; padding-top:20px'>광고 정보 수정</t3>
	<hr>
	<style>
		#app-info	.ui-block-a	{height: 45px; line-height:45px; padding-left: 10px; width:100px; border-bottom: 1px solid #ddd; font-weight: bold}
		#app-info	.ui-block-b	{height: 45px; width:500px; border-bottom: 1px solid #ddd}
		
		#app-content	{height: 80px !important; overflow-y: scroll !important}
		#app-exec-desc 	{height: 180px !important; overflow-y: scroll !important}

		.app-interactive		{background-color: #ddffdd}
		.required			{background-color:lightyellow}
		.app-keyword-wrapper	{background-color: lightgreen}
		
		.btn-small-wrapper a	{font-size: 12px}
		.btn-wrapper			{width: 200px}
		.btn-wrapper a			{padding:8px 5px; margin: 5px 2px 2px -1px; box-shadow:none}
		
	</style>
	<div style='padding: 10px'>
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_modifycampaign()' data-role='button' data-theme='b' data-inline='true' data-mini='true' >변경사항 적용하기</a>
		<a href='?id=merchant-campaign-user-list&mcode=<?=$row['mcode']?>&appkey=<?=urlencode($row['app_key'])?>' data-theme='b' data-role='button' data-mini='true' data-inline='true'>참가자</a>
		
		<a href='?id=merchant-campaign-exec-view&mcode=<?=$row['mcode']?>&appkey=<?=urlencode($row['app_key'])?>' data-role='button' data-theme='b' data-inline='true' data-mini='true' >일자별 수행수</a>
		<a href='#' onclick='goPage("dlg-upload-app-adid", null, {appkey: "<?=$row['app_key']?>"})' data-theme='b' data-role='button' data-mini='true' data-inline='true'>ADID 등록(중복방지)</a>
	</div>	
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script type="text/javascript">
		google.charts.load('current', {packages:["corechart"]});
	</script>
	<hr>
	<div id='app-info' class='ui-grid-a'>
		<div class='ui-block-a'>ALINE 광고키</div>
		<div class='ui-block-b'>
			<t3 style='line-height: 43px'><?=$row['app_key']?></t3>
		</div>
		<div class='ui-block-a'>광고주 광고키</div>
		<div class='ui-block-b'>
			<t3 style='line-height: 43px'><?=$row['lib'] == 'LOCAL' ? "<span style='font-size:14px; color:gray'>없음 (ALINE에서 등록)</span>" : $row['mkey']?></t3>
		</div>
		<div class='ui-block-a'>광고주 명</div>
		<div class='ui-block-b'>
			<t3 style='line-height: 43px'><?=$row['m_name']?></t3>
		</div>
		<div class='ui-block-a app-interactive'>적립 상태</div>
		<div class='ui-block-b app-interactive'>
			<style>
				.icon-active-Y	{border: 1px solid blue; padding: 4px 10px; position: inline-block; border-radius: 0.4em; background: blue; color: yellow; font-size: 16px; font-weight: normal;}
				.icon-active-N	{border: 1px solid orange; padding: 4px 10px; position: inline-block; border-radius: 0.4em; background: red; color: white; font-size: 16px; font-weight: normal;}
				.active-Y .icon-active-N 	{display: none}
				.active-N .icon-active-Y 	{display: none}
			</style>
			<table><tr><td>
					<div class='active-<?=$row['is_active']?>'>
						<div class='icon-active-Y'>적립 가능</div>
						<div class='icon-active-N'>적립 종료</div>
					</div>
				</td><td>
				<? if ($row['lib'] == 'LOCAL') { ?>
				<style>
					.active-Y .set-active-Y	{display: none}
					.active-N .set-active-N	{display: none}
				</style>
				<div class='active-<?=$row['is_active']?>'>
					<a href='#' onclick='<?=$js_page_id?>.action.on_btn_set_local_active("Y")' class='set-active-Y' data-role='button' data-mini='true' data-inline='true'>적립 가능 상태로 변경</a>
					<a href='#' onclick='<?=$js_page_id?>.action.on_btn_set_local_active("N")' class='set-active-N' data-role='button' data-mini='true' data-inline='true'>적립 종료 상태로 변경</a>
				</div>
				<? } ?>
			</td><td>

			</td></tr></table>
		</div>
		
		<div class='ui-block-a app-interactive'>광고상태</div>
		<div class='ui-block-b app-interactive'>
			<?
				// 현재의 Merchant의 active상태 : Y / T / N 만 가능함.					
				$ar_btn_theme = array('a','a','a','a');
				if ($row['is_mactive'] == 'Y') $ar_btn_theme[0] = 'b';
				else if ($row['is_mactive'] == 'N') $ar_btn_theme[1] = 'b';
				else if ($row['is_mactive'] == 'D') $ar_btn_theme[2] = 'b';
				else if ($row['is_mactive'] == 'T') $ar_btn_theme[3] = 'b';			
			
			?>			
			<div class='btn-small-wrapper btn-wrapper'>
				<a class='btn-mactive btn-Y' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_mactive("Y")' data-theme='<?=$ar_btn_theme[0]?>' data-role='button' data-mini='true' data-inline='true'>정상</a>
				<a class='btn-mactive btn-N' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_mactive("N")' data-theme='<?=$ar_btn_theme[1]?>' data-role='button' data-mini='true' data-inline='true'>중지</a>
				<a class='btn-mactive btn-D' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_mactive("D")' data-theme='<?=$ar_btn_theme[2]?>' data-role='button' data-mini='true' data-inline='true'>삭제</a>
				<a class='btn-mactive btn-T' href='#' onclick='<?=$js_page_id?>.action.on_btn_set_mactive("T")' data-theme='<?=$ar_btn_theme[3]?>' data-role='button' data-mini='true' data-inline='true'>개발</a>
			</div>
			
		</div>
		
		<div class='ui-block-a'>플랫폼</div>
		<div class='ui-block-b'>
			<fieldset id="app-platform" class='td-2-item' data-role="controlgroup" data-type="horizontal" data-mini=true init-value="W" readonly >
		        <input name="app-platform" id="app-platform-android" value="W" type="radio" />
		        <label for="app-platform-android">기타</label>
		    </fieldset>									
		</div>
		<div class='ui-block-a' style='height:50px'>실행 타입</div>
		<div class='ui-block-b' style='height:50px; padding-top:3px'>
        	<div data-role="fieldcontain" style='padding: 0px 0px; border: 0; margin: 0'>
				<select name="app-type" id="app-type" onchange="<?=$js_page_id?>.action.on_change_app_type()" data-inline='true' data-mini='true' data-native-menu="true" data-theme='c'>
					<option value="W" <?=$row['app_exec_type'] == 'W' ? 'selected' : ''?>>CPA (가입/신청 수행형)</option>
				</select>
        	</div>
		</div>
		
		<div class='ui-block-a'>홈 URL</div>
		<div class='ui-block-b'>
			<div style='width:400px; display: block; height: 20px; padding-top: 5px'>
				<form onsubmit="return <?=$js_page_id?>.action.on_btn_search_info()">
				<div style='width:330px; display: inline-block; height: 20px'>
					<input type="text" id="app-homeurl" name="app-homeurl" value='<?=$row['app_homeurl']?>'/>
				</div>
				<input type=submit data-role="button" data-theme='c' data-theme="b" data-inline='true' data-mini='true' style="margin-top: 15px; margin:0px 5px" value="검색" />
				</form>
				
			</div>
		</div>
		<div class='ui-block-a'>Package ID</div>
		<div class='ui-block-b'>
			<div style='width:420px; display: inline-block; height: 20px; padding-top: 5px'>
				
				<div style='float:left; width:330px'>
					<input type="text" id="app-packageid" name="app-packageid" value='<?=addslashes($row['app_packageid'])?>' />
				</div>
				<? if ($row['app_packageid']) { ?>
				<a data-role='button' data-inline='true' data-mini='true' href='https://play.google.com/store/apps/details?id=<?=$row['app_packageid']?>' target=_blank style='float:right'>구글확인</a>
				<? } ?>
				<div style='clear:both'></div>
				
			</div>
		</div>
		<div class='ui-block-a'>실행 URL</div>
		<div class='ui-block-b'>
			<div style='width:400px; display: block; height: 20px; padding-top: 5px'>
				<input type="text" id="app-execurl" name="app-execurl" value='<?=$row['app_execurl']?>' />
			</div>
		</div>		
		
		<div class='ui-block-a'>적립URL 메모</div>
		<div class='ui-block-b'>
			<div style='width:400px; display: block; height: 20px; padding-top: 5px'>
				<input type="text" id="app-etc" name="app-etc" value='<?=$row['app_etc']?>' />
			</div>
		</div>		
		
		<div class='ui-block-a'>제목</div>
		<div class='ui-block-b'>
			<div style='width:400px; display: inline-block; height: 20px; padding-top: 5px'>
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
		<div class='ui-block-a required'>매출원가</div>
		<div class='ui-block-b required'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-merchant-fee" name="app-merchant-fee" value='<?=number_format($row['app_merchant_fee'])?>'/>
			</div>
			<div style='float:left; padding: 15px 10px'>원 - 판매시 실제 수입으로 들어오는 금액</div>
			<div style='clear:both'></div>
		</div>
		<div class='ui-block-a required'>매체원가</div>
		<div class='ui-block-b required'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-tag-price" name="app-tag-price" value='<?=number_format($row['app_tag_price'])?>'/>
			</div>
			<div style='float:left; padding: 15px 10px'>원 - 매체에 공급하는 원가 (매체 제공 금액은 이 가격으로 계산)</div>
			<div style='clear:both'></div>
		</div>
		<div class='ui-block-a required'>총 실행 수</div>
		<div class='ui-block-b required'>
			<div style='width:100px; display: inline-block; height: 20px; padding-top: 5px; float:left'>
				<input type="text" id="app-exec-total-cnt" name="app-exec-total-cnt" init-value='<?=number_format($row['exec_tot_max_cnt'])?>' />
			</div>
			<div style='float:left; padding: 10px 10px'>회 <b3 style='padding-left:20px'>총 적립수 : <?=number_format($row_today['totcnt'])?> 회</b3></div>
			<div style='clear:both'></div>
		</div>
		<div class='ui-block-a'>주말수행</div>
		<div class='ui-block-b'>
			<div class='ui-block-a' style='width:200px; padding-left: 0px'>
				<fieldset id="app-exec-weekend" class='td-2-item' data-role="controlgroup" data-type="horizontal" data-mini=true init-value="<?=$row['exec_weekend']?>">
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
			<div style='float:left; padding: 10px 10px'>회 <b3 style='padding-left:20px'>금일 적립수 : <?=number_format($row_today['cnt'])?> 회</b3></div>
			<div style='clear:both'></div>
		</div>
		<div class='ui-block-a'>스케쥴 설정</div>
		<div class='ui-block-b'>
			<style>
				.app-scheduled	{border: 1px solid blue; padding: 4px 10px; position: inline-block; border-radius: 0.4em; background: blue; color: yellow; font-size: 16px; font-weight: bold; text-align:center;}
				.app-not-scheduled	{border: 1px solid #ddd; padding: 4px 10px; position: inline-block; border-radius: 0.4em; background: #eee; color: #888; font-size: 16px; font-weight: : bold; text-align:center;}
				.schedule-Y .app-not-scheduled 	{display: none}
				.schedule-N .app-scheduled 	{display: none}
			</style>
			<div class='schedule-<?=$row['schedule_cnt'] > 0 ? 'Y' : 'N'?>' style='width:100px; display: inline-block; float:left; padding-top:7px;'>
				<div class='app-scheduled'>스케쥴 됨</div>
				<div class='app-not-scheduled'>없음</div>
			</div>
			<div style='float:left; padding: 2px 10px'>
				<a href='#' onclick="goPage('dlg-campaign-schedule', null, {appkey:'<?=$row['app_key']?>'})" data-theme='b' data-role='button' data-inline='true' data-mini='true' target=_blank style='float:right'>스케쥴 설정</a>
			</div>
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
		<iframe id='home-page' src='about:blank' style='width:100%;height:1'></iframe>
	</div>
	<div style='padding-top: 20px'>
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_modifycampaign()' data-role='button' data-theme='b' data-inline='true' data-mini='true' >변경사항 적용하기</a>
		<a href='?id=merchant-campaign-user-list&mcode=<?=$row['mcode']?>&appkey=<?=urlencode($row['app_key'])?>' data-theme='b' data-role='button' data-mini='true' data-inline='true'>참가자</a>
		
		<a href='?id=merchant-campaign-exec-view&mcode=<?=$row['mcode']?>&appkey=<?=urlencode($row['app_key'])?>' data-role='button' data-theme='b' data-inline='true' data-mini='true' >일자별 수행수</a>
		<a href='#' onclick='goPage("dlg-upload-app-adid", null, {appkey: "<?=$row['app_key']?>"})' data-theme='b' data-role='button' data-mini='true' data-inline='true'>ADID 등록(중복방지)</a>
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
	
	* UserData가 전달되는 파라미터 이 첫번째 Subpath 값으로 설정함.
		리턴주는 주소 규칙이 http://www.도메인.co.kr/result.asp 를 등록하면
		실제 호출이 ==> http://www.도메인.co.kr/result.asp?<span style='color:red;font-weight: bold'>adkey</span>=??? 인 경우
		
		http://cb.aline-soft.kr/<span style='color:red;font-weight: bold'>adkey</span>/typesf/result.json 을 등록함
		실제 호출 http://cb.aline-soft.kr/<span style='color:red;font-weight: bold'>adkey</span>/typesf/result.json?<span style='color:red;font-weight: bold'>adkey</span>=??? 로 들어오도록 함.
	
	* 두번 째 라파미터는 리턴 타입에 따라 결정
		- typesf : 은 성공시에는 S 또는 실패시에는 F 를 페이지에 출력함
		- typejs : json 으로 결과 출력

		http://cb.aline-soft.kr/adkey/<span style='color:red;font-weight: bold'>typesf</span>/result.json 을 등록함
		실제 호출 http://cb.aline-soft.kr/adkey/<span style='color:red;font-weight: bold'>typesf</span>/result.json?adkey=??? 적립이 들어오면
			결과를  S 또는 F 로 리턴한다.

</pre>
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
				
				// alert(util.var_dump(ar_param));
				util.post(get_ajax_url('admin-campaign-app-modify'), ar_param, function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						util.Alert('알림', '수정되었습니다.', function() {
							window.location.reload();
						});	
					} else util.Alert(js_data['msg']);
				});

			},
			on_btn_set_local_active: function(new_status) {
				var ar_param = {
					'mcode' : '<?=$mcode?>',
					'appkey' : '<?=$appkey?>',
					'isactive': new_status
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
			
			on_btn_set_mactive: function(active) {
				
				var ar_merchant_active = {'Y':'정상', 'D':'삭제', 'T':'개발', 'N':'중지'};
				var ar_param = {mcode: '<?=$row["mcode"]?>', appkey: '<?=$row["app_key"]?>', isactive: active};
				util.request(get_ajax_url('admin-merchantapp-set-mactive', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						$('.btn-mactive.btn-Y').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-mactive.btn-N').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-mactive.btn-D').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-mactive.btn-T').removeClass('ui-btn-a ui-btn-b ui-btn-up-a ui-btn-up-b');
						$('.btn-mactive.btn-' + ar_param.isactive).addClass('ui-btn-b ui-btn-up-b').attr('data-theme', 'b');
						toast('저장되었습니다. (' + ar_merchant_active[ar_param.isactive] + ')');
					} else util.Alert(js_data['msg']);
				});
				
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
							
							_$("#app-packageid").val(ar_info[1]);
							_$("#app-title").val(title);
							_$("#app-content").val(ifempty(description,'') + _$("#app-content").val()).blur();
							_$("#home-page").attr('src', 'about:blank');
						}
					}
				}, 1000);

				return false;
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
