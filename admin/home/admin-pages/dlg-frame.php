<div data-role="dialog" id="<?=$page_id?>" data-overlay-theme="a">
    <div data-role="main" id="content" style='text-align: center'>
    <iframe src="http://admin.aline-soft.kr/admin_index.php?id=dlgpage-merchantapp-config" id='frame_content' style='display:inline-block; min-width:100px; min-height: 100px' onload='<?=$js_page_id?>.action.onload_iframe()'></iframe>
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
				$.mobile.loading("show");
				_$("#frame_content").attr('src', 'http://admin.aline-soft.kr/admin_index.php?id=dlgpage-merchantapp-config');
			}	
		},
		action: {
			onload_iframe: function() {
				$.mobile.loading("hide");
				
		        var newheight = window.frames['frame_content'].contentWindow.document.body.scrollHeight;
		        var newwidth = window.frames['frame_content'].contentWindow.document.body.scrollWidth;
		        
			    document.getElementById('frame_content').height = (newheight) + "px";
			    document.getElementById('frame_content').width = (newwidth) + "px";
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
