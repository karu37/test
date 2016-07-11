var g_ui = 
{
	toggle_class: function(obj, cls_name) {
		if ($(obj).hasClass(cls_name)) $(obj).removeClass(cls_name); else $(obj).addClass(cls_name);
	},
	toggle_child_checkbox: function(obj, cls_name) {
		var checkbox = $(obj).find("input[type='checkbox']");
		alert(checkbox);
	},
	
};

var toast=function(msg){
	$("<div class='ui-loader ui-overlay-shadow ui-body-e ui-corner-all'><h3>"+msg+"</h3></div>")
	.css({ display: "block", 
		opacity: 1, 
		position: "fixed",
		padding: "7px",
		"background-color": "white",
		"text-align": "center",
		width: "270px",
		left: ($(window).width() - 284)/2,
		top: $(window).height()/2 })
	.appendTo( $.mobile.pageContainer ).delay( 1000 )
	.fadeOut( 200, function(){
		$(this).remove();
	});
}