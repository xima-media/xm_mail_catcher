define("TYPO3/CMS/XmMailCatcher/MailCatcher",["TYPO3/CMS/Backend/Modal","TYPO3/CMS/Backend/Severity","jquery"],((e,t,a)=>{return s={735:(e,t,a)=>{var s,n;s=[a,t,a(404),a(580),a(339)],n=function(e,t,a,s,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.XmMailCatcher=void 0,t.XmMailCatcher=new class{constructor(){this.bindListener()}bindListener(){a(".content-type-switches a").on("click",this.onContentTypeSwitchClick.bind(this)),a("button[data-delete]").on("click",this.onDeleteButtonClick.bind(this)),a("#delete-all-messages").on("click",this.onDeleteAllMessagesClick.bind(this)),a(".panel.panel-default .form-irre-header-body").on("click",this.onPanelClick.bind(this))}onPanelClick(e){e.preventDefault(),e.stopPropagation();const t=a(e.currentTarget).closest(".panel").find(".panel-collapse");t.hasClass("collapse")?t.removeClass("collapse"):t.addClass("collapse")}onDeleteAllMessagesClick(e){e.preventDefault();const t=this;s.confirm("Delete Messages","Are you sure, you want to delete all messages?",n.warning,[{text:"Yes, delete",btnClass:"btn-danger",trigger:function(){t.deleteAllMessages(),s.dismiss()}},{text:"No, abort",btnClass:"primary-outline",active:!0,trigger:function(){s.dismiss()}}])}deleteAllMessages(){const e=this,t=a(".panel[data-message-file]");a.ajax({url:TYPO3.settings.ajaxUrls.mailcatcher_delete_all}).done((function(a){if(a.success)return t.remove(),e.refreshMessageCount(),void top.TYPO3.Notification.success("Success","All messages have been deleted",3);top.TYPO3.Notification.error("Error","Could not delete messages",3)}))}refreshMessageCount(){const e=a(".panel[data-message-file]").length;a("*[data-message-count]").attr("data-message-count",e),a(".message-count").html(e.toString())}onDeleteButtonClick(e){e.preventDefault();const t=a(e.currentTarget).closest(".panel"),s=t.attr("data-message-file"),n=this,r=TYPO3.settings.ajaxUrls.mailcatcher_delete+"&"+a.param({messageFile:s});a.ajax({url:r}).done((function(e){if(e.success)return t.remove(),void n.refreshMessageCount();top.TYPO3.Notification.error("Error","Could not delete message",3)}))}onContentTypeSwitchClick(e){e.preventDefault();const t=a(e.currentTarget).attr("data-content-type"),s=a(e.currentTarget).attr("data-m-id");a('.content-type-switches a[data-m-id="'+s+'"]').addClass("btn-default").removeClass("btn-primary"),a('.content-type-switches a[data-m-id="'+s+'"][data-content-type="'+t+'"]').removeClass("btn-default").addClass("btn-primary"),a('.form-section[data-m-id="'+s+'"]').addClass("hidden"),a('.form-section[data-m-id="'+s+'"][data-content-type="'+t+'"]').removeClass("hidden");const n=a(e.currentTarget).closest(".panel");"false"===n.attr("data-html-loaded")&&this.loadHtmlMail(n.attr("data-message-file"))}loadHtmlMail(e){const t=TYPO3.settings.ajaxUrls.mailcatcher_html+"&"+a.param({messageFile:e});a.ajax({url:t}).done((function(t){const s=a("<iframe />").attr("frameBorder","0").attr("width","100%").attr("height","500px").attr("srcdoc",t.src);a('.panel[data-message-file="'+e+'"] .form-section[data-content-type="html"]').html(s)}))}}}.apply(t,s),void 0===n||(e.exports=n)},580:t=>{"use strict";t.exports=e},339:e=>{"use strict";e.exports=t},404:e=>{"use strict";e.exports=a}},n={},function e(t){var a=n[t];if(void 0!==a)return a.exports;var r=n[t]={exports:{}};return s[t](r,r.exports,e),r.exports}(735);var s,n}));