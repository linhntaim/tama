"use strict";(self["webpackChunkstarter"]=self["webpackChunkstarter"]||[]).push([[924],{1924:function(e,r,s){s.r(r),s.d(r,{default:function(){return $}});var t=s(3396),i=s(9242),a=s(7139);const o={class:"register"},n=(0,t._)("h2",{class:"mb-3"},"Register",-1),l={class:"col-md-6 col-lg-4 col-xl-3 mx-auto"},d={class:"mb-3"},m={key:0,class:"invalid-feedback text-start"},g=(0,t._)("br",null,null,-1),u={class:"mb-3"},c={key:0,class:"invalid-feedback text-start"},p=(0,t._)("br",null,null,-1),w={class:"mb-3"},v={key:0,class:"invalid-feedback text-start"},h=(0,t._)("br",null,null,-1),_={class:"mb-3"},b=["disabled"];function f(e,r,s,f,k,y){return(0,t.wg)(),(0,t.iD)("div",o,[n,(0,t._)("form",{class:"row",onSubmit:r[4]||(r[4]=(0,i.iM)(((...e)=>y.onSubmit&&y.onSubmit(...e)),["prevent"]))},[(0,t._)("div",l,[(0,t._)("div",d,[(0,t.wy)((0,t._)("input",{class:(0,a.C_)(["form-control",{"is-invalid":!!k.error.validation.name}]),"onUpdate:modelValue":r[0]||(r[0]=e=>y.name=e),type:"text",name:"name",placeholder:"Name",required:""},null,2),[[i.nr,y.name]]),k.error.validation.name?((0,t.wg)(),(0,t.iD)("div",m,[((0,t.wg)(!0),(0,t.iD)(t.HY,null,(0,t.Ko)(k.error.validation.name,(e=>((0,t.wg)(),(0,t.iD)(t.HY,null,[(0,t.Uk)((0,a.zw)(e),1),g],64)))),256))])):(0,t.kq)("",!0)]),(0,t._)("div",u,[(0,t.wy)((0,t._)("input",{class:(0,a.C_)(["form-control",{"is-invalid":!!k.error.validation.email}]),"onUpdate:modelValue":r[1]||(r[1]=e=>y.email=e),type:"email",name:"email",placeholder:"Email",required:""},null,2),[[i.nr,y.email]]),k.error.validation.email?((0,t.wg)(),(0,t.iD)("div",c,[((0,t.wg)(!0),(0,t.iD)(t.HY,null,(0,t.Ko)(k.error.validation.email,(e=>((0,t.wg)(),(0,t.iD)(t.HY,null,[(0,t.Uk)((0,a.zw)(e),1),p],64)))),256))])):(0,t.kq)("",!0)]),(0,t._)("div",w,[(0,t.wy)((0,t._)("input",{class:(0,a.C_)(["form-control",{"is-invalid":!!k.error.validation.password}]),"onUpdate:modelValue":r[2]||(r[2]=e=>y.password=e),type:"password",name:"password",placeholder:"Password",required:""},null,2),[[i.nr,y.password]]),k.error.validation.password?((0,t.wg)(),(0,t.iD)("div",v,[((0,t.wg)(!0),(0,t.iD)(t.HY,null,(0,t.Ko)(k.error.validation.password,(e=>((0,t.wg)(),(0,t.iD)(t.HY,null,[(0,t.Uk)((0,a.zw)(e),1),h],64)))),256))])):(0,t.kq)("",!0)]),(0,t._)("div",_,[(0,t.wy)((0,t._)("input",{class:"form-control","onUpdate:modelValue":r[3]||(r[3]=e=>y.passwordConfirmation=e),type:"password",name:"password_confirmation",placeholder:"Password Confirmation",required:""},null,512),[[i.nr,y.passwordConfirmation]])]),(0,t._)("button",{class:"btn btn-primary",disabled:k.loading._,type:"submit"},"Submit",8,b)])],32)])}var k=s(65),y={name:"Index",data(){return{loading:{_:!1},error:{messages:[],validation:{}}}},computed:{...(0,k.Se)({registerProgressing:"register/progressing"}),name:{get(){return this.$store.state.register.name},set(e){this.$store.state.register.name=e}},email:{get(){return this.$store.state.register.email},set(e){this.$store.state.register.email=e}},password:{get(){return this.$store.state.register.password},set(e){this.$store.state.register.password=e}},passwordConfirmation:{get(){return this.$store.state.register.passwordConfirmation},set(e){this.$store.state.register.passwordConfirmation=e}}},created(){this.registerProgressing||this.registerReset()},methods:{...(0,k.nv)({register:"register/register"}),...(0,k.OI)({registerSetProgressing:"register/setProgressing",registerReset:"register/reset"}),onSubmit(){this.loading._=!0,this.register({login_url:this.$url.route({name:"login"})}).then((()=>{this.loading._=!1,this.registerSetProgressing(!0),this.$router.push({name:"register.success"})})).catch((e=>{this.loading._=!1,this.error.messages=e.messages,e.data&&"validation"in e.data&&(this.error.validation=e.data.validation)}))}}},C=s(89);const D=(0,C.Z)(y,[["render",f]]);var $=D}}]);
//# sourceMappingURL=924.7e2b4eff.js.map