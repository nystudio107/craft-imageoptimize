/*!
 * @project        imageoptimize
 * @name           field.js
 * @author         Andrew Welch
 * @build          Sat Jan 16 2021 03:56:20 GMT+0000 (Coordinated Universal Time)
 * @copyright      Copyright (c) 2021 ©2020 nystudio107.com
 *
 */
(self.webpackChunkimageoptimize=self.webpackChunkimageoptimize||[]).push([[708],{2547:function(t,n,i){i(9826),i(4916),i(5306),function(t,n,i,e){var a="ImageOptimizeOptimizedImages",o={};function l(n,i){this.element=n,this.options=t.extend({},o,i),this._defaults=o,this._name=a,this.init()}l.prototype={init:function(n){t((function(){}))}},t.fn[a]=function(n){return this.each((function(){t.data(this,"plugin_"+a)||t.data(this,"plugin_"+a,new l(this,n))}))}}(jQuery,window,document),Craft.OptimizedImagesInput=Garnish.Base.extend({id:null,inputNamePrefix:null,inputIdPrefix:null,$container:null,$blockContainer:null,$addBlockBtnContainer:null,$addBlockBtnGroup:null,$addBlockBtnGroupBtns:null,blockSort:null,blockSelect:null,init:function(t,n){this.id=t,this.inputNamePrefix=n,this.inputIdPrefix=Craft.formatInputId(this.inputNamePrefix),this.$container=$("#"+this.id),this.$blockContainer=this.$container.children(".variant-blocks"),this.$addBlockBtnContainer=this.$container.children(".buttons"),this.$addBlockBtnGroup=this.$addBlockBtnContainer.children(".btngroup"),this.$addBlockBtnGroupBtns=this.$addBlockBtnGroup.children(".btn");var i=this;this.$blockContainer.find("> > .actions > .settings").each((function(t,n){var e,a=$(n);(e=a.data("menubtn")?a.data("menubtn"):new Garnish.MenuBtn(n)).menu.settings.onOptionSelect=$.proxy((function(t){this.onMenuOptionSelect(t,e)}),i)}));var e=this.$blockContainer.children();this.blockSort=new Garnish.DragSort(e,{handle:"> .actions > .move",axis:"y",collapseDraggees:!0,magnetStrength:4,helperLagBase:1.5,helperOpacity:.9,onSortChange:$.proxy((function(){this.resetVariantBlockOrder()}),this)}),this.addListener(this.$addBlockBtnGroupBtns,"click",(function(t){$(t.target).data("type");this.addVariantBlock(null)})),this.addAspectRatioHandlers(),this.reIndexVariants()},onMenuOptionSelect:function(t,n){var i=$(t),e=n.$btn.closest(".matrixblock");switch(i.data("action")){case"add":this.addVariantBlock(e);break;case"delete":i.hasClass("disabled")||this.deleteVariantBlock(e)}},getHiddenBlockCss:function(t){return{opacity:0,marginBottom:-t.outerHeight()}},reIndexVariants:function(){this.$blockContainer=this.$container.children(".variant-blocks");var t=this.$blockContainer.children();t.each((function(t,n){var i=t,e=$(n).find("div .field, label, input, select");$(e).each((function(t,n){var e=$(n).attr("id");e&&(e=e.replace(/\-([0-9]+)\-/g,"-"+i+"-"),$(n).attr("id",e)),(e=$(n).attr("for"))&&(e=e.replace(/\-([0-9]+)\-/g,"-"+i+"-"),$(n).attr("for",e)),(e=$(n).attr("name"))&&(e=e.replace(/\[([0-9]+)\]/g,"["+i+"]"),$(n).attr("name",e))}))}));var n=!1;1==t.length&&(n=!0),t.find("> .actions > .settings").each((function(t,i){var e,a=$(i);a.data("menubtn")&&(e=a.data("menubtn"),$menuItem=$(e.menu.$menuList[1]),n?$menuItem.find("> li > a").addClass("disabled").disable():$menuItem.find("> li > a").removeClass("disabled").enable())}))},addAspectRatioHandlers:function(){this.addListener($(".lightswitch"),"click",(function(t){$(t.target).closest(".matrixblock").find(".io-aspect-ratio-wrapper").slideToggle()})),this.addListener($(".io-select-ar-box"),"click",(function(t){var n,i,e=$(t.target),a=$(t.target).data("x"),o=$(t.target).data("y"),l=$(t.target).data("custom");(i=e.closest(".matrixblock")).find(".io-select-ar-box").each((function(t,n){$(n).removeClass("io-selected-ar-box")})),e.addClass("io-selected-ar-box"),l?i.find(".io-custom-ar-wrapper").slideDown():(i.find(".io-custom-ar-wrapper").slideUp(),n=i.find("input")[2],$(n).val(a),n=i.find("input")[3],$(n).val(o))}))},addVariantBlock:function(t){var n=this;$block=$(this.$blockContainer.children()[0]).clone(),$block.find(".io-select-ar-box").each((function(t,n){0===t?$(n).addClass("io-selected-ar-box"):$(n).removeClass("io-selected-ar-box")})),$block.find(".io-custom-ar-wrapper").hide(),field=$block.find("input")[0],$(field).val(1200),field=$block.find("input")[1],$(field).val(1),field=$block.find("input")[2],$(field).val(16),field=$block.find("input")[3],$(field).val(9),field=$block.find("select")[0],$(field).val(82),field=$block.find("select")[1],$(field).val("jpg"),$block.css(this.getHiddenBlockCss($block)).velocity({opacity:1,"margin-bottom":10},"fast",$.proxy((function(){t?$block.insertBefore(t):$block.appendTo(this.$blockContainer),this.blockSort.addItems($block),this.addAspectRatioHandlers(),$block.find(".settings").each((function(t,i){var e,a,o=$(i);a=n.$container.find(".io-menu-clone > .menu").clone(),$(a).insertAfter(o),(e=new Garnish.MenuBtn(i)).menu.settings.onOptionSelect=$.proxy((function(t){n.onMenuOptionSelect(t,e)}),this)})),this.reIndexVariants()}),this))},deleteVariantBlock:function(t){var n=this;t.velocity(this.getHiddenBlockCss(t),"fast",$.proxy((function(){t.remove(),n.reIndexVariants()}),this))},resetVariantBlockOrder:function(){this.reIndexVariants()}})}},0,[[2547,666,216]]]);
//# sourceMappingURL=field.js.map