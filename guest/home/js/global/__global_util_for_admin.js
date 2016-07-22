var g_admin_util = 
{
	// set_url_param("<?=$_SERVER['REQUEST_URI']?>", "day", $(this).val())
	set_url_param: function (url, param_key, param_value) 
	{
		if (!param_value) return g_admin_util.del_url_param(url, param_key);
		var param_string = encodeURIComponent(param_value);
		var re = new RegExp("([?&]"+param_key+"=)([^&]*)");
		if (url.match(re)) url = url.replace(re, '$1' + param_string);
			else if (url.match(/\?/)) url += "&"+param_key+"="+param_string;
				else url += "?"+param_key+"="+param_string;
		return url;
	},
	// del_url_param("<?=$_SERVER['REQUEST_URI']?>", "day")
	del_url_param: function (url, param_key) 
	{
		var re = new RegExp("([&]?"+param_key+"=)([^&]*)(&|$)");
		if (url.match(re)) url = url.replace(re, '');
		return url;
	},
	// set_url_params(document.location.href, ['a','a-val','b','b-val'])
	url_add_params: function(url, ar_data) {
		for (var i = 0 ; i < ar_data.length / 2; i++) {
			url = g_admin_util.set_url_param(url, ar_data[i*2], ar_data[i*2+1]);			
		}
		return url;
	},
	
	strip_tags: function(input, allowed) {
	  allowed = (((allowed || '') + '')
	      .toLowerCase()
	      .match(/<[a-z][a-z0-9]*>/g) || [])
	    .join('') // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
	  var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
	    commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi
	  return input.replace(commentsAndPhpTags, '')
	    .replace(tags, function ($0, $1) {
	      return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : ''
	    })
	},

};

String.prototype.set_url_param = function(param_key, param_value) {
	return g_admin_util.set_url_param(this, param_key, param_value);
};
String.prototype.del_url_param = function(param_key) {
	return g_admin_util.del_url_param(this, param_key);
};