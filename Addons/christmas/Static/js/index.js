
var isAnimating = true;

var now = { row:1, col:1 }, last = { row:0, col:0};
const towards = { up:1, right:2, down:3, left:4};
var page_limit = 7;


var page6_click_number1 = 0;
var page6_click_number2 = 0;
var page6_click_number3 = 0;

var page_6_go = false;


(function(){

	s=window.innerHeight/500;
	ss=250*(1-s);

	$('.wrap').css('-webkit-transform','scale('+s+','+s+') translate(0px,-'+ss+'px)');

	document.addEventListener('touchmove',function(event){
		event.preventDefault(); },false);

	$(document).swipeUp(function(){
		if (isAnimating) return;
		last.row = now.row;
		last.col = now.col;
		if (last.row != page_limit) { now.row = last.row+1; now.col = 1; pageMove(towards.up);}
	});

	$(document).swipeDown(function(){
		if (isAnimating) return;
		last.row = now.row;
		last.col = now.col;
		if (last.row!=1) { now.row = last.row-1; now.col = 1; pageMove(towards.down);}
	});

	$(document).swipeLeft(function(){
		if (isAnimating) return;
		last.row = now.row;
		last.col = now.col;
		if (last.row!=1) { now.row = last.row-1; now.col = 1; pageMove(towards.down);}
	});

	$(document).swipeRight(function(){
		if (isAnimating) return;
		last.row = now.row;
		last.col = now.col;
		if (last.row!=1) { now.row = last.row-1; now.col = 1; pageMove(towards.down);}
	});
	$(document).swipeRight(function(){
		if (isAnimating) return;
		last.row = now.row;
		last.col = now.col;
		if (last.row!=1) { now.row = last.row-1; now.col = 1; pageMove(towards.down);}
	});

	function pageMove(tw){
		var lastPage = ".page-"+last.row+"-"+last.col,
			nowPage = ".page-"+now.row+"-"+now.col;

		switch(tw) {
			case towards.up:
				outClass = 'pt-page-moveToTop';
				inClass = 'pt-page-moveFromBottom';
				break;
			case towards.right:
				outClass = 'pt-page-moveToRight';
				inClass = 'pt-page-moveFromLeft';
				break;
			case towards.down:
				outClass = 'pt-page-moveToBottom';
				inClass = 'pt-page-moveFromTop';
				break;
			case towards.left:
				outClass = 'pt-page-moveToLeft';
				inClass = 'pt-page-moveFromRight';
				break;
		}
		isAnimating = true;
		$(nowPage).removeClass("hide");
		$(nowPage+" img").each(function(){
			var flash_name = $(this).data("flash");
			var after_flash_name = $(this).attr("after-flash");
			if( flash_name != undefined && flash_name != "" && after_flash_name != undefined && after_flash_name != ""){
				$(this).removeClass(after_flash_name);
				$(this).addClass(flash_name);
			}
		});


		/**
		 * 特殊页面特殊处理
         */
		if(  now.row == 6 ){
			page6_click_number1 = 0;
			page6_click_number2 = 0;
			page6_click_number3 = 0;
			// page_limit = 6;
			$(".page-6-1 .img_1").show();
			$(".page-6-1 .img_2").show();
			$(".page-6-1 .img_3").show();
			$(".page-6-1 .img_4").show();
			$(".page-6-1 .img_5").show();
			$(".page-6-1 .img_6").show();
			$(".page-6-1 .img_7").show();
			$("#page6_mask").hide();
			page_6_go = false;
		}




		$(lastPage).addClass(outClass);
		$(nowPage).addClass(inClass);

		setTimeout(function(){
			$(lastPage).removeClass('page-current');
			$(lastPage).removeClass(outClass);
			$(lastPage).addClass("hide");
			$(lastPage).find("img").addClass("hide");

			$(nowPage).addClass('page-current');
			$(nowPage).removeClass(inClass);
			$(nowPage).find("img").removeClass("hide");

			isAnimating = false;
		},600);
	}
	$(".page-7-1 a").click(function(){
		page_limit = 9;
		if (isAnimating) return;
		last.row = now.row;
		last.col = now.col;
		now.row = last.row+1; now.col = 1; pageMove(towards.up);
	});


	$("#page6_mask").click(function(){
		if (isAnimating) return;
		last.row = now.row;
		last.col = now.col;
		now.row = last.row+1; now.col = 1; pageMove(towards.up);
	});
})();



