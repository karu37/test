function getCurrentTime() {
    return toTimeString(new Date());
 }
 
function toTimeString(date) { //formatTime(date)
    var year  = date.getFullYear();
    var month = date.getMonth() + 1; // 1월=0,12월=11이므로 1 더함
   var day   = date.getDate();
    var hour  = date.getHours();
    var min   = date.getMinutes();
 
   if (("" + month).length == 1) { month = "0" + month; }
    if (("" + day).length   == 1) { day   = "0" + day;   }
    if (("" + hour).length  == 1) { hour  = "0" + hour;  }
    if (("" + min).length   == 1) { min   = "0" + min;   }
 
   return ("" + year + month + day + hour + min)
 }

function popchk(str1,str2,str3,str4)
{
	var str1; 
	var str2; 
	var str3; 
	var str4; 
	var iMyWidth; 
	var iMyHeight; 

	iMyWidth = (window.screen.width/2) - (str2/2); 
	iMyHeight = (window.screen.height/2) - (str3/2); 

	var openwin = window.open(str1,"window","status=no,width="+str2+",height="+str3+",resizable=no,scrollbars="+str4+",left=" + iMyWidth + ",top=" + iMyHeight + ""); 
	openwin.focus(); 
}

function isEmpty(data)
{
	for(var i = 0 ; i < data.length ; i++)
	{
		if(data.substring(i, i+1) != " ")
		return false;
	}
	return true;
}



function getCookie( name )
{
	var nameOfCookie = name + "=";
	var x = 0;
	while ( x <= document.cookie.length )
	{
		var y = (x+nameOfCookie.length);
		if ( document.cookie.substring( x, y ) == nameOfCookie ) {
			if ( (endOfCookie=document.cookie.indexOf( ";", y )) == -1 )
				endOfCookie = document.cookie.length;
			return unescape( document.cookie.substring( y, endOfCookie ) );
		}
		x = document.cookie.indexOf( " ", x ) + 1;
		if ( x == 0 )
			break;
	}
	return "";
}

//엔터
function enterchk()
{
    if(event.keyCode ==13) 
    { 
    	doLogin();
	}
}
