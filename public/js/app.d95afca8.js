(function(){var e={6289:function(e,t,r){"use strict";r.d(t,{O:function(){return s}});var n=r(6431);class s extends n.UQ{index(){return this.get("exchange")}tickerIndex(e){return this.get(`exchange/${e}/ticker`)}intervalIndex(e){return this.get(`exchange/${e}/interval`)}symbolShow(e,t){return this.get(`exchange/${e}/symbol/${t}`)}}},2836:function(e,t,r){"use strict";r.d(t,{N:function(){return s}});var n=r(6285);class s{constructor(e){this.app=e,this.config=e.config.globalProperties.$config,this.drivers={},this.extended={}}getDefaultDriver(){return"default"}extend(e,t){switch(typeof e){case"string":this.extended[e]=t;break;case"object":Object.keys(e).forEach((t=>this.extended[t]=e[t]));break}return this}driver(e=null){return null==e&&(e=this.getDefaultDriver()),e in this.drivers?this.drivers[e]:this.drivers[e]=this.createDriver(e)}createDriver(e){return(0,n.Pj)(this.createCustomDriver(e),(t=>t||(0,n.Pj)(this.createExtendedDriver(e),(e=>e||this.createDefaultDriver()))))}createCustomDriver(e){const t="create"+n.Bd.studly(e);return t in this?this[t]():null}createExtendedDriver(e){return e in this.extended?this.extended[e]():null}createDefaultDriver(){throw"Driver does not exist."}}},6285:function(e,t,r){"use strict";r.d(t,{Eu:function(){return o},Pj:function(){return i},Bd:function(){return a},qn:function(){return s}});class n{constructor(){this.snakeCache={},this.camelCache={},this.studlyCache={}}ctypeLower(e){return/^[a-z]+$/.test(e)}lcfirst(e){return e.charAt(0).toLowerCase()+e.substr(1)}strncmp(e,t,r){return e=e.substring(0,r),t=t.substring(0,r),e===t?0:e>t?1:-1}ucwords(e,t="\\s\\t\\r\\n\\f\\v"){return e.replace(new RegExp("(^(.)|["+t+"]+(.))","g"),(e=>e.toUpperCase()))}after(e,t){if(""===t)return e;const r=e.indexOf(t);return-1===r?e:e.substr(r+t.length)}afterLast(e,t){if(""===t)return e;const r=e.lastIndexOf(t);return-1===r?e:e.substr(r+t.length)}before(e,t){return""===t?e:e.split(t)[0]}beforeLast(e,t){if(""===t)return e;const r=e.lastIndexOf(t);return-1===r?e:e.substr(0,r)}between(e,t,r){return""===t||""===r?e:this.beforeLast(this.after(e,t),r)}camel(e){return e in this.camelCache?this.camelCache[e]:this.camelCache[e]=this.lcfirst(this.studly(e))}contains(e,t){return"string"===typeof t&&(t=[t]),t.some((t=>""!==t&&-1!==e.indexOf(t)))}containsAll(e,t){return t.every((t=>this.contains(e,t)))}endsWith(e,t){return"string"===typeof t&&(t=[t]),t.some((t=>""!==t&&null!==t&&e.substr(-t.length)===t))}snake(e,t="_"){const r=e;if(r in this.snakeCache){if(t in this.snakeCache[r])return this.snakeCache[r][t]}else this.snakeCache[r]={};return this.ctypeLower(e)||(e=this.ucwords(e).replace(/\s+/u,""),e=e.replace(/(.)(?=[A-Z])/u,"$1"+t).toLowerCase()),this.snakeCache[r][t]=e}startsWith(e,t){return"string"===typeof t&&(t=[t]),t.some((t=>""!==t&&null!==t&&0===this.strncmp(e,t,t.length)))}studly(e){const t=e;return t in this.studlyCache?this.studlyCache[t]:(e=this.ucwords(e.replace(/[-_]/g," ")),this.studlyCache[t]=e.replace(/\s+/g,""))}}function s(e,t=null){return t&&t(e),e}function i(e,t=null){return t?t(e):e}function o(e,t,r=null){const n=t.split(".");let s;while(s=n.shift()){if("object"!==typeof e||!(s in e)){e=null;break}e=e[s]}return null==e?r:e}const a=new n},733:function(e,t,r){"use strict";r.d(t,{c:function(){return n}});class n{constructor(e){this.app=e}}},7714:function(e,t,r){"use strict";r.d(t,{G:function(){return l}});var n=r(2836),s=r(6265),i=r.n(s);class o extends n.N{getDefaultDriver(){return this.config?.get("services.request","starter")}createDefaultDriver(){return i().create(this.config?.get("services.requests.starter"))}}var a=r(733);class c extends a.c{constructor(e){super(e),this.registered={}}make(e,t=null){const r=e.name;return r in this.registered||(this.registered[r]=t||(t=>new e(t))),this.create(r)}create(e){return this.registered[e](this.app)}}class u extends c{create(e){return"function"===typeof this.registered[e]?this.registered[e]=super.create(e):this.registered[e]}}function l(e={}){return{install(t){const r=new u(t);t.config.globalProperties.$request=new o(t).extend(e),t.config.globalProperties.$service=e=>r.make(e)}}}},6431:function(e,t,r){"use strict";r.d(t,{UP:function(){return h},wS:function(){return l},C:function(){return n},UQ:function(){return u}});r(7714);class n{constructor(e){this.error=e,this.message=null,this.messages=[],this.parseError()}parseError(){}getMessage(){return this.message}getMessages(){return this.messages}}var s=r(733);class i extends s.c{constructor(e){super(e),this.request=this.app.config.globalProperties.$request.driver(this.driver())}driver(){return null}response(e){return e}}var o=r(6285),a=r(6265);class c extends n{parseError(){this.error instanceof a.AxiosError&&!this.parseErrorResponse(this.error.response.data)&&!this.parseErrorCode(this.error.code)&&this.parseErrorMessage(this.error.message)}parseErrorResponse(e){return!!e?._message&&(this.message=e._message,this.messages=e._messages,!0)}parseErrorCode(e){return!1}parseErrorMessage(e){return this.message=e,this.messages=[e],!0}}class u extends i{constructor(e){super(e),this.doneCallback=null,this.errorCallback=null,this.wrapErrorCallback=null,this.alwaysCallback=null}driver(){return"starter"}done(e){return this.doneCallback=e,this}error(e){return this.errorCallback=e,this}always(e){return this.alwaysCallback=e,this}wrapError(e){return this.wrapErrorCallback=e,this}responseThen(e,t=null,r=null,n=null,s=null){return e.then((e=>{if(!e.data._status)throw e;const r=e.data._data;return t&&t(r),r})).catch((e=>(e instanceof a.AxiosError||(e=new a.AxiosError("Server Error","ERR_SERVER",e.config,e.request,e)),e=s?s(e):new c(e),r&&r(e),e))).then((e=>(n&&n(e),e)))}response(e){return(0,o.qn)(this.responseThen(e,this.doneCallback,this.errorCallback,this.alwaysCallback,this.wrapErrorCallback),(()=>{this.doneCallback=null,this.errorCallback=null,this.alwaysCallback=null,this.wrapErrorCallback=null}))}get(e,t={}){return this.response(this.request.get(e,{params:t}))}post(e,t={}){return this.response(this.request.post(e,t))}}class l extends u{ping(){return this.get("ping")}}class h extends u{encrypt(e){return this.post("encrypt",{data:e})}decrypt(e){return this.post("decrypt",{data:e})}}},8415:function(e,t,r){"use strict";r.d(t,{l:function(){return Pe}});var n={};r.r(n),r.d(n,{app:function(){return o},log:function(){return a},services:function(){return u}});var s={};r.r(s),r.d(s,{account:function(){return I},exchange:function(){return F},ping:function(){return q}});var i=r(9242);const o={name:"Crypto Bot",client:"default",static:!("VUE_APP_SERVICE_URL"in{NODE_ENV:"production",VUE_APP_CLIENT:"default",VUE_APP_NAME:"Crypto Bot",VUE_APP_SERVICE_URL:"api",BASE_URL:"/"}),ping:{expired_in:6e4},routes:{connection_lost:{name:"connection_lost"}}},a={default:"console",drivers:{console:{}}};var c=r(3436);const u={request:"starter",requests:{starter:{baseURL:(()=>{if("VUE_APP_SERVICE_URL"in{NODE_ENV:"production",VUE_APP_CLIENT:"default",VUE_APP_NAME:"Crypto Bot",VUE_APP_SERVICE_URL:"api",BASE_URL:"/"}){let e="api";return/^https?:\/\//.test(e)?e:(e=(0,c.trim)(e,"/"),window.location.origin+(e?"/"+e:""))}return null})()}}};var l=r(6285);class h{constructor(e){Object.keys(e).forEach((t=>this[t]=e[t]))}get(e,t=null){return(0,l.Eu)(this,e,t)}}function f(e){return{install(t){t.config.globalProperties.$config=new h(e)}}}const d=f(n);var p=r(2836);class g{constructor(e={}){this.options=e}info(e,t,...r){}}class v extends g{info(e,t,...r){console.info(e+":",t,...r)}}class m extends p.N{getDefaultDriver(){return this.config?.get("log.default","console")}createDefaultDriver(){return new v(this.config?.get("log.drivers.console",{}))}}function b(e={}){return{install(t){const r=new m(t).extend(e);t.config.globalProperties.$logging=r,t.config.globalProperties.$log=r.driver()}}}const w=b();var y=r(7714);const E=(0,y.G)();class x{encrypt(e){return e}decrypt(e){return e}}var C=r(6431);class k extends x{constructor(e){super(),this.app=e}async encrypt(e){const t=await this.app.config.globalProperties.$service(C.UP).encrypt(e);if(t instanceof C.C)throw"Encrypt failed.";return t.encrypted}async decrypt(e){const t=await this.app.config.globalProperties.$service(C.UP).decrypt(e);if(t instanceof C.C)throw"Decrypt failed.";return t.decrypted}}class _ extends p.N{getDefaultDriver(){return this.config?.get("encryption.default","starter")}createDefaultDriver(){return new k(this.app)}}function P(e={}){return{install(t){const r=new _(t).extend(e);t.config.globalProperties.$encryption=r,t.config.globalProperties.$encryptor=r.driver()}}}const D=P();class ${constructor(e){this.encryptor=e}put(e,t,r={}){return this.putRaw(e,this.toValue(t,r),r)}putRaw(e,t,r={}){return this}toValue(e,t={}){return t.encrypt&&(e=this.encryptor.encrypt(JSON.stringify(e))),e}flash(e,t,r={}){return r.flash=!0,this.put(e,t,r)}has(e){return!1}keep(e){if(this.has(e)){const t=this.fromValue(this.getRaw(e));t.options.keep=!0,this.put(e,t.value,t.options)}return this}get(e,t=null){if(!this.has(e))return t;const r=this.getRaw(e);return this.fromValue(e,r.value,r.options)}fromValue(e,t,r={}){return r.expired&&(new Date).getTime()>r.expired?(this.remove(e),null):(r.flash&&(r.keep?delete r.keep:this.remove(e)),r.encrypt&&(t=JSON.parse(this.encryptor.decrypt(t))),t)}getRaw(e){return null}remove(e){return this}}class A extends ${constructor(e){super(e),this.handler=window.localStorage}putRaw(e,t,r={}){return this.handler.setItem(e,JSON.stringify({value:t,options:r})),this}has(e){return e in this.handler}getRaw(e){return JSON.parse(this.handler.getItem(e))}remove(e){return this.handler.removeItem(e),this}}class R extends p.N{getDefaultDriver(){return this.config?.get("storage.default","local")}createDefaultDriver(){return new A(this.app.config.globalProperties.$encryptor)}}function O(e={}){return{install(t){const r=new R(t).extend(e);t.config.globalProperties.$storageManager=r,t.config.globalProperties.$storage=r.driver()}}}const N=O();class U{set(e,t,r=null){return this}get(e,t=null){return t}}class j extends U{constructor(e){super(),this.storage=e}set(e,t,r=null){const n={};return r&&(n.expired=(new Date).getTime()+r),this.storage.put(e,t,n),this}get(e,t=null){return this.storage.get(e,t)}remove(e){return this.storage.remove(e),this}}class S extends p.N{getDefaultDriver(){return this.config?.get("cache.default","storage")}createDefaultDriver(){return new j(this.app.config.globalProperties.$storage)}}function V(e={}){return{install(t){const r=new S(t).extend(e);t.config.globalProperties.$cacheManager=r,t.config.globalProperties.$cache=r.driver()}}}const L=V();var T=r(7139);const q={namespaced:!0,state:()=>({available:!1,expiredAt:0}),mutations:{setAvailable(e,t){e.available=t,e.expiredAt=(new Date).getTime()+Pe.$config.get("app.ping.expired_in",6e4),Pe.$cache.set("ping",{available:e.available,expiredAt:e.expiredAt})},setFromCache(e){const t=Pe.$cache.get("ping");Pe.$log.info("model","ping.setFromCache",t),t&&(e.available=t.available,e.expiredAt=t.expiredAt)},reset(e){e.available=!1,e.expiredAt=0,Pe.$cache.remove("ping")}},actions:{async ping(e){return e.getters.expired&&e.commit("setAvailable",!(await Pe.$service(C.wS).ping()instanceof C.C)),e.getters.available}},getters:{available:e=>e.available,expired:e=>(new Date).getTime()>e.expiredAt}},I={namespaced:!0,state:()=>({account:null}),mutations:{setAccount(e,t){e.account=t}},actions:{setAccount(e,t){e.commit("setAccount",t)}},getters:{account:e=>e.account}};var M=r(6289);const F={namespaced:!0,state:()=>({all:[]}),mutations:{setAll(e,t){e.all=t}},actions:{all(e){const t=e.getters.all;return t.length?new Promise((e=>e(t))):Pe.$service(M.O).done((t=>(e.commit("setAll",t.exchanges),t))).index()}},getters:{all:e=>e.all}},B=(0,T.MT)({state:{},getters:{},mutations:{},actions:{},modules:s});var W=r(678);class J{constructor(){this.middlewares=[]}collect(e){return this.middlewares=[],e.matched.forEach((e=>{"middleware"in e.meta&&this.middlewares.push(...e.meta.middleware)})),this}before(e,t,r,n){const s=(i=null)=>{if(null==i){const i=this.middlewares.shift();if(i){const n=new i;if(e in n)return void n[e](t,r,s)}n()}else n(i)};s()}beforeEach(e,t,r){this.before("beforeEach",e,t,r)}beforeResolve(e,t,r){this.before("beforeResolve",e,t,r)}after(e,t,r){const n=()=>{const s=this.middlewares.shift();if(s){const i=new s;if(e in i)return void i[e](t,r,n)}n()};n()}afterEach(e,t){this.after("afterEach",e,t)}}const Z=new J;function z(e={}){return"history"in e||(e.history=(0,W.PO)("/")),(0,l.qn)((0,W.p7)(e),(function(e){e.beforeEach(((e,t,r)=>{Z.collect(e).beforeEach(e,t,r)})),e.beforeResolve(((e,t,r)=>{Z.collect(e).beforeResolve(e,t,r)})),e.afterEach(((e,t)=>{Z.collect(e).afterEach(e,t)}))}))}class G{beforeEach(e,t,r){r()}beforeResolve(e,t,r){r()}afterEach(e,t){}}let H=!0;class Q extends G{beforeEach(e,t,r){H&&(this.restoreFromCache(),this.restoreFromCookie()),H=!1,r()}restoreFromCache(){Pe.$log.info("middleware","fresh.restoreFromCache"),Pe.$store.commit("ping/setFromCache")}restoreFromCookie(){Pe.$log.info("middleware","fresh.restoreFromCookie")}}class K extends G{async beforeEach(e,t,r){if(Pe.$log.info("middleware","ping.beforeEach"),!Pe.$config.get("app.static")&&!await Pe.$store.dispatch("ping/ping")){const t=Pe.$config.app.routes.connection_lost;if(e.name!==t.name)return void r(t)}r()}}const Y=[Q,K];var X=r(3396),ee=r(2268);const te={class:"container"},re={class:"text-center my-5"},ne={class:"card"},se={class:"card-header"},ie={class:"nav nav-tabs card-header-tabs"},oe={class:"nav-item"},ae={class:"card-body"};function ce(e,t,r,n,s,i){const o=(0,X.up)("router-link"),a=(0,X.up)("router-view");return(0,X.wg)(),(0,X.iD)("div",te,[(0,X._)("h1",re,(0,ee.zw)(s.appName),1),(0,X._)("div",ne,[(0,X._)("div",se,[(0,X._)("ul",ie,[((0,X.wg)(!0),(0,X.iD)(X.HY,null,(0,X.Ko)(s.menu,(e=>((0,X.wg)(),(0,X.iD)("li",oe,[(0,X.Wm)(o,{class:(0,ee.C_)(["nav-link",{active:i.currentRouteName===e.to.name}]),to:e.to},{default:(0,X.w5)((()=>[(0,X.Uk)((0,ee.zw)(e.label),1)])),_:2},1032,["class","to"])])))),256))])]),(0,X._)("div",ae,[(0,X.Wm)(a)])])])}var ue={name:"Base",data(){return{appName:this.$config.app.name,menu:[{label:"Holding",to:{name:"holding"}},{label:"Trading",to:{name:"trading"}}]}},computed:{currentRouteName(){return this.$route.name}}},le=r(89);const he=(0,le.Z)(ue,[["render",ce]]);var fe=he;const de={class:"error"};function pe(e,t,r,n,s,i){const o=(0,X.up)("router-view");return(0,X.wg)(),(0,X.iD)("div",de,[(0,X.Wm)(o)])}var ge={name:"BaseError"};const ve=(0,le.Z)(ge,[["render",pe]]);var me=ve;const be=[{path:"/",component:fe,meta:{middleware:Y},children:[{path:"error",component:me,children:[{path:"connection-lost",name:"connection_lost",component:()=>r.e(771).then(r.bind(r,4771))},{path:"404",name:"not_found",component:()=>r.e(129).then(r.bind(r,6129))}]},{path:"/",name:"root",redirect:"holding"},{path:"holding",name:"holding",component:()=>r.e(767).then(r.bind(r,8767))},{path:"trading",name:"trading",component:()=>r.e(921).then(r.bind(r,7921))},{path:":pathMatch(.*)*",component:()=>r.e(129).then(r.bind(r,6129))}]}],we=z({routes:be}),ye={config:d,log:w,service:E,encryption:D,storage:N,cache:L,store:B,router:we};function Ee(e,t){const r=(0,X.up)("router-view");return(0,X.wg)(),(0,X.j4)(r)}const xe={},Ce=(0,le.Z)(xe,[["render",Ee]]);var ke=Ce;const _e=(0,i.ri)(ke),Pe=(0,l.qn)(_e,(function(e){Object.keys(ye).forEach((t=>e.use(ye[t])))})).mount("#app")},866:function(e,t,r){"use strict";var n=r(8415);n.l.$log.info("app","created",n.l)},2158:function(){},8076:function(){}},t={};function r(n){var s=t[n];if(void 0!==s)return s.exports;var i=t[n]={exports:{}};return e[n](i,i.exports,r),i.exports}r.m=e,function(){var e=[];r.O=function(t,n,s,i){if(!n){var o=1/0;for(l=0;l<e.length;l++){n=e[l][0],s=e[l][1],i=e[l][2];for(var a=!0,c=0;c<n.length;c++)(!1&i||o>=i)&&Object.keys(r.O).every((function(e){return r.O[e](n[c])}))?n.splice(c--,1):(a=!1,i<o&&(o=i));if(a){e.splice(l--,1);var u=s();void 0!==u&&(t=u)}}return t}i=i||0;for(var l=e.length;l>0&&e[l-1][2]>i;l--)e[l]=e[l-1];e[l]=[n,s,i]}}(),function(){r.n=function(e){var t=e&&e.__esModule?function(){return e["default"]}:function(){return e};return r.d(t,{a:t}),t}}(),function(){r.d=function(e,t){for(var n in t)r.o(t,n)&&!r.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})}}(),function(){r.f={},r.e=function(e){return Promise.all(Object.keys(r.f).reduce((function(t,n){return r.f[n](e,t),t}),[]))}}(),function(){r.u=function(e){return"js/"+e+"."+{129:"95d90241",733:"2c80cebc",767:"df73c2ac",771:"9a6d8348",921:"553c0868"}[e]+".js"}}(),function(){r.miniCssF=function(e){}}(),function(){r.g=function(){if("object"===typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(e){if("object"===typeof window)return window}}()}(),function(){r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}}(),function(){var e={},t="starter:";r.l=function(n,s,i,o){if(e[n])e[n].push(s);else{var a,c;if(void 0!==i)for(var u=document.getElementsByTagName("script"),l=0;l<u.length;l++){var h=u[l];if(h.getAttribute("src")==n||h.getAttribute("data-webpack")==t+i){a=h;break}}a||(c=!0,a=document.createElement("script"),a.charset="utf-8",a.timeout=120,r.nc&&a.setAttribute("nonce",r.nc),a.setAttribute("data-webpack",t+i),a.src=n),e[n]=[s];var f=function(t,r){a.onerror=a.onload=null,clearTimeout(d);var s=e[n];if(delete e[n],a.parentNode&&a.parentNode.removeChild(a),s&&s.forEach((function(e){return e(r)})),t)return t(r)},d=setTimeout(f.bind(null,void 0,{type:"timeout",target:a}),12e4);a.onerror=f.bind(null,a.onerror),a.onload=f.bind(null,a.onload),c&&document.head.appendChild(a)}}}(),function(){r.r=function(e){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})}}(),function(){r.p="/"}(),function(){var e={143:0};r.f.j=function(t,n){var s=r.o(e,t)?e[t]:void 0;if(0!==s)if(s)n.push(s[2]);else{var i=new Promise((function(r,n){s=e[t]=[r,n]}));n.push(s[2]=i);var o=r.p+r.u(t),a=new Error,c=function(n){if(r.o(e,t)&&(s=e[t],0!==s&&(e[t]=void 0),s)){var i=n&&("load"===n.type?"missing":n.type),o=n&&n.target&&n.target.src;a.message="Loading chunk "+t+" failed.\n("+i+": "+o+")",a.name="ChunkLoadError",a.type=i,a.request=o,s[1](a)}};r.l(o,c,"chunk-"+t,t)}},r.O.j=function(t){return 0===e[t]};var t=function(t,n){var s,i,o=n[0],a=n[1],c=n[2],u=0;if(o.some((function(t){return 0!==e[t]}))){for(s in a)r.o(a,s)&&(r.m[s]=a[s]);if(c)var l=c(r)}for(t&&t(n);u<o.length;u++)i=o[u],r.o(e,i)&&e[i]&&e[i][0](),e[i]=0;return r.O(l)},n=self["webpackChunkstarter"]=self["webpackChunkstarter"]||[];n.forEach(t.bind(null,0)),n.push=t.bind(null,n.push.bind(n))}();var n=r.O(void 0,[998],(function(){return r(866)}));n=r.O(n)})();
//# sourceMappingURL=app.d95afca8.js.map