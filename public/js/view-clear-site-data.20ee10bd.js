"use strict";(self["webpackChunk_linhntaim_vue_3_starter"]=self["webpackChunk_linhntaim_vue_3_starter"]||[]).push([[105],{5793:function(e,s,o){o.r(s),o.d(s,{default:function(){return d}});var a=o(3396),t=o(7139);const l={class:"text-center"},r=(0,a._)("h2",null,"Clear site data",-1),n=(0,a._)("p",null,"Including session storage, local storage and cookies.",-1),i={key:0,class:"my-3"};function c(e,s,o,c,u,g){return(0,a.wg)(),(0,a.iD)("div",l,[r,n,(0,a._)("button",{class:"btn btn-danger",type:"button",onClick:s[0]||(s[0]=(...e)=>g.onClearClick&&g.onClearClick(...e))},"Clear"),u.logs.length?((0,a.wg)(),(0,a.iD)("pre",i,[(0,a._)("code",null,(0,t.zw)(g.lines),1)])):(0,a.kq)("",!0)])}var u={name:"ClearSiteData",data(){return{logs:[]}},computed:{lines(){return this.logs.join("\n")}},methods:{onClearClick(){this.logs=[],this.$nextTick((()=>{this.clearSessionStorage(),this.clearLocalStorage(),this.clearCookies()}))},clearSessionStorage(){this.logs.push("Clearing session storage ..."),window.sessionStorage.clear(),this.logs.push("Session storage was cleared.")},clearLocalStorage(){this.logs.push("Clearing local storage ..."),window.localStorage.clear(),this.logs.push("Local storage was cleared.")},clearCookies(){this.logs.push("Clearing cookies ..."),document.cookie.split(";").forEach((function(e){document.cookie=e.replace(/^ +/,"").replace(/=.*/,"=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/")})),this.logs.push("Cookies were cleared.")}}},g=o(89);const h=(0,g.Z)(u,[["render",c]]);var d=h}}]);
//# sourceMappingURL=view-clear-site-data.20ee10bd.js.map