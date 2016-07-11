var g_admin_util = 
{
	// set_url_param("<?=$_SERVER['REQUEST_URI']?>", "day", $(this).val())
	set_url_param: function (url, param_key, param_value) 
	{
		param_string = encodeURIComponent(param_value);
		var re = new RegExp("([?&]"+param_key+"=)([^&]*)");
		if (url.match(re)) url = url.replace(re, '$1' + param_string);
			else if (url.match(/\?/)) url += "&"+param_key+"="+param_string;
				else url += "?"+param_key+"="+param_string;
		return url;
	},
	url_add_params: function(url, ar_data) {
		for (var i = 0 ; i < ar_data.length / 2; i++) {
			url = util.url_add_param(url, ar_data[i*2], ar_data[i*2+1]);			
		}
		return url;
	},

};