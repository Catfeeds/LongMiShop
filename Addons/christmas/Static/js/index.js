(function(){

var now = { row:1, col:1 }, last = { row:0, col:0};
const towards = { up:1, right:2, down:3, left:4};
var isAnimating = false;

s=window.innerHeight/500;
ss=250*(1-s);

$('.wrap').css('-webkit-transform','scale('+s+','+s+') translate(0px,-'+ss+'px)');

document.addEventListener('touchmove',function(event){
	event.preventDefault(); },false);

$(document).swipeUp(function(){
	if (isAnimating) return;
	last.row = now.row;
	last.col = now.col;
	if (last.row != 9) { now.row = last.row+1; now.col = 1; pageMove(towards.up);}
})

$(document).swipeDown(function(){
	if (isAnimating) return;
	last.row = now.row;
	last.col = now.col;
	if (last.row!=1) { now.row = last.row-1; now.col = 1; pageMove(towards.down);}	
})

$(document).swipeLeft(function(){
	if (isAnimating) return;
	last.row = now.row;
	last.col = now.col;
	if (last.row!=1) { now.row = last.row-1; now.col = 1; pageMove(towards.down);}
})

$(document).swipeRight(function(){
	if (isAnimating) return;
	last.row = now.row;
	last.col = now.col;
	if (last.row!=1) { now.row = last.row-1; now.col = 1; pageMove(towards.down);}
})
$(document).swipeRight(function(){
	if (isAnimating) return;
	last.row = now.row;
	last.col = now.col;
	if (last.row!=1) { now.row = last.row-1; now.col = 1; pageMove(towards.down);}
})
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

})();

$(function(){
	var screenHeight = $(".page-1-1").height();
	var screenWidth = $(".page-1-1").width();
	$(".page_div .page .wrap img").each(function(){
		var myTop = $(this).css("top");
		var myWidth = $(this).css("width");
		var myWidth2 = $(this).width();
		var myHeight = $(this).height();
		var reCat = /^((\d+\.?\d*)|(\d*\.\d+))\%$/;
		if(reCat.test(myTop)){
			var newTop = screenHeight * toPoint(myTop);
			$(this).css('top',newTop+ 'px');
		}
		if(reCat.test(myWidth)){
			var newWidth = screenWidth * toPoint(myWidth);
			var newHeight =newWidth *(myHeight/myWidth2);
			// var newHeight = myHeight * toPoint(myWidth);
			$(this).css('width',newWidth+ 'px');
			$(this).css('height',newHeight+ 'px');
		}
	});
	$(".page_div .page ").each(function(){
		$(this).css('background-size',screenWidth+ 'px,'+screenHeight+ 'px');
	});
});
function toPoint(percent){
	var str=percent.replace("%","");
	str= str/100;
	return str;
}