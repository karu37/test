(function($) {
    $.fn.longClick = function(callback, timeout) {
        var timer;
        timeout = timeout || 500;
        $(this).unbind('mousedown').unbind('mouseup');
        $(this).mousedown(function() {
            timer = setTimeout(function() { callback(); }, timeout);
            return false;
        });
        $(document).mouseup(function() {
            clearTimeout(timer);
            return false;
        });
    };
    $.fn.offlongClick = function() {
    	$(this).unbind('mousedown').unbind('mouseup');
    };    
})(jQuery);