
//左侧滚动条

(function($){
$.zUI = $.zUI || {}
$.zUI.emptyFn = function(){};
$.zUI.asWidget = [];
$.zUI.addWidget = function(sName,oSefDef){
	$.zUI.asWidget.push(sName);
	var w = $.zUI[sName] = $.zUI[sName] || {};
	var sPrefix = "zUI" + sName
	w.sFlagName = sPrefix;
	w.sEventName = sPrefix + "Event";
	w.sOptsName = sPrefix + "Opts";
	w.__creator = $.zUI.emptyFn;
	w.__destroyer = $.zUI.emptyFn;
	$.extend(w,oSefDef);
	w.fn = function(ele,opts){
		var jqEle = $(ele);
		jqEle.data(w.sOptsName,$.extend({},w.defaults,opts));
		if(jqEle.data(w.sFlagName)){
			return;
		}
		jqEle.data(w.sFlagName,true);
		w.__creator(ele);
		jqEle.on(jqEle.data(w.sEventName));
	};
	w.unfn = function(ele){
		w.__destroyer(ele);
		var jqEle = $(ele);//移除监听事件
		if(jqEle.data(w.sFlagName)){
			jqEle.off(jqEle.data(w.sEventName));
			jqEle.data(w.sFlagName,false);
		}
	}
	
}
$.zUI.addWidget("draggable",{
	defaults:{
		bOffsetParentBoundary:false,
		oBoundary:null,
		fnComputePosition:null
	},
	__creator:function(ele){
		var jqEle = $(ele);
		jqEle.data($.zUI.draggable.sEventName,{
		mousedown:function(ev){
		var jqThis = $(this);
		var opts = jqThis.data($.zUI.draggable.sOptsName);
		jqThis.trigger("draggable.start");
		var iOffsetX = ev.pageX - this.offsetLeft;
		var iOffsetY = ev.pageY - this.offsetTop;
		function fnMouseMove (ev) {
			var oPos = {};
			if(opts.fnComputePosition){
				oPos = opts.fnComputePosition(ev,iOffsetX,iOffsetY);
			}else{
				oPos.iLeft = ev.pageX - iOffsetX;
				oPos.iTop = ev.pageY - iOffsetY;
			}
			var oBoundary = opts.oBoundary;
			if(opts.bOffsetParentBoundary){界
				var eParent = jqThis.offsetParent()[0];
				oBoundary = {};
				oBoundary.iMinLeft = 0;
				oBoundary.iMinTop = 0;
				oBoundary.iMaxLeft = eParent.clientWidth - jqThis.outerWidth();
				oBoundary.iMaxTop = eParent.clientHeight - jqThis.outerHeight();
			}
		
			if(oBoundary){
				oPos.iLeft = oPos.iLeft < oBoundary.iMinLeft ? oBoundary.iMinLeft : oPos.iLeft;
				oPos.iLeft = oPos.iLeft > oBoundary.iMaxLeft ? oBoundary.iMaxLeft : oPos.iLeft;
				oPos.iTop = oPos.iTop < oBoundary.iMinTop ? oBoundary.iMinTop : oPos.iTop;
				oPos.iTop = oPos.iTop > oBoundary.iMaxTop ? oBoundary.iMaxTop : oPos.iTop;
			}
			jqThis.css({left:oPos.iLeft,top:oPos.iTop});
			ev.preventDefault();
			jqThis.trigger("draggable.move");
		}
		var oEvent = {
			mousemove:fnMouseMove,
			mouseup:function(){
				$(document).off(oEvent);
				jqThis.trigger("draggable.stop");
			}
		};
		$(document).on(oEvent);
	}});
	}
});
$.zUI.addWidget("panel",{
	defaults : {
			iWheelStep:16,
			sBoxClassName:"zUIpanelScrollBox",
			sBarClassName:"zUIpanelScrollBar"
	},
	__creator:function(ele){
		var jqThis = $(ele);
		//如果是static定位，加上relative定位
		if(jqThis.css("position") === "static"){
			jqThis.css("position","relative");
		}
		jqThis.css("overflow","hidden");
		var jqChild = jqThis.children(":first");
		if(jqChild.length){
			jqChild.css({top:0,position:"absolute"});
		}else{
			return;
		}
		var opts = jqThis.data($.zUI.panel.sOptsName);
		var jqScrollBox = $("<div style='position:absolute;display:none;line-height:0;'></div>");
		jqScrollBox.addClass(opts.sBoxClassName);
		var jqScrollBar= $("<div style='position:absolute;display:none;line-height:0;'></div>");
		jqScrollBar.addClass(opts.sBarClassName);
		jqScrollBox.appendTo(jqThis);
		jqScrollBar.appendTo(jqThis);
		opts.iTop = parseInt(jqScrollBox.css("top"));
		opts.iWidth = jqScrollBar.width();
		opts.iRight = parseInt(jqScrollBox.css("right"));
		jqScrollBar.on("draggable.move",function(){
			var opts = jqThis.data($.zUI.panel.sOptsName);
			fnScrollContent(jqScrollBox,jqScrollBar,jqThis,jqChild,opts.iTop,0);
		});
		var oEvent ={
			mouseenter:function(){
				fnFreshScroll();
				jqScrollBox.css("display","block");
				jqScrollBar.css("display","block");
			},
			mouseleave:function(){
				jqScrollBox.css("display","none");
				jqScrollBar.css("display","none");
			}
		};
		var sMouseWheel = "mousewheel";
		if(!("onmousewheel" in document)){
			sMouseWheel = "DOMMouseScroll";
		}
		oEvent[sMouseWheel] = function(ev){
			var opts = jqThis.data($.zUI.panel.sOptsName);
			var iWheelDelta = 1;
			ev.preventDefault();//阻止默认事件
			ev = ev.originalEvent;//获取原生的event
			if(ev.wheelDelta){
					iWheelDelta = ev.wheelDelta/120;
			}else{
					iWheelDelta = -ev.detail/3;
			}
			var iMinTop = jqThis.innerHeight() - jqChild.outerHeight();
			//外面比里面高，不需要响应滚动
			if(iMinTop>0){
				jqChild.css("top",0);
				return;
			}
			var iTop = parseInt(jqChild.css("top"));
			var iTop = iTop + opts.iWheelStep*iWheelDelta;
			iTop = iTop > 0 ? 0 : iTop;
			iTop = iTop < iMinTop ? iMinTop : iTop;
			jqChild.css("top",iTop);
			fnScrollContent(jqThis,jqChild,jqScrollBox,jqScrollBar,0,opts.iTop);
		}
		//记录添加事件
		jqThis.data($.zUI.panel.sEventName,oEvent);
		//跟随滚动函数
		function fnScrollContent(jqWrapper,jqContent,jqFollowWrapper,jqFlollowContent,iOffset1,iOffset2){
			var opts = jqThis.data($.zUI.panel.sOptsName);
			var rate = (parseInt(jqContent.css("top"))-iOffset1)/(jqContent.outerHeight()-jqWrapper.innerHeight())//卷起的比率
			var iTop = (jqFlollowContent.outerHeight()-jqFollowWrapper.innerHeight())*rate + iOffset2;
			jqFlollowContent.css("top",iTop);
		}
	
		//刷新滚动条
		function fnFreshScroll(){

			var opts = jqThis.data($.zUI.panel.sOptsName);
			var iScrollBoxHeight = jqThis.innerHeight()-2*opts.iTop;
			var iRate = jqThis.innerHeight()/jqChild.outerHeight();
			var iScrollBarHeight = iScrollBarHeight = Math.round(iRate*iScrollBoxHeight);
			//如果比率大于等于1，不需要滚动条,自然也不需要添加拖拽事件
			if(iRate >= 1){
				jqScrollBox.css("height",0);
				jqScrollBar.css("height",0);
				return;
			}
			jqScrollBox.css("height",iScrollBoxHeight);
			jqScrollBar.css("height",iScrollBarHeight);
			//计算拖拽边界，添加拖拽事件
			var oBoundary = {iMinTop:opts.iTop};
			oBoundary.iMaxTop = iScrollBoxHeight - Math.round(iRate*iScrollBoxHeight)+opts.iTop;
			oBoundary.iMinLeft = jqThis.innerWidth() - opts.iWidth - opts.iRight;
			oBoundary.iMaxLeft = oBoundary.iMinLeft;
			fnScrollContent(jqThis,jqChild,jqScrollBox,jqScrollBar,0,opts.iTop);
			jqScrollBar.draggable({oBoundary:oBoundary});
		}
	},
		__destroyer:function(ele){
			var jqEle = $(ele);
			if(jqEle.data($.zUI.panel.sFlagName)){
				var opts = jqEle.data($.zUI.panel.sOptsName);
				jqEle.children("."+opts.sBoxClassName).remove();
				jqEle.children("."+opts.sBarClassName).remove();
			}
	}
});

$.each($.zUI.asWidget,function(i,widget){
	unWidget = "un"+widget;
	var w = {};
	w[widget] = function(args){
			this.each(function(){
			$.zUI[widget].fn(this,args);
		});
		return this;
	};
	w[unWidget] = function(){
			this.each(function(){
			$.zUI[widget].unfn(this);
		});
		return this;
	}
	$.fn.extend(w);
});
})(jQuery);

