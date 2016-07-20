String.prototype.repeat||(String.prototype.repeat=function(t){return t=Math.max(t||0,0),new Array(t+1).join(this.valueOf())}),String.prototype.startsWith||(String.prototype.startsWith=function(t,r){return r=Math.max(r||0,0),this.indexOf(t)==r}),String.prototype.endsWith||(String.prototype.endsWith=function(t,r){r=Math.max(r||0,0);var n=String(t),e=this.lastIndexOf(n);return e>=0&&e==this.length-n.length-r}),String.prototype.contains||(String.prototype.contains=function(t,r){return r=Math.max(r||0,0),-1!=this.indexOf(t)}),String.prototype.toArray||(String.prototype.toArray=function(){return this.split("")}),String.prototype.reverse||(String.prototype.reverse=function(){return this.split("").reverse().join("")}),String.validBrackets=function(t){if(!t)return!1;var r="''\"\"`'``",n="<>{}[]()%%||//\\\\",e="/**/<??><%%>(**)";return 2==t.length&&-1!=(r+n).indexOf(t)||4==t.length&&-1!=e.indexOf(t)},String.prototype.brace=String.prototype.bracketize=function(t){var r=this;if(!String.validBrackets(t))return r;var n=t.length/2;return t.substr(0,n)+r.toString()+t.substr(n)},String.prototype.unbrace=String.prototype.unbracketize=function(t){var r=this;if(!t)for(var n=r.length,e=2;e>=1;e--)if(t=r.substring(0,e)+r.substring(n-e),String.validBrackets(t))return r.substring(e,n-e);if(!String.validBrackets(t))return r;var i=t.length/2,e=r.indexOf(t.substr(0,i)),o=r.lastIndexOf(t.substr(i));return 0==e&&o==r.length-i&&(r=r.substring(e+i,o)),r},Number.prototype.radix=function(t,r,n){return this.toString(t).padding(-r,n)},Number.prototype.bin=function(t,r){return this.radix(2,t,r)},Number.prototype.oct=function(t,r){return this.radix(8,t,r)},Number.prototype.dec=function(t,r){return this.radix(10,t,r)},Number.prototype.hexl=function(t,r){return this.radix(16,t,r)},Number.prototype.hex=function(t,r){return this.radix(16,t,r).toUpperCase()},Number.prototype.human=function(t,r){for(var n=Math.abs(this),e=this,i="",o=arguments.callee.add(r),p=o.length-1;p>=0;p--)if(n>=o[p].d){e/=o[p].d,i=o[p].s;break}return e.toFixed(t)+i},Number.prototype.human.add=function(t,r,n){var e=t?"div2":"div10",i=Number.prototype.human[e]=Number.prototype.human[e]||[];return arguments.length<3?i:(i.push({s:r,d:Math.abs(n)}),i.sort(function(t,r){return t.d-r.d}),i)},Number.prototype.human.add(!0,"K",1024),Number.prototype.human.add(!0,"M",1<<20),Number.prototype.human.add(!0,"G",1<<30),Number.prototype.human.add(!0,"T",Math.pow(2,40)),Number.prototype.human.add(!1,"K",1e3),Number.prototype.human.add(!1,"M",1e6),Number.prototype.human.add(!1,"G",1e9),Number.prototype.human.add(!1,"T",1e12),Number.fromHuman=function(t,r){var n=String(t).match(/^([\-\+]?\d+\.?\d*)([A-Z])?$/);if(!n)return Number.NaN;if(!n[2])return+n[1];for(var e=Number.prototype.human.add(r),i=0;i<e.length;i++)if(e[i].s==n[2])return n[1]*e[i].d;return Number.NaN},String.prototype.trim||(String.prototype.trim=function(){return this.replace(/(^\s*)|(\s*$)/g,"")}),String.prototype.trimLeft||(String.prototype.trimLeft=function(){return this.replace(/(^\s*)/,"")}),String.prototype.trimRight||(String.prototype.trimRight=function(){return this.replace(/(\s*$)/g,"")}),String.prototype.dup=function(){var t=this.valueOf();return t+t},String.prototype.padding=function(t,r){var n=this.valueOf();if(Math.abs(t)<=n.length)return n;var e=Math.max(Math.abs(t)-this.length||0,0),i=Array(e+1).join(String(r||" ").charAt(0));return 0>t?i+n:n+i},String.prototype.padLeft=function(t,r){return this.padding(-Math.abs(t),r)},String.prototype.alignRight=String.prototype.padLeft,String.prototype.padRight=function(t,r){return this.padding(Math.abs(t),r)},String.prototype.format=function(){var t=arguments;return this.replace(/\{(\d+)\}/g,function(r,n){return void 0!==t[n]?t[n]:r})},String.prototype.alignLeft=String.prototype.padRight,String.prototype.sprintf=function(){var t,r,n=arguments,e=0;return this.replace(String.prototype.sprintf.re,function(){if("%%"==arguments[0])return"%";t=[];for(var i=0;i<arguments.length;i++)t[i]=arguments[i]||"";return t[3]=t[3].slice(-1)||" ",r=n[+t[1]?t[1]-1:e++],String.prototype.sprintf[t[6]](r,t)})},String.prototype.sprintf.re=/%%|%(?:(\d+)[\$#])?([+-])?('.|0| )?(\d*)(?:\.(\d+))?([bcdfosuxXhH])/g,String.prototype.sprintf.b=function(t,r){return Number(t).bin(r[2]+r[4],r[3])},String.prototype.sprintf.c=function(t,r){return String.fromCharCode(t).padding(r[2]+r[4],r[3])},String.prototype.sprintf.d=String.prototype.sprintf.u=function(t,r){return Number(t).dec(r[2]+r[4],r[3])},String.prototype.sprintf.f=function(t,r){var t=Number(t);return t=r[5]?t.toFixed(r[5]):r[4]?t.toExponential(r[4]):t.toExponential(),r[2]="-"==r[2]?"+":"-",t.padding(r[2]+r[4],r[3])},String.prototype.sprintf.o=function(t,r){return Number(t).oct(r[2]+r[4],r[3])},String.prototype.sprintf.s=function(t,r){return String(t).padding(r[2]+r[4],r[3])},String.prototype.sprintf.x=function(t,r){return Number(t).hexl(r[2]+r[4],r[3])},String.prototype.sprintf.X=function(t,r){return Number(t).hex(r[2]+r[4],r[3])},String.prototype.sprintf.h=function(t,r){var t=String.prototype.replace.call(t,/,/g,"");return r[2]="-"==r[2]?"+":"-",Number(t).human(r[5],!0).padding(r[2]+r[4],r[3])},String.prototype.sprintf.H=function(t,r){var t=String.prototype.replace.call(t,/,/g,"");return r[2]="-"==r[2]?"+":"-",Number(t).human(r[5],!1).padding(r[2]+r[4],r[3])},String.prototype.compile=function(){var t,r,n=(arguments,0),e=this.replace(/(\\|\")/g,"\\$1").replace(String.prototype.sprintf.re,function(){if("%%"==arguments[0])return"%";arguments.length=7,t=[];for(var e=0;e<arguments.length;e++)t[e]=arguments[e]||"";return t[3]=t[3].slice(-1)||" ",r=t[1]?t[1]-1:n++,'", String.prototype.sprintf.'+t[6]+"(arguments["+r+'], ["'+t.join('", "')+'"]), "'});return Function("",'return ["'+e+'"].join("")')},String.prototype.parseUrl=function(){var t=this.match(arguments.callee.re);if(!t)return null;var r={scheme:t[1]||"",subscheme:t[2]||"",user:t[3]||"",pass:t[4]||"",host:t[5],port:t[6]||"",path:t[7]||"",query:t[8]||"",fragment:t[9]||""};return r},String.prototype.parseUrl.re=/^(?:([a-z]+):(?:([a-z]*):)?\/\/)?(?:([^:@]*)(?::([^:@]*))?@)?((?:[a-z0-9_-]+\.)+[a-z]{2,}|localhost|(?:(?:[01]?\d\d?|2[0-4]\d|25[0-5])\.){3}(?:(?:[01]?\d\d?|2[0-4]\d|25[0-5])))(?::(\d+))?(?:([^:\?\#]+))?(?:\?([^\#]+))?(?:\#([^\s]+))?$/i,String.prototype.camelize=function(){return this.replace(/([^-]+)|(?:-(.)([^-]+))/gm,function(t,r,n,e){return(n||"").toUpperCase()+(e||r).toLowerCase()})},String.prototype.uncamelize=function(){return this.replace(/[A-Z]/g,function(t){return"-"+t.toLowerCase()})};
!function(a){"use strict";function b(a,b){var c=(65535&a)+(65535&b),d=(a>>16)+(b>>16)+(c>>16);return d<<16|65535&c}function c(a,b){return a<<b|a>>>32-b}function d(a,d,e,f,g,h){return b(c(b(b(d,a),b(f,h)),g),e)}function e(a,b,c,e,f,g,h){return d(b&c|~b&e,a,b,f,g,h)}function f(a,b,c,e,f,g,h){return d(b&e|c&~e,a,b,f,g,h)}function g(a,b,c,e,f,g,h){return d(b^c^e,a,b,f,g,h)}function h(a,b,c,e,f,g,h){return d(c^(b|~e),a,b,f,g,h)}function i(a,c){a[c>>5]|=128<<c%32,a[(c+64>>>9<<4)+14]=c;var d,i,j,k,l,m=1732584193,n=-271733879,o=-1732584194,p=271733878;for(d=0;d<a.length;d+=16)i=m,j=n,k=o,l=p,m=e(m,n,o,p,a[d],7,-680876936),p=e(p,m,n,o,a[d+1],12,-389564586),o=e(o,p,m,n,a[d+2],17,606105819),n=e(n,o,p,m,a[d+3],22,-1044525330),m=e(m,n,o,p,a[d+4],7,-176418897),p=e(p,m,n,o,a[d+5],12,1200080426),o=e(o,p,m,n,a[d+6],17,-1473231341),n=e(n,o,p,m,a[d+7],22,-45705983),m=e(m,n,o,p,a[d+8],7,1770035416),p=e(p,m,n,o,a[d+9],12,-1958414417),o=e(o,p,m,n,a[d+10],17,-42063),n=e(n,o,p,m,a[d+11],22,-1990404162),m=e(m,n,o,p,a[d+12],7,1804603682),p=e(p,m,n,o,a[d+13],12,-40341101),o=e(o,p,m,n,a[d+14],17,-1502002290),n=e(n,o,p,m,a[d+15],22,1236535329),m=f(m,n,o,p,a[d+1],5,-165796510),p=f(p,m,n,o,a[d+6],9,-1069501632),o=f(o,p,m,n,a[d+11],14,643717713),n=f(n,o,p,m,a[d],20,-373897302),m=f(m,n,o,p,a[d+5],5,-701558691),p=f(p,m,n,o,a[d+10],9,38016083),o=f(o,p,m,n,a[d+15],14,-660478335),n=f(n,o,p,m,a[d+4],20,-405537848),m=f(m,n,o,p,a[d+9],5,568446438),p=f(p,m,n,o,a[d+14],9,-1019803690),o=f(o,p,m,n,a[d+3],14,-187363961),n=f(n,o,p,m,a[d+8],20,1163531501),m=f(m,n,o,p,a[d+13],5,-1444681467),p=f(p,m,n,o,a[d+2],9,-51403784),o=f(o,p,m,n,a[d+7],14,1735328473),n=f(n,o,p,m,a[d+12],20,-1926607734),m=g(m,n,o,p,a[d+5],4,-378558),p=g(p,m,n,o,a[d+8],11,-2022574463),o=g(o,p,m,n,a[d+11],16,1839030562),n=g(n,o,p,m,a[d+14],23,-35309556),m=g(m,n,o,p,a[d+1],4,-1530992060),p=g(p,m,n,o,a[d+4],11,1272893353),o=g(o,p,m,n,a[d+7],16,-155497632),n=g(n,o,p,m,a[d+10],23,-1094730640),m=g(m,n,o,p,a[d+13],4,681279174),p=g(p,m,n,o,a[d],11,-358537222),o=g(o,p,m,n,a[d+3],16,-722521979),n=g(n,o,p,m,a[d+6],23,76029189),m=g(m,n,o,p,a[d+9],4,-640364487),p=g(p,m,n,o,a[d+12],11,-421815835),o=g(o,p,m,n,a[d+15],16,530742520),n=g(n,o,p,m,a[d+2],23,-995338651),m=h(m,n,o,p,a[d],6,-198630844),p=h(p,m,n,o,a[d+7],10,1126891415),o=h(o,p,m,n,a[d+14],15,-1416354905),n=h(n,o,p,m,a[d+5],21,-57434055),m=h(m,n,o,p,a[d+12],6,1700485571),p=h(p,m,n,o,a[d+3],10,-1894986606),o=h(o,p,m,n,a[d+10],15,-1051523),n=h(n,o,p,m,a[d+1],21,-2054922799),m=h(m,n,o,p,a[d+8],6,1873313359),p=h(p,m,n,o,a[d+15],10,-30611744),o=h(o,p,m,n,a[d+6],15,-1560198380),n=h(n,o,p,m,a[d+13],21,1309151649),m=h(m,n,o,p,a[d+4],6,-145523070),p=h(p,m,n,o,a[d+11],10,-1120210379),o=h(o,p,m,n,a[d+2],15,718787259),n=h(n,o,p,m,a[d+9],21,-343485551),m=b(m,i),n=b(n,j),o=b(o,k),p=b(p,l);return[m,n,o,p]}function j(a){var b,c="";for(b=0;b<32*a.length;b+=8)c+=String.fromCharCode(a[b>>5]>>>b%32&255);return c}function k(a){var b,c=[];for(c[(a.length>>2)-1]=void 0,b=0;b<c.length;b+=1)c[b]=0;for(b=0;b<8*a.length;b+=8)c[b>>5]|=(255&a.charCodeAt(b/8))<<b%32;return c}function l(a){return j(i(k(a),8*a.length))}function m(a,b){var c,d,e=k(a),f=[],g=[];for(f[15]=g[15]=void 0,e.length>16&&(e=i(e,8*a.length)),c=0;16>c;c+=1)f[c]=909522486^e[c],g[c]=1549556828^e[c];return d=i(f.concat(k(b)),512+8*b.length),j(i(g.concat(d),640))}function n(a){var b,c,d="0123456789abcdef",e="";for(c=0;c<a.length;c+=1)b=a.charCodeAt(c),e+=d.charAt(b>>>4&15)+d.charAt(15&b);return e}function o(a){return unescape(encodeURIComponent(a))}function p(a){return l(o(a))}function q(a){return n(p(a))}function r(a,b){return m(o(a),o(b))}function s(a,b){return n(r(a,b))}function t(a,b,c){return b?c?r(b,a):s(b,a):c?p(a):q(a)}"function"==typeof define&&define.amd?define(function(){return t}):a.md5=t}(this);
