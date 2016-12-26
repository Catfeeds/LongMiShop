/**
 * 米粒游戏js
 *
 * 钟瀚涛
 */

var player_test;
var playerWidth ;
var playerHeight;

//分数
var fraction=0;

var is_draw = false;
var is_first_load = true;
var is_over = false;
var is_show_fraction = false;



$(function(){



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



    var player  = new Image();
    var cao     = new Image();
    var boom    = new Image();


    var h=20;
    var speed=15; //速度
    var zl=100;
    var chi=0;
    var shi=0;


    function object(){
        this.x=0;
        this.y=0;
        this.h=0;
        this.w=0;
        this.l=11;
        this.image=new Image();
    }


    var sprite= new object();
    var plus = new Array();
    var flyingObject = new Array();
    var range = canvasW - 60;


    var probability_1 = 18;//18
    var probability_2 = 35;//40
    var probability_3 = 50;//50
    var probability_4 = 100;//100

    boom.src= _ADDONS+"/images/boom.png";


    /**
     * 生成飞行物
     */
    function makeFlyingObject(){
        if(shi%h==0){
            for(var j=2*chi;j<2*(chi+1);j++){

                flyingObject[j]=new object();

                var i=Math.round(Math.random()*range);
                // if(j==2*chi+1)
                // {
                //     while(Math.abs(i-flyingObject[2*chi].x)<30){
                //         i=Math.round(Math.random()*range);
                //     }
                // }
                var k=Math.round(Math.random()*zl);

                if(k < probability_1){
                    flyingObject[j].image.src=_ADDONS+"/images/zhadan.png";
                    flyingObject[j].q = -1;
                    flyingObject[j].h = 63;
                    flyingObject[j].w = 40;
                }else if(k < probability_2){
                    flyingObject[j].image.src=_ADDONS+"/images/mi2.png";
                    flyingObject[j].q = 1;
                    flyingObject[j].h = 50;
                    flyingObject[j].w = 24;
                }else if(k < probability_3){
                    flyingObject[j].image.src=_ADDONS+"/images/mi3.png";
                    flyingObject[j].q = 1;
                    flyingObject[j].h = 31;
                    flyingObject[j].w = 15;
                }else if(k < probability_4){
                    flyingObject[j].image.src=_ADDONS+"/images/mi1.png";
                    flyingObject[j].q = 1;
                    flyingObject[j].h = 29;
                    flyingObject[j].w = 15;
                }
                flyingObject[j].image.width = flyingObject[j].w;
                flyingObject[j].image.height = flyingObject[j].h;
                flyingObject[j].x=i;
                flyingObject[j].y=-Math.round(Math.random()*300);
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

        //创建飞行物
        makeFlyingObject();


        //飞行物绘制
        for(var i=0; i<flyingObject.length; i++){
            if( touchDetection(sprite,flyingObject[i])  ) {
                if(flyingObject[i].q != -1){
                    plusFunction( flyingObject[i].q );
                    // speed++;
                }else{
                    ctx.drawImage(boom,sprite.x -20,canvasH-120,100,80);
                    playDieSound();
                    stop();
                }
                flyingObject[i].image.width =0;
                flyingObject[i].image.height =0;
                flyingObject[i].x =0;
                flyingObject[i].y =canvasH;
            }else{
                flyingObject[i].y += speed;
                ctx.drawImage(flyingObject[i].image,flyingObject[i].x,flyingObject[i].y,flyingObject[i].w,flyingObject[i].h);
            }
        }

        //加分特效绘制
        for(var j=0; j<plus.length; j++){
            plus[j].y -= 5;
            plus[j].life -= 1;
            if( plus[j].life > 0){
                plus[j].life --;
                ctx.drawImage(plus[j].image,plus[j].x,plus[j].y,plus[j].w,plus[j].h);
            }
        }
    }


    /**
     * 加分
     * @param fraction_number
     */
    function plusFunction( fraction_number ){
        fraction += fraction_number;
        var new_key  = plus.length > 0 ?plus.length:0;
        plus[new_key]=new object();
        plus[new_key].image.src=_ADDONS+"/images/add_one.png";
        plus[new_key].h = 20;
        plus[new_key].w = 31;
        plus[new_key].image.width = plus[new_key].w;
        plus[new_key].image.height = plus[new_key].h;
        plus[new_key].x=sprite.x;
        plus[new_key].y=canvasH - 110;
        plus[new_key].life = 20;
        playSound();
    }



    function playSound(){
        document.getElementById('audio').pause();
        document.getElementById('audio').play();
    }

    function playDieSound(){
        document.getElementById('audio').pause();
        document.getElementById('die_audio').play();
    }

    /**
     * 触碰检测
     * @param a
     * @param b
     * @returns {boolean}
     */
    function touchDetection(a,b){
        if(
            (a.y <= b.y + b.image.height && b.y  < canvasH - 30  ) &&
            (
                ( a.x <= b.x && a.x + a.image.width  >= b.x ) ||
                ( a.x <= b.x  + b.image.width   && a.x + a.image.width  >= b.x  + b.image.width )
            )
        ){
            return true;
        }
        return false;
    }

    /**
     * 游戏停止
     */
    function stop()
    {
        is_over = true;
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
        if( is_over ){
            return;
        }
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
        sprite.x =  x - playerWidth/2
        if( x + playerWidth/2 >= canvasW ) {
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
            player.width = playerWidth;
            player.height = playerHeight;
            is_first_load = false;
        }

        if( is_over ){
            resultDraw();
            clearInterval(interval);
        }else{
            ctx.clearRect(0,0,canvasW,canvasH);
            ctx.drawImage(cao,0,canvasH-80,canvasW,80);
            ctx.drawImage(sprite.image,sprite.x,sprite.y,playerWidth,playerHeight);
            ctx.drawImage(cao,0,canvasH-30,canvasW,30);
            draw();
        }
    },50);


    /**
     * 结果画图
     */
    function resultDraw(){
        if( is_show_fraction ){
            ctx.clearRect(0,0,canvasW,canvasH);
            ctx.drawImage(cao,0,canvasH-80,canvasW,80);
            ctx.drawImage(sprite.image,(canvasW - playerWidth)/2,sprite.y,playerWidth,playerHeight);
            ctx.drawImage(cao,0,canvasH-30,canvasW,30);
        }else{
            ctx.clearRect(0,0,canvasW,canvasH);
            ctx.drawImage(cao,0,canvasH-80,canvasW,80);
            ctx.drawImage(sprite.image,sprite.x,sprite.y,playerWidth,playerHeight);
            ctx.drawImage(cao,0,canvasH-30,canvasW,30);
            ctx.drawImage(boom,sprite.x -20,canvasH-120,100,80);
        }
    }


    /**
     * 事件定义
     */

    $(".page_1 .ren").click(function(){
        player_test =  $(this).attr("data-test");
        playerWidth = $(this).attr("data-width");
        playerHeight = $(this).attr("data-height");

        sprite.x=(canvasW - playerWidth)/2;
        sprite.y=canvasH-playerHeight;
        sprite.image=player;
        $(".page_1").hide();
        $(".page_2").show();
        is_draw = true;
    });
    $(".page_1 .img_14").click(function(){
        $(".page_1 .rule").show();
    });
    $(".page_1 .rule").click(function(){
        $(this).hide();
    });
    $(".page_2 .result").click(function(){
        location.reload();
    });
    $(".page_2 .result2").click(function(){
        $(".page_2").hide();
        $(".page_3").show();
    });
    $("#game_over .ok").click(function(){
        var number =  $("#game_over input").val();
        if( number == "" ||  number <= 0 ){
            return;
        }
        $(this).hide();
        $("#game_over input").css("width","70%");
        $("#game_over input").attr("disable");
        $("#game_over .fraction").html(fraction);
        $("#game_over .fraction").show();

        var abs =number - fraction;
        if( Math.abs(abs) <= 3 &&  fraction > 80 ){
            $("#game_over .taunt2").show();
            $("#game_over .result2").show();
        }else{
            $("#game_over .taunt").show();
            $("#game_over .result").show();
        }
        is_show_fraction = true;
        resultDraw();

    });


    /**
     * 开始
     */

    $(".page_1").show();
});