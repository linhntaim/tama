"use strict";(self["webpackChunk_linhntaim_vue_3_starter"]=self["webpackChunk_linhntaim_vue_3_starter"]||[]).push([[515],{3007:function(s,r,o){o.r(r),o.d(r,{default:function(){return P}});var t=o(3396),a=o(9242),e=o(7139);const i={class:"forgot-password"},n=(0,t._)("h2",{class:"mb-3"},"Forgot Password",-1),l={class:"col-md-6 col-lg-4 col-xl-3 mx-auto"},d={class:"mb-3"},g={key:0,class:"invalid-feedback text-start"},u=(0,t._)("br",null,null,-1),m=["disabled"];function c(s,r,o,c,w,f){return(0,t.wg)(),(0,t.iD)("div",i,[n,(0,t._)("form",{class:"row",onSubmit:r[1]||(r[1]=(0,a.iM)(((...s)=>f.onSubmit&&f.onSubmit(...s)),["prevent"]))},[(0,t._)("div",l,[(0,t._)("div",d,[(0,t.wy)((0,t._)("input",{class:(0,e.C_)(["form-control",{"is-invalid":!!w.error.validation.email}]),"onUpdate:modelValue":r[0]||(r[0]=s=>f.email=s),type:"email",name:"email",placeholder:"Email",required:""},null,2),[[a.nr,f.email]]),w.error.validation.email?((0,t.wg)(),(0,t.iD)("div",g,[((0,t.wg)(!0),(0,t.iD)(t.HY,null,(0,t.Ko)(w.error.validation.email,(s=>((0,t.wg)(),(0,t.iD)(t.HY,null,[(0,t.Uk)((0,e.zw)(s),1),u],64)))),256))])):(0,t.kq)("",!0)]),(0,t._)("button",{class:"btn btn-primary",disabled:w.loading._,type:"submit"},"Submit",8,m)])],32)])}var w=o(65),f={name:"Index",data(){return{loading:{_:!1},error:{messages:[],validation:{}}}},computed:{...(0,w.Se)({forgotPasswordProgressing:"forgotPassword/progressing"}),email:{get(){return this.$store.state.forgotPassword.email},set(s){this.$store.state.forgotPassword.email=s}}},created(){this.forgotPasswordProgressing||this.forgotPasswordReset()},methods:{...(0,w.nv)({forgotPassword:"forgotPassword/forgotPassword"}),...(0,w.OI)({forgotPasswordSetProgressing:"forgotPassword/setProgressing",forgotPasswordReset:"forgotPassword/reset"}),onSubmit(){this.loading._=!0,this.forgotPassword({reset_url:this.$url.route({name:"password.reset",params:{token:"{token}"}})}).then((()=>{this.loading._=!1,this.forgotPasswordSetProgressing(!0),this.$router.push({name:"password.request.success"})})).catch((s=>{this.loading._=!1,this.error.messages=s.messages,s.data&&"validation"in s.data&&(this.error.validation=s.data.validation)}))}}},h=o(89);const _=(0,h.Z)(f,[["render",c]]);var P=_}}]);
//# sourceMappingURL=view-auth-forgot-password-index.b4c07ad7.js.map