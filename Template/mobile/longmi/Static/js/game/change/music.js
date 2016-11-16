var bgm;
window.Music = function(src,btn){
	var switcher = $(btn);
	var ifOpen = true;
	
	bgm = new Audio();
	bgm.src = src;
	bgm.loop = "loop";
	bgm.play();
	switcher.on("click",function(){
		if(ifOpen){
			ifOpen = false;
			$(this).attr("src",""+imgpath+"yue2.png").removeClass("yinyuetubiao");
			bgm.pause();
		}else{
			ifOpen = true;
			$(this).attr("src",""+imgpath+"yue.png").addClass("yinyuetubiao");
			bgm.play();
		}
	});
}

// Music("./music/bg.mp3","#yinyuetubiao");//背景音乐
