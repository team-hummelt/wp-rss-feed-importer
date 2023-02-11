!function(){var e,t={669:function(e,t,r){e.exports=r(609)},448:function(e,t,r){"use strict";var n=r(867),o=r(26),s=r(372),i=r(327),a=r(97),c=r(109),u=r(985),p=r(61);e.exports=function(e){return new Promise((function(t,r){var l=e.data,d=e.headers,f=e.responseType;n.isFormData(l)&&delete d["Content-Type"];var h=new XMLHttpRequest;if(e.auth){var m=e.auth.username||"",g=e.auth.password?unescape(encodeURIComponent(e.auth.password)):"";d.Authorization="Basic "+btoa(m+":"+g)}var v=a(e.baseURL,e.url);function b(){if(h){var n="getAllResponseHeaders"in h?c(h.getAllResponseHeaders()):null,s={data:f&&"text"!==f&&"json"!==f?h.response:h.responseText,status:h.status,statusText:h.statusText,headers:n,config:e,request:h};o(t,r,s),h=null}}if(h.open(e.method.toUpperCase(),i(v,e.params,e.paramsSerializer),!0),h.timeout=e.timeout,"onloadend"in h?h.onloadend=b:h.onreadystatechange=function(){h&&4===h.readyState&&(0!==h.status||h.responseURL&&0===h.responseURL.indexOf("file:"))&&setTimeout(b)},h.onabort=function(){h&&(r(p("Request aborted",e,"ECONNABORTED",h)),h=null)},h.onerror=function(){r(p("Network Error",e,null,h)),h=null},h.ontimeout=function(){var t="timeout of "+e.timeout+"ms exceeded";e.timeoutErrorMessage&&(t=e.timeoutErrorMessage),r(p(t,e,e.transitional&&e.transitional.clarifyTimeoutError?"ETIMEDOUT":"ECONNABORTED",h)),h=null},n.isStandardBrowserEnv()){var y=(e.withCredentials||u(v))&&e.xsrfCookieName?s.read(e.xsrfCookieName):void 0;y&&(d[e.xsrfHeaderName]=y)}"setRequestHeader"in h&&n.forEach(d,(function(e,t){void 0===l&&"content-type"===t.toLowerCase()?delete d[t]:h.setRequestHeader(t,e)})),n.isUndefined(e.withCredentials)||(h.withCredentials=!!e.withCredentials),f&&"json"!==f&&(h.responseType=e.responseType),"function"==typeof e.onDownloadProgress&&h.addEventListener("progress",e.onDownloadProgress),"function"==typeof e.onUploadProgress&&h.upload&&h.upload.addEventListener("progress",e.onUploadProgress),e.cancelToken&&e.cancelToken.promise.then((function(e){h&&(h.abort(),r(e),h=null)})),l||(l=null),h.send(l)}))}},609:function(e,t,r){"use strict";var n=r(867),o=r(849),s=r(321),i=r(185);function a(e){var t=new s(e),r=o(s.prototype.request,t);return n.extend(r,s.prototype,t),n.extend(r,t),r}var c=a(r(655));c.Axios=s,c.create=function(e){return a(i(c.defaults,e))},c.Cancel=r(263),c.CancelToken=r(972),c.isCancel=r(502),c.all=function(e){return Promise.all(e)},c.spread=r(713),c.isAxiosError=r(268),e.exports=c,e.exports.default=c},263:function(e){"use strict";function t(e){this.message=e}t.prototype.toString=function(){return"Cancel"+(this.message?": "+this.message:"")},t.prototype.__CANCEL__=!0,e.exports=t},972:function(e,t,r){"use strict";var n=r(263);function o(e){if("function"!=typeof e)throw new TypeError("executor must be a function.");var t;this.promise=new Promise((function(e){t=e}));var r=this;e((function(e){r.reason||(r.reason=new n(e),t(r.reason))}))}o.prototype.throwIfRequested=function(){if(this.reason)throw this.reason},o.source=function(){var e;return{token:new o((function(t){e=t})),cancel:e}},e.exports=o},502:function(e){"use strict";e.exports=function(e){return!(!e||!e.__CANCEL__)}},321:function(e,t,r){"use strict";var n=r(867),o=r(327),s=r(782),i=r(572),a=r(185),c=r(875),u=c.validators;function p(e){this.defaults=e,this.interceptors={request:new s,response:new s}}p.prototype.request=function(e){"string"==typeof e?(e=arguments[1]||{}).url=arguments[0]:e=e||{},(e=a(this.defaults,e)).method?e.method=e.method.toLowerCase():this.defaults.method?e.method=this.defaults.method.toLowerCase():e.method="get";var t=e.transitional;void 0!==t&&c.assertOptions(t,{silentJSONParsing:u.transitional(u.boolean,"1.0.0"),forcedJSONParsing:u.transitional(u.boolean,"1.0.0"),clarifyTimeoutError:u.transitional(u.boolean,"1.0.0")},!1);var r=[],n=!0;this.interceptors.request.forEach((function(t){"function"==typeof t.runWhen&&!1===t.runWhen(e)||(n=n&&t.synchronous,r.unshift(t.fulfilled,t.rejected))}));var o,s=[];if(this.interceptors.response.forEach((function(e){s.push(e.fulfilled,e.rejected)})),!n){var p=[i,void 0];for(Array.prototype.unshift.apply(p,r),p=p.concat(s),o=Promise.resolve(e);p.length;)o=o.then(p.shift(),p.shift());return o}for(var l=e;r.length;){var d=r.shift(),f=r.shift();try{l=d(l)}catch(e){f(e);break}}try{o=i(l)}catch(e){return Promise.reject(e)}for(;s.length;)o=o.then(s.shift(),s.shift());return o},p.prototype.getUri=function(e){return e=a(this.defaults,e),o(e.url,e.params,e.paramsSerializer).replace(/^\?/,"")},n.forEach(["delete","get","head","options"],(function(e){p.prototype[e]=function(t,r){return this.request(a(r||{},{method:e,url:t,data:(r||{}).data}))}})),n.forEach(["post","put","patch"],(function(e){p.prototype[e]=function(t,r,n){return this.request(a(n||{},{method:e,url:t,data:r}))}})),e.exports=p},782:function(e,t,r){"use strict";var n=r(867);function o(){this.handlers=[]}o.prototype.use=function(e,t,r){return this.handlers.push({fulfilled:e,rejected:t,synchronous:!!r&&r.synchronous,runWhen:r?r.runWhen:null}),this.handlers.length-1},o.prototype.eject=function(e){this.handlers[e]&&(this.handlers[e]=null)},o.prototype.forEach=function(e){n.forEach(this.handlers,(function(t){null!==t&&e(t)}))},e.exports=o},97:function(e,t,r){"use strict";var n=r(793),o=r(303);e.exports=function(e,t){return e&&!n(t)?o(e,t):t}},61:function(e,t,r){"use strict";var n=r(481);e.exports=function(e,t,r,o,s){var i=new Error(e);return n(i,t,r,o,s)}},572:function(e,t,r){"use strict";var n=r(867),o=r(527),s=r(502),i=r(655);function a(e){e.cancelToken&&e.cancelToken.throwIfRequested()}e.exports=function(e){return a(e),e.headers=e.headers||{},e.data=o.call(e,e.data,e.headers,e.transformRequest),e.headers=n.merge(e.headers.common||{},e.headers[e.method]||{},e.headers),n.forEach(["delete","get","head","post","put","patch","common"],(function(t){delete e.headers[t]})),(e.adapter||i.adapter)(e).then((function(t){return a(e),t.data=o.call(e,t.data,t.headers,e.transformResponse),t}),(function(t){return s(t)||(a(e),t&&t.response&&(t.response.data=o.call(e,t.response.data,t.response.headers,e.transformResponse))),Promise.reject(t)}))}},481:function(e){"use strict";e.exports=function(e,t,r,n,o){return e.config=t,r&&(e.code=r),e.request=n,e.response=o,e.isAxiosError=!0,e.toJSON=function(){return{message:this.message,name:this.name,description:this.description,number:this.number,fileName:this.fileName,lineNumber:this.lineNumber,columnNumber:this.columnNumber,stack:this.stack,config:this.config,code:this.code}},e}},185:function(e,t,r){"use strict";var n=r(867);e.exports=function(e,t){t=t||{};var r={},o=["url","method","data"],s=["headers","auth","proxy","params"],i=["baseURL","transformRequest","transformResponse","paramsSerializer","timeout","timeoutMessage","withCredentials","adapter","responseType","xsrfCookieName","xsrfHeaderName","onUploadProgress","onDownloadProgress","decompress","maxContentLength","maxBodyLength","maxRedirects","transport","httpAgent","httpsAgent","cancelToken","socketPath","responseEncoding"],a=["validateStatus"];function c(e,t){return n.isPlainObject(e)&&n.isPlainObject(t)?n.merge(e,t):n.isPlainObject(t)?n.merge({},t):n.isArray(t)?t.slice():t}function u(o){n.isUndefined(t[o])?n.isUndefined(e[o])||(r[o]=c(void 0,e[o])):r[o]=c(e[o],t[o])}n.forEach(o,(function(e){n.isUndefined(t[e])||(r[e]=c(void 0,t[e]))})),n.forEach(s,u),n.forEach(i,(function(o){n.isUndefined(t[o])?n.isUndefined(e[o])||(r[o]=c(void 0,e[o])):r[o]=c(void 0,t[o])})),n.forEach(a,(function(n){n in t?r[n]=c(e[n],t[n]):n in e&&(r[n]=c(void 0,e[n]))}));var p=o.concat(s).concat(i).concat(a),l=Object.keys(e).concat(Object.keys(t)).filter((function(e){return-1===p.indexOf(e)}));return n.forEach(l,u),r}},26:function(e,t,r){"use strict";var n=r(61);e.exports=function(e,t,r){var o=r.config.validateStatus;r.status&&o&&!o(r.status)?t(n("Request failed with status code "+r.status,r.config,null,r.request,r)):e(r)}},527:function(e,t,r){"use strict";var n=r(867),o=r(655);e.exports=function(e,t,r){var s=this||o;return n.forEach(r,(function(r){e=r.call(s,e,t)})),e}},655:function(e,t,r){"use strict";var n=r(867),o=r(16),s=r(481),i={"Content-Type":"application/x-www-form-urlencoded"};function a(e,t){!n.isUndefined(e)&&n.isUndefined(e["Content-Type"])&&(e["Content-Type"]=t)}var c,u={transitional:{silentJSONParsing:!0,forcedJSONParsing:!0,clarifyTimeoutError:!1},adapter:(("undefined"!=typeof XMLHttpRequest||"undefined"!=typeof process&&"[object process]"===Object.prototype.toString.call(process))&&(c=r(448)),c),transformRequest:[function(e,t){return o(t,"Accept"),o(t,"Content-Type"),n.isFormData(e)||n.isArrayBuffer(e)||n.isBuffer(e)||n.isStream(e)||n.isFile(e)||n.isBlob(e)?e:n.isArrayBufferView(e)?e.buffer:n.isURLSearchParams(e)?(a(t,"application/x-www-form-urlencoded;charset=utf-8"),e.toString()):n.isObject(e)||t&&"application/json"===t["Content-Type"]?(a(t,"application/json"),function(e,t,r){if(n.isString(e))try{return(0,JSON.parse)(e),n.trim(e)}catch(e){if("SyntaxError"!==e.name)throw e}return(0,JSON.stringify)(e)}(e)):e}],transformResponse:[function(e){var t=this.transitional,r=t&&t.silentJSONParsing,o=t&&t.forcedJSONParsing,i=!r&&"json"===this.responseType;if(i||o&&n.isString(e)&&e.length)try{return JSON.parse(e)}catch(e){if(i){if("SyntaxError"===e.name)throw s(e,this,"E_JSON_PARSE");throw e}}return e}],timeout:0,xsrfCookieName:"XSRF-TOKEN",xsrfHeaderName:"X-XSRF-TOKEN",maxContentLength:-1,maxBodyLength:-1,validateStatus:function(e){return e>=200&&e<300},headers:{common:{Accept:"application/json, text/plain, */*"}}};n.forEach(["delete","get","head"],(function(e){u.headers[e]={}})),n.forEach(["post","put","patch"],(function(e){u.headers[e]=n.merge(i)})),e.exports=u},849:function(e){"use strict";e.exports=function(e,t){return function(){for(var r=new Array(arguments.length),n=0;n<r.length;n++)r[n]=arguments[n];return e.apply(t,r)}}},327:function(e,t,r){"use strict";var n=r(867);function o(e){return encodeURIComponent(e).replace(/%3A/gi,":").replace(/%24/g,"$").replace(/%2C/gi,",").replace(/%20/g,"+").replace(/%5B/gi,"[").replace(/%5D/gi,"]")}e.exports=function(e,t,r){if(!t)return e;var s;if(r)s=r(t);else if(n.isURLSearchParams(t))s=t.toString();else{var i=[];n.forEach(t,(function(e,t){null!=e&&(n.isArray(e)?t+="[]":e=[e],n.forEach(e,(function(e){n.isDate(e)?e=e.toISOString():n.isObject(e)&&(e=JSON.stringify(e)),i.push(o(t)+"="+o(e))})))})),s=i.join("&")}if(s){var a=e.indexOf("#");-1!==a&&(e=e.slice(0,a)),e+=(-1===e.indexOf("?")?"?":"&")+s}return e}},303:function(e){"use strict";e.exports=function(e,t){return t?e.replace(/\/+$/,"")+"/"+t.replace(/^\/+/,""):e}},372:function(e,t,r){"use strict";var n=r(867);e.exports=n.isStandardBrowserEnv()?{write:function(e,t,r,o,s,i){var a=[];a.push(e+"="+encodeURIComponent(t)),n.isNumber(r)&&a.push("expires="+new Date(r).toGMTString()),n.isString(o)&&a.push("path="+o),n.isString(s)&&a.push("domain="+s),!0===i&&a.push("secure"),document.cookie=a.join("; ")},read:function(e){var t=document.cookie.match(new RegExp("(^|;\\s*)("+e+")=([^;]*)"));return t?decodeURIComponent(t[3]):null},remove:function(e){this.write(e,"",Date.now()-864e5)}}:{write:function(){},read:function(){return null},remove:function(){}}},793:function(e){"use strict";e.exports=function(e){return/^([a-z][a-z\d\+\-\.]*:)?\/\//i.test(e)}},268:function(e){"use strict";e.exports=function(e){return"object"==typeof e&&!0===e.isAxiosError}},985:function(e,t,r){"use strict";var n=r(867);e.exports=n.isStandardBrowserEnv()?function(){var e,t=/(msie|trident)/i.test(navigator.userAgent),r=document.createElement("a");function o(e){var n=e;return t&&(r.setAttribute("href",n),n=r.href),r.setAttribute("href",n),{href:r.href,protocol:r.protocol?r.protocol.replace(/:$/,""):"",host:r.host,search:r.search?r.search.replace(/^\?/,""):"",hash:r.hash?r.hash.replace(/^#/,""):"",hostname:r.hostname,port:r.port,pathname:"/"===r.pathname.charAt(0)?r.pathname:"/"+r.pathname}}return e=o(window.location.href),function(t){var r=n.isString(t)?o(t):t;return r.protocol===e.protocol&&r.host===e.host}}():function(){return!0}},16:function(e,t,r){"use strict";var n=r(867);e.exports=function(e,t){n.forEach(e,(function(r,n){n!==t&&n.toUpperCase()===t.toUpperCase()&&(e[t]=r,delete e[n])}))}},109:function(e,t,r){"use strict";var n=r(867),o=["age","authorization","content-length","content-type","etag","expires","from","host","if-modified-since","if-unmodified-since","last-modified","location","max-forwards","proxy-authorization","referer","retry-after","user-agent"];e.exports=function(e){var t,r,s,i={};return e?(n.forEach(e.split("\n"),(function(e){if(s=e.indexOf(":"),t=n.trim(e.substr(0,s)).toLowerCase(),r=n.trim(e.substr(s+1)),t){if(i[t]&&o.indexOf(t)>=0)return;i[t]="set-cookie"===t?(i[t]?i[t]:[]).concat([r]):i[t]?i[t]+", "+r:r}})),i):i}},713:function(e){"use strict";e.exports=function(e){return function(t){return e.apply(null,t)}}},875:function(e,t,r){"use strict";var n=r(593),o={};["object","boolean","number","function","string","symbol"].forEach((function(e,t){o[e]=function(r){return typeof r===e||"a"+(t<1?"n ":" ")+e}}));var s={},i=n.version.split(".");function a(e,t){for(var r=t?t.split("."):i,n=e.split("."),o=0;o<3;o++){if(r[o]>n[o])return!0;if(r[o]<n[o])return!1}return!1}o.transitional=function(e,t,r){var o=t&&a(t);function i(e,t){return"[Axios v"+n.version+"] Transitional option '"+e+"'"+t+(r?". "+r:"")}return function(r,n,a){if(!1===e)throw new Error(i(n," has been removed in "+t));return o&&!s[n]&&(s[n]=!0,console.warn(i(n," has been deprecated since v"+t+" and will be removed in the near future"))),!e||e(r,n,a)}},e.exports={isOlderVersion:a,assertOptions:function(e,t,r){if("object"!=typeof e)throw new TypeError("options must be an object");for(var n=Object.keys(e),o=n.length;o-- >0;){var s=n[o],i=t[s];if(i){var a=e[s],c=void 0===a||i(a,s,e);if(!0!==c)throw new TypeError("option "+s+" must be "+c)}else if(!0!==r)throw Error("Unknown option "+s)}},validators:o}},867:function(e,t,r){"use strict";var n=r(849),o=Object.prototype.toString;function s(e){return"[object Array]"===o.call(e)}function i(e){return void 0===e}function a(e){return null!==e&&"object"==typeof e}function c(e){if("[object Object]"!==o.call(e))return!1;var t=Object.getPrototypeOf(e);return null===t||t===Object.prototype}function u(e){return"[object Function]"===o.call(e)}function p(e,t){if(null!=e)if("object"!=typeof e&&(e=[e]),s(e))for(var r=0,n=e.length;r<n;r++)t.call(null,e[r],r,e);else for(var o in e)Object.prototype.hasOwnProperty.call(e,o)&&t.call(null,e[o],o,e)}e.exports={isArray:s,isArrayBuffer:function(e){return"[object ArrayBuffer]"===o.call(e)},isBuffer:function(e){return null!==e&&!i(e)&&null!==e.constructor&&!i(e.constructor)&&"function"==typeof e.constructor.isBuffer&&e.constructor.isBuffer(e)},isFormData:function(e){return"undefined"!=typeof FormData&&e instanceof FormData},isArrayBufferView:function(e){return"undefined"!=typeof ArrayBuffer&&ArrayBuffer.isView?ArrayBuffer.isView(e):e&&e.buffer&&e.buffer instanceof ArrayBuffer},isString:function(e){return"string"==typeof e},isNumber:function(e){return"number"==typeof e},isObject:a,isPlainObject:c,isUndefined:i,isDate:function(e){return"[object Date]"===o.call(e)},isFile:function(e){return"[object File]"===o.call(e)},isBlob:function(e){return"[object Blob]"===o.call(e)},isFunction:u,isStream:function(e){return a(e)&&u(e.pipe)},isURLSearchParams:function(e){return"undefined"!=typeof URLSearchParams&&e instanceof URLSearchParams},isStandardBrowserEnv:function(){return("undefined"==typeof navigator||"ReactNative"!==navigator.product&&"NativeScript"!==navigator.product&&"NS"!==navigator.product)&&"undefined"!=typeof window&&"undefined"!=typeof document},forEach:p,merge:function e(){var t={};function r(r,n){c(t[n])&&c(r)?t[n]=e(t[n],r):c(r)?t[n]=e({},r):s(r)?t[n]=r.slice():t[n]=r}for(var n=0,o=arguments.length;n<o;n++)p(arguments[n],r);return t},extend:function(e,t,r){return p(t,(function(t,o){e[o]=r&&"function"==typeof t?n(t,r):t})),e},trim:function(e){return e.trim?e.trim():e.replace(/^\s+|\s+$/g,"")},stripBOM:function(e){return 65279===e.charCodeAt(0)&&(e=e.slice(1)),e}}},538:function(e,t,r){"use strict";var n=window.wp.element,o=r(669).create({baseURL:RSSEndpoint.url,headers:{"content-type":"application/json","X-WP-Nonce":RSSEndpoint.nonce}});const{Component:s}=wp.element,{withDispatch:i,withSelect:a,select:c}=wp.data,{__:__}=wp.i18n,{compose:u}=wp.compose,{SelectControl:p,TextControl:l,PanelBody:d,PanelRow:f,RadioControl:h}=wp.components;class m extends s{constructor(e){super(...arguments),this.props=e,this.state={selectImport:[],selectContent:[],selectOrderBy:[]},this.importSelectChange=this.importSelectChange.bind(this),this.SelectedContentChange=this.SelectedContentChange.bind(this),this.updateMaxOutput=this.updateMaxOutput.bind(this),this.updateMaxWordsDescription=this.updateMaxWordsDescription.bind(this),this.selectedOrderChange=this.selectedOrderChange.bind(this)}getData(){var e=this;o.get("get-data",{}).then((function(){let{data:t={}}=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};e.setState({selectImport:t.imports,selectContent:t.content,selectOrderBy:t.order})}))}componentDidMount(){this.getData()}importSelectChange(e){this.props.updateSelectedBeitragType(this.props.selectedBeitragType=e)}SelectedContentChange(e){this.props.OnUpdateSelectedContent(this.props.selectedContent=e)}updateMaxOutput(e){this.props.OnUpdateOutputLimit(this.props.maxOutput=e)}updateMaxWordsDescription(e){this.props.OnUpdateDescriptionLimit(this.props.descriptionLimit=e)}selectedOrderChange(e){this.props.OnUpdateSelectedOrder(this.props.selectedOrder=e)}render(){const e=this.state.selectImport.map(((e,t)=>({label:e.label,value:e.value,key:t}))),t=(this.state.selectContent.map(((e,t)=>({label:e.label,value:e.value,key:t}))),this.state.selectOrderBy.map(((e,t)=>({label:e.label,value:e.value,key:t}))));return(0,n.createElement)(n.Fragment,null,(0,n.createElement)("div",{className:"import-select"},(0,n.createElement)(p,{label:__("RSS Import select","wp-rss-feed-importer"),onChange:e=>this.importSelectChange(e),options:e,value:this.props.selectedBeitragType})),(0,n.createElement)("div",{className:"import-select"},(0,n.createElement)(p,{label:__("Sort output","wp-rss-feed-importer"),onChange:e=>this.selectedOrderChange(e),options:t,value:this.props.selectedOrder})),(0,n.createElement)(l,{type:"number",label:__("Restrict output of imports","wp-rss-feed-importer"),onChange:e=>this.updateMaxOutput(e),value:this.props.maxOutput,help:__("0 = no limit","wp-rss-feed-importer")}))}}var g=window.wp.blockEditor,v=window.wp.i18n;const{Component:b}=wp.element,{registerBlockType:y}=wp.blocks,{PanelBody:w,SelectControl:O,PanelRow:x,TextControl:S,InputControl:C}=wp.components;y("rss/importer-block",{title:(0,v.__)("RSS Importer","wp-rss-feed-importer"),icon:()=>(0,n.createElement)("svg",{xmlns:"http://www.w3.org/2000/svg",width:"16",height:"16",fill:"currentColor",className:"bi bi-rss",viewBox:"0 0 16 16"},(0,n.createElement)("path",{d:"M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"}),(0,n.createElement)("path",{d:"M5.5 12a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm-3-8.5a1 1 0 0 1 1-1c5.523 0 10 4.477 10 10a1 1 0 1 1-2 0 8 8 0 0 0-8-8 1 1 0 0 1-1-1zm0 4a1 1 0 0 1 1-1 6 6 0 0 1 6 6 1 1 0 1 1-2 0 4 4 0 0 0-4-4 1 1 0 0 1-1-1z"})),className:"rss-importer-block",category:"media",keywords:[(0,v.__)(" Design BY Jens Wiecker","wp-rss-feed-importer"),(0,v.__)("Design","wp-rss-feed-importer")],attributes:{selectedBeitragType:{type:"string",default:""},selectedContent:{type:"string",default:""},descriptionLimit:{type:"string",default:"0"},maxOutput:{type:"string",default:"0"},selectedOrder:{type:"string",default:""}},edit:class extends b{constructor(e){super(...arguments),this.props=e,this.updateSelectedBeitragType=this.updateSelectedBeitragType.bind(this),this.OnUpdateOutputLimit=this.OnUpdateOutputLimit.bind(this),this.OnUpdateSelectedContent=this.OnUpdateSelectedContent.bind(this),this.OnUpdateDescriptionLimit=this.OnUpdateDescriptionLimit.bind(this),this.OnUpdateSelectedOrder=this.OnUpdateSelectedOrder.bind(this)}updateSelectedBeitragType(e){this.props.setAttributes({selectedBeitragType:e})}OnUpdateSelectedContent(e){this.props.setAttributes({selectedContent:e})}OnUpdateOutputLimit(e){if(Number(e)<0)return!1;this.props.setAttributes({maxOutput:e})}OnUpdateDescriptionLimit(e){if(Number(e)<0)return!1;this.props.setAttributes({descriptionLimit:e})}OnUpdateSelectedOrder(e){this.props.setAttributes({selectedOrder:e})}render(){return(0,n.createElement)(n.Fragment,null,(0,n.createElement)("div",{className:"block-lvg-gallery"},(0,n.createElement)("div",{className:"headline-border"},(0,n.createElement)("h5",{className:"video-headline"},(0,n.createElement)("span",{className:"lvg-color me-1"}," ",(0,v.__)("RSS","wp-rss-feed-importer")," ")," ",(0,v.__)("Import","wp-rss-feed-importer")))),(0,n.createElement)(g.InspectorControls,null,(0,n.createElement)(w,{title:(0,v.__)("RSS Import","wp-rss-feed-importer"),initialOpen:!0,className:"rss-import-block-panel-body"},(0,n.createElement)(m,{selectedBeitragType:this.props.attributes.selectedBeitragType,updateSelectedBeitragType:this.updateSelectedBeitragType,selectedOrder:this.props.attributes.selectedOrder,OnUpdateSelectedOrder:this.OnUpdateSelectedOrder,selectedContent:this.props.attributes.selectedContent,OnUpdateSelectedContent:this.OnUpdateSelectedContent,maxOutput:this.props.attributes.maxOutput,OnUpdateOutputLimit:this.OnUpdateOutputLimit,descriptionLimit:this.props.attributes.descriptionLimit,OnUpdateDescriptionLimit:this.OnUpdateDescriptionLimit}))))}},save(){return null}})},593:function(e){"use strict";e.exports=JSON.parse('{"name":"axios","version":"0.21.4","description":"Promise based HTTP client for the browser and node.js","main":"index.js","scripts":{"test":"grunt test","start":"node ./sandbox/server.js","build":"NODE_ENV=production grunt build","preversion":"npm test","version":"npm run build && grunt version && git add -A dist && git add CHANGELOG.md bower.json package.json","postversion":"git push && git push --tags","examples":"node ./examples/server.js","coveralls":"cat coverage/lcov.info | ./node_modules/coveralls/bin/coveralls.js","fix":"eslint --fix lib/**/*.js"},"repository":{"type":"git","url":"https://github.com/axios/axios.git"},"keywords":["xhr","http","ajax","promise","node"],"author":"Matt Zabriskie","license":"MIT","bugs":{"url":"https://github.com/axios/axios/issues"},"homepage":"https://axios-http.com","devDependencies":{"coveralls":"^3.0.0","es6-promise":"^4.2.4","grunt":"^1.3.0","grunt-banner":"^0.6.0","grunt-cli":"^1.2.0","grunt-contrib-clean":"^1.1.0","grunt-contrib-watch":"^1.0.0","grunt-eslint":"^23.0.0","grunt-karma":"^4.0.0","grunt-mocha-test":"^0.13.3","grunt-ts":"^6.0.0-beta.19","grunt-webpack":"^4.0.2","istanbul-instrumenter-loader":"^1.0.0","jasmine-core":"^2.4.1","karma":"^6.3.2","karma-chrome-launcher":"^3.1.0","karma-firefox-launcher":"^2.1.0","karma-jasmine":"^1.1.1","karma-jasmine-ajax":"^0.1.13","karma-safari-launcher":"^1.0.0","karma-sauce-launcher":"^4.3.6","karma-sinon":"^1.0.5","karma-sourcemap-loader":"^0.3.8","karma-webpack":"^4.0.2","load-grunt-tasks":"^3.5.2","minimist":"^1.2.0","mocha":"^8.2.1","sinon":"^4.5.0","terser-webpack-plugin":"^4.2.3","typescript":"^4.0.5","url-search-params":"^0.10.0","webpack":"^4.44.2","webpack-dev-server":"^3.11.0"},"browser":{"./lib/adapters/http.js":"./lib/adapters/xhr.js"},"jsdelivr":"dist/axios.min.js","unpkg":"dist/axios.min.js","typings":"./index.d.ts","dependencies":{"follow-redirects":"^1.14.0"},"bundlesize":[{"path":"./dist/axios.min.js","threshold":"5kB"}]}')}},r={};function n(e){var o=r[e];if(void 0!==o)return o.exports;var s=r[e]={exports:{}};return t[e](s,s.exports,n),s.exports}n.m=t,e=[],n.O=function(t,r,o,s){if(!r){var i=1/0;for(p=0;p<e.length;p++){r=e[p][0],o=e[p][1],s=e[p][2];for(var a=!0,c=0;c<r.length;c++)(!1&s||i>=s)&&Object.keys(n.O).every((function(e){return n.O[e](r[c])}))?r.splice(c--,1):(a=!1,s<i&&(i=s));if(a){e.splice(p--,1);var u=o();void 0!==u&&(t=u)}}return t}s=s||0;for(var p=e.length;p>0&&e[p-1][2]>s;p--)e[p]=e[p-1];e[p]=[r,o,s]},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},function(){var e={826:0,431:0};n.O.j=function(t){return 0===e[t]};var t=function(t,r){var o,s,i=r[0],a=r[1],c=r[2],u=0;if(i.some((function(t){return 0!==e[t]}))){for(o in a)n.o(a,o)&&(n.m[o]=a[o]);if(c)var p=c(n)}for(t&&t(r);u<i.length;u++)s=i[u],n.o(e,s)&&e[s]&&e[s][0](),e[s]=0;return n.O(p)},r=self.webpackChunkwp_rss_importer_block=self.webpackChunkwp_rss_importer_block||[];r.forEach(t.bind(null,0)),r.push=t.bind(null,r.push.bind(r))}();var o=n.O(void 0,[431],(function(){return n(538)}));o=n.O(o)}();