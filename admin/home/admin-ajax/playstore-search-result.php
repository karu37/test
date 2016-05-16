<?
	$sz_query = $_REQUEST['q'];
	$sz_packageid = $_REQUEST['package_id'];
	$sz_pageid = $_REQUEST['pageid'];

	$en_query = urlencode($sz_query);
	$en_packageid = urlencode($sz_packageid);
	
	if (!$sz_packageid) 
	{
		$search_url = "http://heartoffice.iptime.org:12680/google-store/search.php?q={$en_query}";
		$req_data = admin_get_url($search_url);
		$js_data = json_decode($req_data, true);

		$cnt = count($js_data);
		for ($i = 0; $i < $cnt; $i++) 
		{
			$js_unit = $js_data[$i];
			$sh_title = str_replace("'", "", trim($js_unit['title']));
			$sh_title = str_replace('"', "", $sh_title);
			$sh_image = $js_unit['img'];
			$sz_data .= 
"<li onclick='{$sz_pageid}.action.on_select_google_campaign(\"{$js_unit['package_id']}\", \"{$sh_title}\", \"{$sh_image}\")' style='cursor:pointer'>
	<img src='data:image/png;base64,{$js_unit['img']}' style='float:left; margin-right:10px' width=40px/>
	<div style='text-align:left; padding-top:3px'>
		{$js_unit['title']}<br>
		{$js_unit['package_id']}<br>
	</div>
	<div style='clear:both'></div>
</li>";

//		sub_title : {$js_unit['sub_title']}<br>
//		desc : {$js_unit['desc']}

		}
	
		$ar_data['data'] = $sz_data;
		return_die(true, $ar_data);
	}
	else
	{
		$detail_url = "http://heartoffice.iptime.org:12680/google-store/detail.php?id={$en_packageid}";
		$sz_data = admin_get_url($search_url);
		$ar_data['info'] = json_decode($sz_data, true);
		return_die(true, $ar_data);
	}	
	
?>
