/**
 * 米粒游戏js
 */
var player_test;
var is_draw = false;
var is_first_load = true;

$(function(){
    $(".page_1 .ren").click(function(){
        player_test =  $(this).attr("data-test");
        $(".page_1").hide();
        $(".page_2").show();

        is_draw = true;

    });


    $("#game_over .ok").click(function(){
        var number =  $("#game_over input").val();
        if( number == "" ||  number <= 0 ){
            return;
        }
        $(this).hide();
        $("#game_over input").css("width","70%");
        $("#game_over .result").show();
        alert(fraction);
    });

    var box = $('#box');
    box.css('width',window.innerWidth);
    box.css('height',window.innerHeight);

    var canvasW = window.innerWidth;
    var canvasH = window.innerHeight;
    var canvasObj = $('#canvas');

    canvasObj.attr('width',canvasW);
    canvasObj.attr('height',canvasH);
    canvasObj.css('width',canvasW+"px");
    canvasObj.css('height',canvasH+"px");
    canvasObj.css('background-color',"#e05a5a");
    canvasObj.css('margin-top',(window.innerHeight-canvasH)/2);

    var ca=document.getElementById("canvas");
    var ctx=ca.getContext("2d");



    var flyer = new Array();


    var player = new Image();


    // var playerWidth =215;
    // var playerHeight =309;
    var playerWidth =100;
    var playerHeight =150;

    var cao = new Image();

    var h=20;
    var sudu=15;
    var zl=100;
    var chi=0;
    var shi=0;
    var fraction=0;


    function object(){
        this.x=0;
        this.y=0;
        this.l=11;
        this.image=new Image();
    }


    var sprite= new object();

    sprite.x=(canvasW - playerWidth)/2;
    sprite.y=canvasH-playerHeight;
    sprite.image=player;

    var range = canvasW - 60;


    /**
     * 生成飞行物
     */
    function makeFlyer(){
        if(shi%h==0){
            for(var j=2*chi;j<2*(chi+1);j++){
                flyer[j]=new object();
                var i=Math.round(Math.random()*range);
                if(j==2*chi+1)
                {
                    while(Math.abs(i-flyer[2*chi].x)<30){
                        i=Math.round(Math.random()*range);
                    }
                }
                var k=Math.round(Math.random()*zl);

                if(k < 50){
                    flyer[j].image.src=_ADDONS+"/images/mi1.png";
                    flyer[j].q = 1;
                    flyer[j].h = 29;
                    flyer[j].w = 15;
                }else if(k < 70){
                    flyer[j].image.src=_ADDONS+"/images/mi2.png";
                    flyer[j].q = 2;
                    flyer[j].h = 50;
                    flyer[j].w = 24;
                }else if(k < 80){
                    flyer[j].image.src=_ADDONS+"/images/mi3.png";
                    flyer[j].q = 3;
                    flyer[j].h = 79;
                    flyer[j].w = 38;
                }else if(k < 90){
                    flyer[j].image.src=_ADDONS+"/images/zhadan.png";
                    flyer[j].q = 4;
                    flyer[j].h = 63;
                    flyer[j].w = 40;
                }else {
                    flyer[j].image.src=_ADDONS+"/images/mi3.png";
                    flyer[j].q = 5;
                    flyer[j].h = 79;
                    flyer[j].w = 56;
                }
                flyer[j].x=i;
                flyer[j].y=-Math.round(Math.random()*300);
            }
            chi++;
            if(chi==10) chi=0;
        }
        shi++;
    }


    /**
     * 绘图
     */
    function draw(){
        makeFlyer();
        for(var i=0; i<flyer.length; i++){
            if( touchDetection(sprite,flyer[i]) ) {
                if(flyer[i].q == 1){
                    fraction+=1;
                }else if(flyer[i].q == 2){
                    fraction+=1;
                }else if(flyer[i].q == 3){
                    fraction+=1;
                }else if(flyer[i].q == 4){
                    stop();
                }else{
                    fraction+=1;
                }
                flyer[i].x =0;
                flyer[i].y =0;
                flyer[i].image.width =0;
                flyer[i].image.height =0;
            }else{
                flyer[i].y += sudu;
                ctx.drawImage(flyer[i].image,flyer[i].x,flyer[i].y,flyer[i].w,flyer[i].h);
            }
        }
    }

    /**
     * 触碰检测
     * @param a
     * @param b
     * @returns {boolean}
     */
    var ii = true;
    function touchDetection(a,b){
        if(
            (a.y <= b.y + b.image.height  ) &&
            (
                ( a.x <= b.x && a.x + a.image.width  >= b.x ) ||
                ( a.x <= b.x  + b.image.width   && a.x + a.image.width  >= b.x  + b.image.width )
            )
        ){
            // console.log(a);
            // console.log(b);
            // console.log("1233");
            return true;
        }
        return false;
        // var c=a.x-b.x;
        // var d=a.y-b.y;
        // if(c < b.image.width && c>-a.image.width && d<b.image.height && d>-a.image.height){
        //     if( ii){
        //         ii = false;
        //         console.log(a);
        //         console.log(b);
        //         console.log("1233");
        //     }
        //     return true;
        // }
        // return false;
    }

    /**
     * 游戏停止
     */
    function stop()
    {
        clearInterval(interval);
        $("#game_over").show();
    }



    /**
     * 操作事件定义
     */
    document.addEventListener('touchmove',myTouchMove, false);
    document.addEventListener('touchstart',myTouchMove, false);

    /**
     * 动作
     * @param event
     */
    function myTouchMove(event){
        event = event || window.event;
        var x,y ;
        switch(event.type){
            case "touchstart":
                x = event.touches[0].clientX;
                y = event.touches[0].clientY;
                break;
            case "touchend":
                x = event.changedTouches[0].clientX;
                y = event.changedTouches[0].clientY;
                break;
            case "touchmove":
                event.preventDefault();
                x = event.touches[0].clientX;
                y = event.touches[0].clientY;
                break;
        }
        sprite.x =  x - playerWidth/2;
        if( sprite.x+playerWidth >= canvasW ) {
            sprite.x=canvasW-playerWidth;
        }else if( x <= playerWidth/2){
            sprite.x=0;
        }
    }



    /**
     * 绘制循环
     * @type {number}
     */
    interval = setInterval(function(){
        if( !is_draw ){
            return;
        }
        if( is_first_load ){
            player.src=_ADDONS+"/images/ren_"+player_test+".png";
            cao.src=_ADDONS+"/images/cao2.png";
            is_first_load = false;
        }

        ctx.clearRect(0,0,canvasW,canvasH);
        ctx.drawImage(cao,0,canvasH-80,canvasW,80);
        ctx.drawImage(sprite.image,sprite.x,sprite.y,playerWidth,playerHeight);
        ctx.drawImage(cao,0,canvasH-30,canvasW,30);
        draw();
    },50);



});