$(function() {
    $("#roll").panel({
        iWheelStep: 32
    });
});
$(function() {
    $("#roll").height($(document).height() - 200); //计算高度
    $(window).resize(function() { //当浏览器窗口大小变化时执行
        $("#roll").height($(document).height() - 200);
    });
});

$(function() {
    $('.m33').find('li').each(function(i) {
        $(this).click(function() {
            $('.m33').find('li').removeClass('m33_hover');
            $(this).addClass('m33_hover');
        })
    })
})



//选项卡

jQuery(".slideTxtBox").slide({ trigger: "click" });
jQuery(".slideTxtBox1").slide({ trigger: "click" });



//日期

// $(function() {
//     var currYear = (new Date()).getFullYear();
//     var opt = {};
//     opt.date = {
//         preset: 'date'
//     };
//     opt.datetime = {
//         preset: 'datetime'
//     };
//     opt.time = {
//         preset: 'time'
//     };
//     opt.
// default = {
//         theme: 'android-ics light',
//         //皮肤样式
//         display: 'modal',
//         //显示方式
//         mode: 'scroller',
//         //日期选择模式
//         dateFormat: 'yyyy-mm-dd',
//         lang: 'zh',
//         showNow: true,
//         nowText: "今天",
//         startYear: currYear - 10,
//         //开始年份
//         endYear: currYear + 10 //结束年份
//     };
//
//     $("#appDate,#appDate1").mobiscroll($.extend(opt['date'], opt['default']));
//     var optDateTime = $.extend(opt['datetime'], opt['default']);
//     var optTime = $.extend(opt['time'], opt['default']);
//     $("#appDateTime").mobiscroll(optDateTime).datetime(optDateTime);
//     $("#appTime").mobiscroll(optTime).time(optTime);
// });