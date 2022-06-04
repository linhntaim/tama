"use strict";(self["webpackChunkstarter"]=self["webpackChunkstarter"]||[]).push([[356],{928:function(t){t.exports=function(t){return!1!==t&&(0!==t&&0!==t&&(""!==t&&"0"!==t&&((!Array.isArray(t)||0!==t.length)&&(null!==t&&void 0!==t))))}},5778:function(t,e,n){t.exports=function(t){var e=n(962);return e(t)}},9693:function(t){var e="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"===typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t};t.exports=function(t){var n=void 0,o=void 0,r=void 0,i=void 0,a=[n,null,!1,0,"","0"];for(r=0,i=a.length;r<i;r++)if(t===a[r])return!0;if("object"===("undefined"===typeof t?"undefined":e(t))){for(o in t)if(t.hasOwnProperty(o))return!1;return!0}return!1}},962:function(t){t.exports=function(t){return parseFloat(t)||0}},2610:function(t,e,n){var o="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"===typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t};t.exports=function(t){var e=n(2446),r="undefined"===typeof t?"undefined":o(t),i=void 0,a=function(t){var e=/\W*function\s+([\w$]+)\s*\(/.exec(t);return e?e[1]:"(Anonymous)"};return"object"===r?null!==t?"number"!==typeof t.length||t.propertyIsEnumerable("length")||"function"!==typeof t.splice?t.constructor&&a(t.constructor)&&(i=a(t.constructor),"Date"===i?r="date":"RegExp"===i?r="regexp":"LOCUTUS_Resource"===i&&(r="resource")):r="array":r="null":"number"===r&&(r=e(t)?"double":"integer"),r}},8526:function(t,e,n){n(928),n(5778),n(9693),t.exports.floatval=n(962),n(2610),n(8978),n(8032),n(9989),n(4159),n(4226),n(4741),n(350),n(2446),n(3370),n(633),n(3137),n(38),n(611),n(4193),n(6443),n(9400),n(1213),n(3176),n(9227),n(5468),n(9777),n(4760),n(5633),n(2687),n(2675)},8978:function(t){var e="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"===typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t};t.exports=function(t,n){var o=void 0,r=void 0,i="undefined"===typeof t?"undefined":e(t);return"boolean"===i?+t:"string"===i?(0===n&&(r=t.match(/^\s*0(x?)/i),n=r?r[1]?16:8:10),o=parseInt(t,n||10),isNaN(o)||!isFinite(o)?0:o):"number"===i&&isFinite(t)?t<0?Math.ceil(t):Math.floor(t):0}},8032:function(t,e,n){var o="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"===typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t};t.exports=function(t){var e=function(t){var e=/\W*function\s+([\w$]+)\s*\(/.exec(t);return e?e[1]:"(Anonymous)"},r=function(t){if(!t||"object"!==("undefined"===typeof t?"undefined":o(t))||"number"!==typeof t.length)return!1;var e=t.length;return t[t.length]="bogus",e!==t.length?(t.length-=1,!0):(delete t[t.length],!1)};if(!t||"object"!==("undefined"===typeof t?"undefined":o(t)))return!1;var i=r(t);if(i)return!0;var a=n(6427)("locutus.objectsAsArrays")||"on";if("on"===a){var s=Object.prototype.toString.call(t),l=e(t.constructor);if("[object Object]"===s&&"Object"===l)return!0}return!1}},9989:function(t){t.exports=function(t){return"string"===typeof t}},4159:function(t){t.exports=function(t){return!0===t||!1===t}},4226:function(t){t.exports=function(t){return"string"===typeof t}},4741:function(module,__unused_webpack_exports,__webpack_require__){var _typeof="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"===typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t};module.exports=function is_callable(mixedVar,syntaxOnly,callableName){var $global="undefined"!==typeof window?window:__webpack_require__.g,validJSFunctionNamePattern=/^[_$a-zA-Z\xA0-\uFFFF][_$a-zA-Z0-9\xA0-\uFFFF]*$/,name="",obj={},method="",validFunctionName=!1,getFuncName=function(t){var e=/\W*function\s+([\w$]+)\s*\(/.exec(t);return e?e[1]:"(Anonymous)"};if(/(^class|\(this\,)/.test(mixedVar.toString()))return!1;if("string"===typeof mixedVar)obj=$global,method=mixedVar,name=mixedVar,validFunctionName=!!name.match(validJSFunctionNamePattern);else{if("function"===typeof mixedVar)return!0;"[object Array]"===Object.prototype.toString.call(mixedVar)&&2===mixedVar.length&&"object"===_typeof(mixedVar[0])&&"string"===typeof mixedVar[1]&&(obj=mixedVar[0],method=mixedVar[1],name=(obj.constructor&&getFuncName(obj.constructor))+"::"+method)}return(syntaxOnly||"function"===typeof obj[method]||!(!validFunctionName||"function"!==typeof eval(method)))&&(callableName&&($global[callableName]=name),!0)}},350:function(t,e,n){t.exports=function(t){var e=n(2446);return e(t)}},2446:function(t){t.exports=function(t){return+t===t&&(!isFinite(t)||!!(t%1))}},3370:function(t){t.exports=function(t){return t===+t&&isFinite(t)&&!(t%1)}},633:function(t,e,n){t.exports=function(t){var e=n(3370);return e(t)}},3137:function(t,e,n){t.exports=function(t){var e=n(2446);return e(t)}},38:function(t){t.exports=function(t){return null===t}},611:function(t){t.exports=function(t){var e=[" ","\n","\r","\t","\f","\v"," "," "," "," "," "," "," "," "," "," "," "," ","​","\u2028","\u2029","　"].join("");return("number"===typeof t||"string"===typeof t&&-1===e.indexOf(t.slice(-1)))&&""!==t&&!isNaN(t)}},4193:function(t){var e="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"===typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t};t.exports=function(t){return"[object Array]"!==Object.prototype.toString.call(t)&&(null!==t&&"object"===("undefined"===typeof t?"undefined":e(t)))}},6443:function(t,e,n){t.exports=function(t){var e=n(2446);return e(t)}},1213:function(t){t.exports=function(t){return"string"===typeof t}},3176:function(t){t.exports=function(t){if("string"!==typeof t)return!1;var e=[],n="[\ud800-\udbff]",o="[\udc00-\udfff]",r=new RegExp(n+"([\\s\\S])","g"),i=new RegExp("([\\s\\S])"+o,"g"),a=new RegExp("^"+o+"$"),s=new RegExp("^"+n+"$");while(null!==(e=r.exec(t)))if(!e[1]||!e[1].match(a))return!1;while(null!==(e=i.exec(t)))if(!e[1]||!e[1].match(s))return!1;return!0}},9227:function(t,e,n){n(1703),t.exports=function(){var t=arguments,e=t.length,n=0,o=void 0;if(0===e)throw new Error("Empty isset");while(n!==e){if(t[n]===o||null===t[n])return!1;n++}return!0}},5468:function(t,e,n){var o="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"===typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t};t.exports=function(t,e){var r=n(8357),i="",a=" ",s=4,l=function(t,e){for(var n="",o=0;o<t;o++)n+=e;return n},u=function t(e,n,r,i){n>0&&n++;var a=l(r*n,i),s=l(r*(n+1),i),u="";if("object"===("undefined"===typeof e?"undefined":o(e))&&null!==e&&e.constructor){for(var c in u+="Array\n"+a+"(\n",e)"[object Array]"===Object.prototype.toString.call(e[c])?(u+=s,u+="[",u+=c,u+="] => ",u+=t(e[c],n+1,r,i)):(u+=s,u+="[",u+=c,u+="] => ",u+=e[c],u+="\n");u+=a+")\n"}else u=null===e||void 0===e?"":e.toString();return u};return i=u(t,0,s,a),!0!==e?(r(i),!0):i}},9777:function(t){var e="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"===typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t};t.exports=function t(n){var o=void 0,r=void 0,i=void 0,a="",s="",l=0,u=function(t){return~-encodeURI(t).split(/%..|./).length},c=function(t){var n=void 0,o=void 0,r=void 0,i=void 0,a="undefined"===typeof t?"undefined":e(t);if("object"===a&&!t)return"null";if("object"===a){if(!t.constructor)return"object";for(o in r=t.constructor.toString(),n=r.match(/(\w+)\(/),n&&(r=n[1].toLowerCase()),i=["boolean","number","string","array"],i)if(r===i[o]){a=i[o];break}}return a},d=c(n);switch(d){case"function":o="";break;case"boolean":o="b:"+(n?"1":"0");break;case"number":o=(Math.round(n)===n?"i":"d")+":"+n;break;case"string":o="s:"+u(n)+':"'+n+'"';break;case"array":case"object":for(r in o="a",n)if(n.hasOwnProperty(r)){if(a=c(n[r]),"function"===a)continue;i=r.match(/^[0-9]+$/)?parseInt(r,10):r,s+=t(i)+t(n[r]),l++}o+=":"+l+":{"+s+"}";break;case"undefined":default:o="N";break}return"object"!==d&&"array"!==d&&(o+=";"),o}},4760:function(t,e,n){t.exports=function(t){var e=n(2610),o="";if(null===t)return"";switch(o=e(t),o){case"boolean":return!0===t?"1":"";case"array":return"Array";case"object":return"Object"}return t}},5633:function(t,e,n){n(1703);var o=function(){function t(t,e){var n=[],o=!0,r=!1,i=void 0;try{for(var a,s=t[Symbol.iterator]();!(o=(a=s.next()).done);o=!0)if(n.push(a.value),e&&n.length===e)break}catch(l){r=!0,i=l}finally{try{!o&&s["return"]&&s["return"]()}finally{if(r)throw i}}return n}return function(e,n){if(Array.isArray(e))return e;if(Symbol.iterator in Object(e))return t(e,n);throw new TypeError("Invalid attempt to destructure non-iterable instance")}}();function r(){var t=[],e=function(e){return t.push(e[0]),e};return e.get=function(e){if(e>=t.length)throw RangeError("Can't resolve reference "+(e+1));return t[e]},e}function i(t,e){var n=/^(?:N(?=;)|[bidsSaOCrR](?=:)|[^:]+(?=:))/g,o=(n.exec(t)||[])[0];if(!o)throw SyntaxError("Invalid input: "+t);switch(o){case"N":return e([null,2]);case"b":return e(a(t));case"i":return e(s(t));case"d":return e(l(t));case"s":return e(c(t));case"S":return e(d(t));case"a":return y(t,e);case"O":return p(t,e);case"C":return h(t,e);case"r":case"R":return m(t,e);default:throw SyntaxError("Invalid or unsupported data type: "+o)}}function a(t){var e=/^b:([01]);/,n=e.exec(t)||[],r=o(n,2),i=r[0],a=r[1];if(!a)throw SyntaxError("Invalid bool value, expected 0 or 1");return["1"===a,i.length]}function s(t){var e=/^i:([+-]?\d+);/,n=e.exec(t)||[],r=o(n,2),i=r[0],a=r[1];if(!a)throw SyntaxError("Expected an integer value");return[parseInt(a,10),i.length]}function l(t){var e=/^d:(NAN|-?INF|(?:\d+\.\d*|\d*\.\d+|\d+)(?:[eE][+-]\d+)?);/,n=e.exec(t)||[],r=o(n,2),i=r[0],a=r[1];if(!a)throw SyntaxError("Expected a float value");var s=void 0;switch(a){case"NAN":s=Number.NaN;break;case"-INF":s=Number.NEGATIVE_INFINITY;break;case"INF":s=Number.POSITIVE_INFINITY;break;default:s=parseFloat(a);break}return[s,i.length]}function u(t,e){var n=arguments.length>2&&void 0!==arguments[2]&&arguments[2],o=0,r="",i=0,a=t.length,s=!1,l=0;while(o<e&&i<a){var u=t.charAt(i),c=u.charCodeAt(0),d=c>=55296&&c<=56319,f=c>=56320&&c<=57343;n&&"\\"===u&&(u=String.fromCharCode(parseInt(t.substr(i+1,2),16)),l++,i+=2),i++,o+=d||f&&s?2:c>2047?3:c>127?2:1,o+=s&&!f?1:0,r+=u,s=d}return[r,o,l]}function c(t){var e=/^s:(\d+):"/g,n=e.exec(t)||[],r=o(n,2),i=r[0],a=r[1];if(!i)throw SyntaxError("Expected a string value");var s=parseInt(a,10);t=t.substr(i.length);var l=u(t,s),c=o(l,2),d=c[0],f=c[1];if(f!==s)throw SyntaxError("Expected string of "+s+" bytes, but got "+f);if(t=t.substr(d.length),!t.startsWith('";'))throw SyntaxError('Expected ";');return[d,i.length+d.length+2]}function d(t){var e=/^S:(\d+):"/g,n=e.exec(t)||[],r=o(n,2),i=r[0],a=r[1];if(!i)throw SyntaxError("Expected an escaped string value");var s=parseInt(a,10);t=t.substr(i.length);var l=u(t,s,!0),c=o(l,3),d=c[0],f=c[1],p=c[2];if(f!==s)throw SyntaxError("Expected escaped string of "+s+" bytes, but got "+f);if(t=t.substr(d.length+2*p),!t.startsWith('";'))throw SyntaxError('Expected ";');return[d,i.length+d.length+2]}function f(t){try{return c(t)}catch(e){}try{return d(t)}catch(e){}try{return s(t)}catch(e){throw SyntaxError("Expected key or index")}}function p(t,e){var n=/^O:(\d+):"([^"]+)":(\d+):\{/,r=n.exec(t)||[],a=o(r,4),s=a[0],l=a[2],u=a[3];if(!s)throw SyntaxError("Invalid input");if("stdClass"!==l)throw SyntaxError("Unsupported object type: "+l);var c=s.length,d=parseInt(u,10),p={};e([p]),t=t.substr(c);for(var h=0;h<d;h++){var m=f(t);t=t.substr(m[1]),c+=m[1];var y=i(t,e);t=t.substr(y[1]),c+=y[1],p[m[0]]=y[0]}if("}"!==t.charAt(0))throw SyntaxError("Expected }");return[p,c+1]}function h(t,e){throw Error("Not yet implemented")}function m(t,e){var n=/^[rR]:([1-9]\d*);/,r=n.exec(t)||[],i=o(r,2),a=i[0],s=i[1];if(!a)throw SyntaxError("Expected reference value");return[e.get(parseInt(s,10)-1),a.length]}function y(t,e){var n=/^a:(\d+):{/,r=n.exec(t)||[],i=o(r,2),a=i[0],s=i[1];if(!s)throw SyntaxError("Expected array length annotation");t=t.substr(a.length);var l=g(t,parseInt(s,10),e);if("}"!==t.charAt(l[1]))throw SyntaxError("Expected }");return[l[0],a.length+l[1]+1]}function g(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:0,n=arguments[2],o=void 0,r=!1,a=void 0,s=0,l=[];n([l]);for(var u=0;u<e;u++)o=f(t),r||(r="string"===typeof o[0]),t=t.substr(o[1]),s+=o[1],a=i(t,n),t=t.substr(a[1]),s+=a[1],l[o[0]]=a[0];return r&&(l=Object.assign({},l)),[l,s]}t.exports=function(t){try{return"string"===typeof t&&i(t,r())[0]}catch(e){return console.error(e),!1}}},2687:function(t,e,n){var o="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"===typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t};t.exports=function(){var t=n(8357),e="",r=" ",i=4,a=0,s=0,l=function(t){var e=/\W*function\s+([\w$]+)\s*\(/.exec(t);return e?e[1]:"(Anonymous)"},u=function(t,e){for(var n="",o=0;o<t;o++)n+=e;return n},c=function(t,e){var n="";if(null===t)n="NULL";else if("boolean"===typeof t)n="bool("+t+")";else if("string"===typeof t)n="string("+t.length+') "'+t+'"';else if("number"===typeof t)n=parseFloat(t)===parseInt(t,10)?"int("+t+")":"float("+t+")";else if("undefined"===typeof t)n="undefined";else if("function"===typeof t){var o=t.toString().split("\n");n="";for(var r=0,i=o.length;r<i;r++)n+=(0!==r?"\n"+e:"")+o[r]}else if(t instanceof Date)n="Date("+t+")";else if(t instanceof RegExp)n="RegExp("+t+")";else if(t.nodeName)switch(t.nodeType){case 1:n="undefined"===typeof t.namespaceURI||"https://www.w3.org/1999/xhtml"===t.namespaceURI?'HTMLElement("'+t.nodeName+'")':'XML Element("'+t.nodeName+'")';break;case 2:n="ATTRIBUTE_NODE("+t.nodeName+")";break;case 3:n="TEXT_NODE("+t.nodeValue+")";break;case 4:n="CDATA_SECTION_NODE("+t.nodeValue+")";break;case 5:n="ENTITY_REFERENCE_NODE";break;case 6:n="ENTITY_NODE";break;case 7:n="PROCESSING_INSTRUCTION_NODE("+t.nodeName+":"+t.nodeValue+")";break;case 8:n="COMMENT_NODE("+t.nodeValue+")";break;case 9:n="DOCUMENT_NODE";break;case 10:n="DOCUMENT_TYPE_NODE";break;case 11:n="DOCUMENT_FRAGMENT_NODE";break;case 12:n="NOTATION_NODE";break}return n},d=function t(e,n,r,i){n>0&&n++;var s=u(r*(n-1),i),d=u(r*(n+1),i),f="",p="";if("object"===("undefined"===typeof e?"undefined":o(e))&&null!==e){if(e.constructor&&"LOCUTUS_Resource"===l(e.constructor))return e.var_dump();for(var h in a=0,e)e.hasOwnProperty(h)&&a++;for(var m in f+="array("+a+") {\n",e){var y=e[m];"object"!==("undefined"===typeof y?"undefined":o(y))||null===y||y instanceof Date||y instanceof RegExp||y.nodeName?(p=c(y,d),f+=d,f+="[",f+=m,f+="] =>\n",f+=d,f+=p,f+="\n"):(f+=d,f+="[",f+=m,f+="] =>\n",f+=d,f+=t(y,n+1,r,i))}f+=s+"}\n"}else f=c(e,d);return f};for(e=d(arguments[0],0,i,r),s=1;s<arguments.length;s++)e+="\n"+d(arguments[s],0,i,r);return t(e),e}},2675:function(t,e,n){var o="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"===typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t};t.exports=function t(e,r){var i=n(8357),a="",s="",l=void 0,u=0,c=[],d=0,f=[],p=arguments[2]||2,h="",m="",y=function(t){var e=/\W*function\s+([\w$]+)\s*\(/.exec(t);return e?e[1]:"(Anonymous)"},g=function(t){var e=Math.floor(Number(t));return e!==1/0&&String(e)===t&&e>=0},b=function(t){return new Array(t+1).join(" ")},x=function(t){var e=0,n=void 0,r=void 0,i=void 0,a="undefined"===typeof t?"undefined":o(t);if("object"===a&&t&&t.constructor&&"LOCUTUS_Resource"===y(t.constructor))return"resource";if("function"===a)return"function";if("object"===a&&!t)return"null";if("object"===a){if(!t.constructor)return"object";for(i=t.constructor.toString(),n=i.match(/(\w+)\(/),n&&(i=n[1].toLowerCase()),r=["boolean","number","string","array"],e=0;e<r.length;e++)if(i===r[e]){a=r[e];break}}return a},v=x(e);if(null===v)a="NULL";else if("array"===v||"object"===v){for(d in m=b(p-2),h=b(p),e){l=" ";var _=x(e[d]);"array"!==_&&"object"!==_||(l="\n"),l+=t(e[d],1,p+2),d=g(d)?d:"'"+d+"'",c[u++]=h+d+" =>"+l}c.length>0&&(s=c.join(",\n")+",\n"),a=m+"array (\n"+s+m+")"}else"function"===v?(f=e.toString().match(/function .*?\((.*?)\) \{([\s\S]*)\}/),a="create_function ('"+f[1]+"', '"+f[2].replace(/'/g,"\\'")+"')"):a="resource"===v?"NULL":"string"!==typeof e?e:"'"+e.replace(/(["'])/g,"\\$1").replace(/\0/g,"\\0")+"'";return r?a:(i(a),null)}},9356:function(t,e,n){n.r(e),n.d(e,{default:function(){return Mt}});var o=n(3396),r=n(7139);const i={class:"table-responsive"},a={class:"table align-middle"},s={class:"text-nowrap text-center",colspan:"4"},l={class:"btn-group",role:"group","aria-label":"Actions"},u=(0,o._)("i",{class:"fas fa-plus-circle"},null,-1),c=[u],d=(0,o._)("i",{class:"fas fa-file-export"},null,-1),f=[d],p=(0,o._)("i",{class:"fas fa-file-import"},null,-1),h=[p],m=(0,o._)("th",{class:"text-nowrap text-end"},[(0,o._)("span",{class:"text-primary"},"Initial (USD)")],-1),y={class:"text-nowrap text-end"},g=(0,o._)("th",{class:"text-nowrap text-end"},[(0,o._)("span",{class:"text-info"},"Current (USD)")],-1),b=(0,o._)("td",{colspan:"4"},null,-1),x={class:"text-end"},v={class:"text-end"},_={class:"text-end"},S=(0,o._)("th",{class:"text-nowrap text-center"},"#",-1),w=(0,o._)("th",{class:"text-nowrap"},null,-1),C=(0,o._)("th",{class:"text-nowrap"},"Coin/Token",-1),E=(0,o._)("th",{class:"text-nowrap text-end w180"},"Price (USD)",-1),k=(0,o._)("th",{class:"text-nowrap text-end w180"},"Amount",-1),N=(0,o.Uk)("Current (USD)"),A=(0,o._)("th",{class:"text-nowrap text-end w180"},"% Current",-1),I={class:"text-center"},D={class:"text-center text-nowrap"},U={class:"btn-group",role:"group","aria-label":"Item actions"},F=["disabled","onClick"],j=(0,o._)("i",{class:"fas fa-times"},null,-1),O=[j],P=["disabled","onClick"],T=(0,o._)("i",{class:"fas fa-arrow-up"},null,-1),V=[T],$=["disabled","onClick"],R=(0,o._)("i",{class:"fas fa-arrow-down"},null,-1),M=[R],B=["href"],L=(0,o._)("i",{class:"fas fa-chart-area"},null,-1),W=[L],K={class:"text-end"},Z={class:"text-end"},Y={class:"text-end"},q={class:"text-end"};function z(t,e,n,u,d,p){const j=(0,o.up)("add-form"),T=(0,o.up)("protected-formatted-number-input"),R=(0,o.up)("protected-formatted-number"),L=(0,o.up)("price");return(0,o.wg)(),(0,o.iD)(o.HY,null,[d.adding?((0,o.wg)(),(0,o.j4)(j,{key:0,class:"mb-3",onAdd:p.onAdd},null,8,["onAdd"])):(0,o.kq)("",!0),(0,o._)("div",i,[(0,o._)("table",a,[(0,o._)("thead",null,[(0,o._)("tr",null,[(0,o._)("th",s,[(0,o._)("div",l,[(0,o._)("button",{class:"btn btn-sm border-0",type:"button",onClick:e[0]||(e[0]=(...t)=>p.onAddClick&&p.onAddClick(...t)),title:"Add","data-bs-toggle":"tooltip"},c),(0,o._)("button",{class:"btn btn-sm border-0",type:"button",onClick:e[1]||(e[1]=(...t)=>p.onProtectClick&&p.onProtectClick(...t)),title:"Protect","data-bs-toggle":"tooltip"},[(0,o._)("i",{class:(0,r.C_)(["fas",d.protected?"fa-eye-slash":"fa-eye"])},null,2)]),(0,o._)("button",{class:"btn btn-sm border-0",type:"button",onClick:e[2]||(e[2]=(...t)=>p.onExportClick&&p.onExportClick(...t)),title:"Export","data-bs-toggle":"tooltip"},f),(0,o._)("button",{class:"btn btn-sm border-0",type:"button",onClick:e[3]||(e[3]=(...t)=>p.onImportClick&&p.onImportClick(...t)),title:"Import","data-bs-toggle":"tooltip"},h)])]),m,(0,o._)("th",y,[(0,o._)("span",{class:(0,r.C_)({"text-danger":p.profit<0,"text-success":p.profit>0,"text-light":0===p.profit}),onClick:e[4]||(e[4]=(...t)=>p.onProfitClick&&p.onProfitClick(...t))},"Profit (USD)",2)]),g])]),(0,o._)("tbody",null,[(0,o._)("tr",null,[b,(0,o._)("td",x,[(0,o.Wm)(T,{modelValue:p.initial,"onUpdate:modelValue":e[5]||(e[5]=t=>p.initial=t),fractionDigits:-1,textClass:"text-primary",inputClass:"outline-0 border-0 w-100 text-primary text-end",protected:d.protected},null,8,["modelValue","protected"])]),(0,o._)("td",v,[(0,o.Wm)(R,{class:(0,r.C_)({"text-danger":p.profit<0,"text-success":p.profit>0,"text-light":0===p.profit}),value:p.profit,protected:!d.unprotectedProfit&&d.protected},null,8,["class","value","protected"])]),(0,o._)("td",_,[(0,o.Wm)(R,{class:"text-info",value:p.current,protected:d.protected},null,8,["value","protected"])])])]),(0,o._)("thead",null,[(0,o._)("tr",null,[S,w,C,E,k,(0,o._)("th",{class:"text-nowrap text-end w180",onClick:e[6]||(e[6]=(...t)=>p.onCurrentSortClick&&p.onCurrentSortClick(...t))},[N,(0,o._)("i",{class:(0,r.C_)(["fas ms-2",{"fa-sort text-light":0===d.sortCurrent,"fa-sort-up":1===d.sortCurrent,"fa-sort-down":2===d.sortCurrent}])},null,2)]),A])]),(0,o._)("tbody",null,[((0,o.wg)(!0),(0,o.iD)(o.HY,null,(0,o.Ko)(p.assets,((t,e)=>((0,o.wg)(),(0,o.iD)("tr",null,[(0,o._)("td",I,(0,r.zw)(e+1),1),(0,o._)("td",D,[(0,o._)("div",U,[(0,o._)("button",{class:"btn btn-sm border-0",disabled:d.loading._,onClick:n=>p.onDeleteClick(t,e)},O,8,F),(0,o._)("button",{class:"btn btn-sm border-0",disabled:d.loading._||0===e,onClick:n=>p.onMoveUpClick(t,e)},V,8,P),(0,o._)("button",{class:"btn btn-sm border-0",disabled:d.loading._||e===p.assets.length-1,onClick:n=>p.onMoveDownClick(t,e)},M,8,$)])]),(0,o._)("th",null,[(0,o._)("a",{class:(0,r.C_)(["btn btn-link btn-sm",{disabled:!t.chartUrl}]),href:t.chartUrl?t.chartUrl:"#",target:"_blank"},W,10,B),(0,o.Uk)((0,r.zw)(t.symbol),1)]),(0,o._)("td",K,[(0,o.Wm)(L,{asset:t,protected:d.protected,onUpdate:n=>p.onPriceUpdate(t,e,n)},null,8,["asset","protected","onUpdate"])]),(0,o._)("td",Z,[(0,o.Wm)(T,{modelValue:t.amount,"onUpdate:modelValue":n=>p.onAmountUpdate(t,e,n),fractionDigits:-1,inputClass:"outline-0 border-0 w-100 h-100 text-end",protected:d.protected},null,8,["modelValue","onUpdate:modelValue","protected"])]),(0,o._)("td",Y,[(0,o.Wm)(R,{value:t.price*t.amount,protected:d.protected},null,8,["value","protected"])]),(0,o._)("td",q,[(0,o.Wm)(R,{value:p.current?t.price*t.amount/p.current*100:0,protected:d.protected},null,8,["value","protected"])])])))),256))])])])],64)}var H=n(65),G=n(9242);const J={class:"col-12"},X=(0,o._)("label",{class:"visually-hidden",for:"inputSymbol"},"Symbol",-1),Q={class:"col-12"},tt=(0,o._)("label",{class:"visually-hidden",for:"inputAmount"},"Amount",-1),et={class:"col-12"},nt=(0,o._)("label",{class:"visually-hidden",for:"inputExchange"},"Exchange",-1),ot=["value","selected"],rt={class:"col-12"},it=["disabled"];function at(t,e,n,i,a,s){return(0,o.wg)(),(0,o.iD)("form",{class:"row row-cols-lg-auto g-3 align-items-center",onSubmit:e[4]||(e[4]=(0,G.iM)(((...t)=>s.onSubmit&&s.onSubmit(...t)),["prevent"])),method:"post"},[(0,o._)("div",J,[X,(0,o.wy)((0,o._)("input",{class:"form-control",id:"inputSymbol","onUpdate:modelValue":e[0]||(e[0]=t=>a.symbol=t),type:"text",placeholder:"BTC",required:""},null,512),[[G.nr,a.symbol]])]),(0,o._)("div",Q,[tt,(0,o.wy)((0,o._)("input",{class:"form-control text-end",id:"inputAmount","onUpdate:modelValue":e[1]||(e[1]=t=>a.amount=t),type:"text",placeholder:"0.00000000",required:""},null,512),[[G.nr,a.amount]])]),(0,o._)("div",et,[nt,(0,o.wy)((0,o._)("select",{class:"form-select",id:"inputExchange","onUpdate:modelValue":e[2]||(e[2]=t=>a.exchange=t),onChange:e[3]||(e[3]=(...e)=>t.onExchangeChange&&t.onExchangeChange(...e))},[((0,o.wg)(!0),(0,o.iD)(o.HY,null,(0,o.Ko)(t.exchanges,(t=>((0,o.wg)(),(0,o.iD)("option",{key:t.id,value:t.id,selected:a.exchange===t.id},(0,r.zw)(t.name),9,ot)))),128))],544),[[G.bM,a.exchange]])]),(0,o._)("div",rt,[(0,o._)("button",{class:"btn btn-primary",type:"submit",disabled:a.loading._},"Add your asset",8,it)])],32)}var st=n(8526),lt={name:"AddForm",data(){return{loading:{_:!1},symbol:"",amount:"0.000000000000000000",exchange:null}},computed:{...(0,H.Se)({exchanges:"exchange/all"})},created(){this.getAllExchanges()},methods:{...(0,H.nv)({exchangeAll:"exchange/all"}),getAllExchanges(){this.loading._=!0,this.exchangeAll().then((()=>{this.exchange=this.exchanges.length?this.exchanges[0].id:null,this.loading._=!1}))},onSubmit(){this.$emit("add",{symbol:this.symbol.toUpperCase(),amount:(t=>t>=0?t:0)((0,st.floatval)(this.amount)),exchange:this.exchange}),this.symbol="",this.amount="0.000000000000000000",this.exchange=this.exchanges.length?this.exchanges[0].id:null}}},ut=n(89);const ct=(0,ut.Z)(lt,[["render",at]]);var dt=ct;function ft(t,e,n,i,a,s){const l=(0,o.up)("protected-formatted-number");return(0,o.wg)(),(0,o.j4)(l,{class:(0,r.C_)({"opacity-50":a.loading._&&!n.protected}),value:n.asset.price,fractionDigits:-1,protected:n.protected},null,8,["class","value","protected"])}var pt=n(4715),ht=n(9226);const mt={key:0};function yt(t,e,n,r,i,a){const s=(0,o.up)("formatted-number");return n.protected?((0,o.wg)(),(0,o.iD)("span",mt,"*****")):((0,o.wg)(),(0,o.j4)(s,{key:1,value:n.value,fractionDigits:n.fractionDigits},null,8,["value","fractionDigits"]))}const gt={class:"formatted-number"};function bt(t,e,n,i,a,s){return(0,o.wg)(),(0,o.iD)("span",gt,(0,r.zw)(s.shownValue),1)}n(6699);var xt={name:"FormattedNumber",props:{value:Number,fractionDigits:{type:Number,default:2}},computed:{shownValue(){if(this.fractionDigits>0)return this.value.toFixed(this.fractionDigits).replace(/(\d)(?=(\d{3})+\.)/g,"$1,");if(0===this.fractionDigits)return this.value.toFixed(this.fractionDigits).replace(/(\d)(?=(\d{3})+$)/g,"$1,");const t=this.value.toString();return t.includes(".")?t.replace(/(\d)(?=(\d{3})+\.)/g,"$1,"):t.replace(/(\d)(?=(\d{3})+$)/g,"$1,")}}};const vt=(0,ut.Z)(xt,[["render",bt]]);var _t=vt,St={name:"ProtectedFormattedNumber",components:{FormattedNumber:_t},props:{value:Number,fractionDigits:{type:Number,default:2},protected:{type:Boolean,default:!0}}};const wt=(0,ut.Z)(St,[["render",yt]]);var Ct=wt,Et={name:"Price",emits:["update"],components:{ProtectedFormattedNumber:Ct},props:{asset:Object,protected:{type:Boolean,default:!0}},data(){return{loading:{_:!1},timeout:{handle:null,duration:2e4}}},beforeUnmount(){this.clearFetchPrice()},mounted(){this.fetchPrice()},methods:{clearFetchPrice(){this.timeout.handle&&clearTimeout(this.timeout.handle)},fetchPrice(){this.loading._||(this.loading._=!0,pt.l2.$service(ht.O).done((t=>{this.$emit("update",{price:t.symbol.price,chartUrl:t.symbol.chart_url})})).always((()=>{this.loading._=!1,this.timeout.handle=setTimeout((()=>this.fetchPrice()),this.timeout.duration)})).symbolShow(this.asset.exchange,this.asset.symbol))}}};const kt=(0,ut.Z)(Et,[["render",ft]]);var Nt=kt;const At={key:0};function It(t,e,n,r,i,a){const s=(0,o.up)("formatted-number-input");return n.protected?((0,o.wg)(),(0,o.iD)("span",At,"*****")):((0,o.wg)(),(0,o.j4)(s,{key:1,modelValue:n.modelValue,"onUpdate:modelValue":a.onUpdateModelValue,fractionDigits:n.fractionDigits,textClass:n.textClass,inputClass:n.inputClass},null,8,["modelValue","onUpdate:modelValue","fractionDigits","textClass","inputClass"]))}const Dt=["value"];function Ut(t,e,n,i,a,s){const l=(0,o.up)("formatted-number");return a.toggle?((0,o.wg)(),(0,o.j4)(l,{key:0,class:(0,r.C_)(n.textClass),value:n.modelValue,fractionDigits:n.fractionDigits,onClick:s.onToggle},null,8,["class","value","fractionDigits","onClick"])):((0,o.wg)(),(0,o.iD)("input",{key:1,ref:"numberInput",class:(0,r.C_)(n.inputClass),value:a.input,type:"text",onFocus:e[0]||(e[0]=(...t)=>s.onFocus&&s.onFocus(...t)),onBlur:e[1]||(e[1]=(...t)=>s.onBlur&&s.onBlur(...t)),onKeyup:[e[2]||(e[2]=(0,G.D2)(((...t)=>s.onPressEnterKey&&s.onPressEnterKey(...t)),["enter"])),e[3]||(e[3]=(0,G.D2)(((...t)=>s.onPressEscapeKey&&s.onPressEscapeKey(...t)),["esc"]))]},null,42,Dt))}var Ft={name:"FormattedNumberInput",components:{FormattedNumber:_t},props:{modelValue:Number,fractionDigits:{type:Number,default:2},textClass:{type:String,default:""},inputClass:{type:String,default:""}},data(){return{input:this.modelValue,toggle:!0}},watch:{modelValue(){this.input=this.modelValue}},methods:{onToggle(){this.toggle=!this.toggle,this.toggle||this.$nextTick((()=>this.$refs.numberInput.focus()))},onInput(t){const e=parseFloat(t.target.value);this.$emit("update:modelValue",e||0)},onBlur(t){this.onInput(t),this.onToggle()},onFocus(t){const e=t.target.value;/^0+$/.test(e)&&t.target.setSelectionRange(0,e.length)},onPressEnterKey(){this.$refs.numberInput.blur()},onPressEscapeKey(){this.input=this.modelValue,this.$forceUpdate(),this.$nextTick((()=>this.$refs.numberInput.blur()))}}};const jt=(0,ut.Z)(Ft,[["render",Ut]]);var Ot=jt,Pt={name:"ProtectedFormattedNumberInput",components:{FormattedNumberInput:Ot},props:{modelValue:Number,fractionDigits:{type:Number,default:2},textClass:{type:String,default:""},inputClass:{type:String,default:""},protected:{type:Boolean,default:!0}},methods:{onUpdateModelValue(t){this.$emit("update:modelValue",t)}}};const Tt=(0,ut.Z)(Pt,[["render",It]]);var Vt=Tt,$t={name:"Index",components:{AddForm:dt,ProtectedFormattedNumber:Ct,ProtectedFormattedNumberInput:Vt,Price:Nt},data(){return{loading:{_:!1},adding:!1,protected:!0,unprotectedProfit:!!this.$route.query.unprotected_profit,sortCurrent:0}},computed:{...(0,H.Se)({accountIsLoggedIn:"account/isLoggedIn",holding:"holding/holding",holdingForStore:"holding/holdingForStore"}),initial:{set(t){this.holdingUpdateInitial(t)},get(){return this.$store.state.holding.initial}},assets:{get(){return this.$store.state.holding.assets}},profit(){return this.current-this.initial},current(){if(this.assets.length<=0)return this.initial;let t=0;return this.assets.forEach((e=>{t+=e.price*e.amount})),t}},watch:{accountIsLoggedIn(){this.reset(),this.init()}},beforeUnmount(){this.reset()},mounted(){this.init()},methods:{...(0,H.OI)({holdingUpdateAssetPrice:"holding/updateAssetPrice"}),...(0,H.nv)({holdingCurrent:"holding/current",holdingImport:"holding/import",holdingUpdateInitial:"holding/updateInitial",holdingAddAsset:"holding/addAsset",holdingRemoveAsset:"holding/removeAsset",holdingUpdateAssetAmount:"holding/updateAssetAmount",holdingMoveUpAsset:"holding/moveUpAsset",holdingMoveDownAsset:"holding/moveDownAsset",holdingSortAssetBySymbol:"holding/sortAssetBySymbol",holdingSortAssetByPriceTotal:"holding/sortAssetByPriceTotal",holdingReset:"holding/reset"}),reset(){this.protected=!0,this.sortCurrent=0,this.holdingReset()},async init(){await this.protectionFromCache(),await this.holdingCurrent()},async protectionToCache(){await this.$cache.set("holding.protected",this.protected)},async protectionFromCache(){this.protected=await this.$cache.get("holding.protected",!0)},onAdd(t){this.loading._=!0,this.holdingAddAsset(t).then((()=>{this.onAddClick(),this.loading._=!1})).catch((()=>this.loading._=!1))},onAddClick(){this.adding=!this.adding},onProtectClick(){this.protected=!this.protected,this.protectionToCache()},onExportClick(){const t="crypto-holding.json",e=new Blob([JSON.stringify(this.holdingForStore)],{type:"application/json;charset=utf-8"});if(window.navigator.msSaveOrOpenBlob)window.navigator.msSaveOrOpenBlob(e,t);else{const n=new FileReader;n.onloadend=function(){const e=document.createElement("a");e.href=n.result.toString(),e.download=t,document.body.appendChild(e),e.click(),setTimeout((function(){document.body.removeChild(e)}),0)},n.readAsDataURL(e)}},onImportClick(){this.loading._=!0;let t=document.getElementById("inputFileImport");t||(t=document.createElement("input"),t.id="inputFileImport",t.type="file",t.style.display="none",t.onchange=e=>{e.target.files.length?this.holdingImport(e.target.files[0]).then((()=>{t.value="",this.loading._=!1})).catch((()=>{t.value="",this.loading._=!1})):t.value=""},document.body.appendChild(t)),t.click()},onProfitClick(){this.unprotectedProfit=!this.unprotectedProfit},onCurrentSortClick(){if(this.loading._)return;this.loading._=!0;const t=(this.sortCurrent+1)%3;this.sortByCurrent(t).then((()=>{this.sortCurrent=t,this.loading._=!1})).catch((()=>this.loading._=!1))},sortByCurrent(t){switch(t){case 1:return this.holdingSortAssetByPriceTotal();case 2:return this.holdingSortAssetByPriceTotal(!1);default:return this.holdingSortAssetBySymbol()}},onDeleteClick(t,e){this.loading._=!0,this.holdingRemoveAsset({asset:t,index:e}).then((()=>this.loading._=!1)).catch((()=>this.loading._=!1))},onMoveUpClick(t,e){this.loading._=!0,this.holdingMoveUpAsset({asset:t,index:e}).then((()=>{this.sortCurrent=0,this.loading._=!1})).catch((()=>this.loading._=!1))},onMoveDownClick(t,e){this.loading._=!0,this.holdingMoveDownAsset({asset:t,index:e}).then((()=>{this.sortCurrent=0,this.loading._=!1})).catch((()=>this.loading._=!1))},onPriceUpdate(t,e,n){this.holdingUpdateAssetPrice({index:e,price:n.price,chartUrl:n.chartUrl})},onAmountUpdate(t,e,n){this.loading._=!0,this.holdingUpdateAssetAmount({asset:t,index:e,amount:n}).then((()=>this.loading._=!1)).catch((()=>this.loading._=!1))}}};const Rt=(0,ut.Z)($t,[["render",z]]);var Mt=Rt}}]);
//# sourceMappingURL=356.4a6678e8.js.map