var screenHeight = 0;

/**
 * 兼容
 */
function __pic_init(){
	screenHeight = $(".page-1-1").height();
	var screenWidth = $(".page-1-1").width();
	var blHeight = screenWidth * 568/320;
	$(".page_div img").each(function(){
		var myTop = $(this).css("top");
		// var myLeft = $(this).css("left");
		var myWidth = $(this).css("width");
		var myHeight = $(this).height();
		var myWidth2 = $(this).width();
		var reCat = /^((\d+\.?\d*)|(\d*\.\d+))\%$/;
		if(reCat.test(myTop)){
			var newTop = screenHeight * toPoint(myTop);
			$(this).css('top',newTop+ 'px');
		}
		// if(reCat.test(myLeft)){
		// 	var newLeft = myWidth2 * toPoint(myLeft);
		// 	$(this).css('left',newLeft+ 'px');
		// }
		if(reCat.test(myWidth)){
			var newWidth = screenWidth * toPoint(myWidth);
			$(this).css('width',newWidth+ 'px');
			var newHeight = myHeight *(screenHeight/blHeight);
			$(this).css('height',newHeight+ 'px');
		}else{
			if( myHeight >0){
				var newHeight = myHeight *(screenHeight/blHeight);
				$(this).css('height',newHeight+ 'px');
			}
		}

		var flash_name = $(this).data("flash");
		if( flash_name != undefined && flash_name != ""){
			var after_flash_name = $(this).attr("after-flash");
			if( after_flash_name != undefined && after_flash_name != ""){
				$(this).bind("webkitAnimationEnd",function(){
					$(this).removeClass(flash_name);
					$(this).addClass(after_flash_name);
				});
			}
			$(this).addClass(flash_name);
		}
		var touch_flash_name = $(this).attr("touch-flash");
		if( touch_flash_name != undefined && touch_flash_name != ""){
			$(this).bind("touchstart",function(){
				my_touch_flash(this);
			});
		}

		var touch_mask_text = $(this).attr("touch-mask");
		if( touch_mask_text != undefined && touch_mask_text != ""){
			$(this).bind("click",function(){
				showMask(touch_mask_text);
			});
		}
	});
	$(".page_div .page ").each(function(){
		$(this).addClass("hide");
		$(this).css('background-size',screenWidth+ 'px '+screenHeight+ 'px');
	});
};
/**
 * 百分比点
 * @param percent
 * @returns {string|*|XML|void}
 */
function toPoint(percent){
	var str=percent.replace("%","");
	str= str/100;
	return str;
}

/**
 * 点击的动画
 * @param obj
 */
function my_touch_flash(obj){
	var touch_flash_name = $(obj).attr("touch-flash");
	$(obj).addClass(touch_flash_name);
	setTimeout( function () {$(obj).removeClass(touch_flash_name);},500);
}

/**
 * loading
 */
$(function(){
	var t_img; // 定时器
	var isLoad = true; // 控制变量
	var imgNum=$('img').length;
	var imgNumNow = 0;
// 判断图片加载状况，加载完成后回调
	isImgLoad(function(){
		// 加载完成
		__pic_init();
		loadind_ending = true;
		isAnimating = false;
		$("#loading p").html("轻触屏幕任意位置开始！");
		$(".page-1-1").removeClass("hide");
	});

	/**
	 * 事件定义
	 */
	$("#mask").click(function(){
		hideMask();
	});
	$("#mask p").click(function(){
		hideMask();
	});
	$("#loading").bind("touchstart",function(){
		if(loadind_ending){
			$(this).hide();
			// PlayAudioBg();
		}
	});
	$("#loading p").bind("touchstart",function(){
		if(loadind_ending){
			$(this).hide();
			// PlayAudioBg();
		}
	});

	// 判断图片加载的函数
	function isImgLoad(callback){
		// 注意我的图片类名都是cover，因为我只需要处理cover。其它图片可以不管。
		// 查找所有封面图，迭代处理
		$('img').each(function(index,element){
			// 找到为0就将isLoad设为false，并退出each
			if(this.height === 0){
				isLoad = false;
				return false;
			}
			if( index - 1 >= imgNumNow){
				imgNumNow = index+1;
				var load_number = ((imgNumNow/imgNum).toFixed(2) * 100).toFixed(0);
				$("#loadingNumber").html(load_number+"%");
			}
		});
		// 为true，没有发现为0的。加载完毕
		if(isLoad){
			clearTimeout(t_img); // 清除定时器
			// 回调函数
			callback();
			// 为false，因为找到了没有加载完成的图，将调用定时器递归
		}else{
			isLoad = true;
			t_img = setTimeout(function(){
				isImgLoad(callback); // 递归扫描
			},500); // 我这里设置的是500毫秒就扫描一次，可以自己调整
		}
	}
});


/**
 * 弹框显示
 * @param text
 */
function showMask( text ){

	/**
	 * 特殊情况特殊处理
     */
	if( text == "好漂亮的叶子~" ){
		page6_click_number1 = 1;
	}
	if( text == "我就知道圣诞奶奶忽悠我，<br>这是个空盒子~" ){
		page6_click_number2 = 1;
	}
	if( text == "叮叮当~叮叮当~" ){
		page6_click_number3 = 1;
	}

	$("#mask p").html("");
	$("#mask").show();
	isAnimating = true;
	$("#mask p").html(text);
	var mask_p_w =$("#mask p").width();
	var mask_p_h =$("#mask p").height();
	$("#mask p").css("margin-top",((screenHeight-mask_p_h)/2)+"px");
	$("#mask p").css('background-size',mask_p_w+ 'px '+mask_p_h+ 'px');
}

/**
 * 弹框隐藏
 */
function hideMask(){
	isAnimating = false;
	$("#mask").hide();

	/**
	 * 特殊情况特殊处理
	 */
	if( page6_click_number1 == 1 && page6_click_number2 == 1 && page6_click_number3 == 1){
		// page_limit = 7;
		$(".page-6-1 .img_1").hide();
		$(".page-6-1 .img_2").hide();
		$(".page-6-1 .img_3").hide();
		$(".page-6-1 .img_4").hide();
		$(".page-6-1 .img_5").hide();
		$(".page-6-1 .img_6").hide();
		$(".page-6-1 .img_7").hide();
		$("#page6_mask").show();
		$("#page6_mask img").each(function(){
			var mask_flash_name = $(this).attr("mask-flash");
			if( mask_flash_name != undefined && mask_flash_name != ""){
				$(this).removeClass(mask_flash_name);
				$(this).addClass(mask_flash_name);
			}
		});
		$("#page6_mask span").each(function(){
			var mask_flash_name = $(this).attr("mask-flash");
			if( mask_flash_name != undefined && mask_flash_name != ""){
				$(this).removeClass(mask_flash_name);
				$(this).addClass(mask_flash_name);
				$(this).bind("webkitAnimationEnd",function(){
					$(this).removeClass(mask_flash_name);
					$(this).addClass("pt-page-4-1");
				});
			}
		});
		var mask_p_w =$("#page6_mask p").width();
		var mask_p_h =$("#page6_mask p").height();
		$("#page6_mask p").css("margin-top",((screenHeight-mask_p_h)/2)+"px");
		$("#page6_mask p").css('background-size',mask_p_w+ 'px '+mask_p_h+ 'px');
	}
}


