


//js开始
// $(function (){

    //loading
    var imgarr = [""+imgpath+"bg.png",""+imgpath+"brick.png",""+imgpath+"cg.png",""+imgpath+"enemy1.png",""+imgpath+"enemy2.png",""+imgpath+"enemy3.png",""+imgpath+"enemy4.png",""+imgpath+"enemy5.png",""+imgpath+"fx.png",""+imgpath+"game_sm.png",""+imgpath+"loading_tu.png",""+imgpath+"logo.png",""+imgpath+"mei.png",""+imgpath+"nv.png",""+imgpath+"over_1.png",""+imgpath+"over_2.png",""+imgpath+"ren.png",""+imgpath+"shouye_anniu.png",""+imgpath+"shouye_bg.png",""+imgpath+"shouye_biaoti1.png",""+imgpath+"shouye_biaoti2.png",""+imgpath+"shouye_hongbao.png",""+imgpath+"shouye_liuxing.png",""+imgpath+"shouye_ren.png",""+imgpath+"shouye_tan1.png",""+imgpath+"shouye_tan2.png",""+imgpath+"shouye_xingxing.png",""+imgpath+"zhong.png"];

var imgobj = [];
    var imgs = 0;
    var jindu = 0;
    for (var i = 0,len = imgarr.length; i < len; i++) {
        imgobj[i] = new Image();
        imgobj[i].src = imgarr[i];
        imgobj[i].onload = function (){
            imgs++;
            jindu = Math.floor((imgs / len)*100);
            $(".loading_w").text(jindu + "%");
            if (imgs >= len) {
                $(".loading").fadeOut(function (){
                    $(".shouye").addClass("shouye_e");
                });
            };
        }
    };


    //定时器开始
    // var timer = setInterval(function (){},2000);
    // clearInterval(timer);

    // var timer_o = setTimeout(function (){},2000);
    // clearTimeout(timer_o);
    //定时器结束


    //全部点击事件开始

    // by点击事件
    // $(".by").get(0).addEventListener('touchstart',function (e){
        // e.preventDefault();
    // }, false);



    // 画布
    var canvas = document.getElementById('canvas1');
    // 判断是否是手机
    function isAdater(){
        var Agents = ["Android","iPhone","SymbianOS","Windows Phone","iPad","iPod"];
        var userAgentInfo = navigator.userAgent;
        var flag = false;
        for (var i = 0; i < Agents.length; i++) {
            if (userAgentInfo.indexOf(Agents[i]) > 0) {
                flag = true;
                break;
            }
        }
        return flag;
    }
    if (isAdater()) {
        canvas.width = document.documentElement.clientWidth;
        canvas.height = document.documentElement.clientHeight;
    };
    var ctx = canvas.getContext("2d");
    var canvasWidth = canvas.width;
    var canvasHeight = canvas.height;

    window.requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
    var tf = false;
    var wen_fps = 0;
    var shouye_wen_box = $(".shouye_wen_box");
    var wen_fps_max = shouye_wen_box.width();

    var imgbg = new Image();
    imgbg.src = ""+imgpath+"bg.png";
    var imgren = new Image();
    imgren.src = ""+imgpath+"ren.png";
    var imgnv = new Image();
    imgnv.src = ""+imgpath+"nv.png";
    var imgbrick = new Image();
    imgbrick.src = ""+imgpath+"brick.png";
    var imgenemy1 = new Image();
    imgenemy1.src = ""+imgpath+"enemy1.png";
    var imgenemy2 = new Image();
    imgenemy2.src = ""+imgpath+"enemy2.png";
    var imgenemy3 = new Image();
    imgenemy3.src = ""+imgpath+"enemy3.png";
    var imgenemy4 = new Image();
    imgenemy4.src = ""+imgpath+"enemy4.png";
    var imgenemy5 = new Image();
    imgenemy5.src = ""+imgpath+"enemy5.png";

    var brick = [];
    var brick_tf = false;
    var brick_s = 0;
    var brick_for = 0;
    var brick_lg = 0;
    var brick_max = 80;
    var brick_speed = 1000;
    var brick_dian = 0;

    var enemys = [];
    var enemys_draw = 0;
    var enemys_max = fnRand(5,10);

    var nv = [];

    var date_st = 0;
    var date_ed = 0;
    var date_tf = false;

    var dian_tf = false;

    var xx = canvasWidth/2-33;
    var yy = canvasHeight-240;

    var bg_speed = canvasHeight - 1500;


    function start(){

    if (tf) {
        if (brick_tf) {
            brick_s++;
            if (brick_s > 2) {
                brick_s = 0;
                brick_for++;
                if (brick_for > 12) {
                    brick_tf = false;
                    // nv.push(new Nv());
                    dian_tf = true;
                } else {
                    brick.push(new Brick());
                }
            }
        }
        if (date_tf) {
            date_st = new Date();
            if (date_st - date_ed > brick_speed) {
                date_ed = date_st;

                for (var i = 0,lg = brick.length; i < lg; i++) {
                    if (brick[i].o == 1) {
                        brick[i].o = 2;

                        if (crash(ren,brick[i])) {
                            ren.o = 2;
                        } else {
                            ren.o = 1;
                        }
                        break;
                    }
                }
            }
        }

        ctx.clearRect(0, 0, canvasWidth, canvasHeight);
        bg.draw();

        for (var i = 0,lg = brick.length; i < lg; i++) {
            if (brick[i].y > canvasHeight) {
                brick.splice(i,1);
                i--;
                lg--;
            } else {
                brick[i].draw();
            }
        };
        for (var i = 0,lg = enemys.length; i < lg; i++) {
            enemys[i].draw();
        };
        for (var i = 0,lg = nv.length; i < lg; i++) {
            nv[i].draw();
        };

        ren.draw();
        if (ren.o == 1) {
            yd();
        }
    } else {
        wen_fps++;
        if (wen_fps > wen_fps_max) {
            wen_fps = -$(".shouye_wen").width();
        }
        shouye_wen_box.css({"transform":"translate("+-wen_fps+"px,0)"});
    }

    requestAnimationFrame(start);
    }






    // 移动
    function yd(){
        var a = ren.x - xx;
        var b = ren.y - yy;
        if (nv.length > 0 && nv[0].y > canvasHeight*0.1) {
            b = 0;
        }
        if (a > 2 || a < -2) {
            for (var i = 0,lg = brick.length; i < lg; i++) {
                brick[i].x -= a/20;
                brick[i].y -= b/20;
            };
            for (var i = 0,lg = enemys.length; i < lg; i++) {
                enemys[i].x -= a/20;
                enemys[i].y -= b/20;
            };
            for (var i = 0,lg = nv.length; i < lg; i++) {
                nv[i].x -= a/20;
                nv[i].y -= b/20;
            };
            ren.x -= a/20;
            ren.y -= b/20;
        }
    }






    //背景
    var bg = {
        y: canvasHeight - 1500,
        draw:function (){
            this.move();
            ctx.drawImage(imgbg, 0, 0, 640, 1500, 0, this.y, 640, 1500);
        },
        move:function (){
            if (bg_speed-this.y > 2) {
                this.y += (bg_speed-this.y)/20;
            }
            if (this.y > 0) {
                this.y = 0;
            };
        }
    };






    // 人
    var ren = {
        w:65,
        h:85,
        x:canvasWidth/2-33,
        y:canvasHeight-240,
        img_w:65,
        img_h:85,
        img_x:0,
        img_y:0,
        tf:false,
        zy:2,
        s:0,
        o:1,
        draw:function (){
            this.move();
            ctx.drawImage(imgren, this.img_x, this.img_y, this.img_w, this.img_h, this.x, this.y, this.w, this.h);
        },
        move:function (){
            if (this.tf) {
                this.s++;
                if (this.s == 1) {
                    if (this.zy == 1) {
                        this.x -= 36;
                        this.y -= 45;
                    } else {
                        this.x += 24;
                        this.y -= 60;
                    }
                } else {
                    dian_tf = true;
                    this.s = 0;
                    this.tf = false;
                    if (this.zy == 1) {
                        this.x -= 10;
                        this.y += 10;
                    } else {
                        this.x += 10;
                        this.y += 10;
                    }

                    this.o = 2;
                    for (var i = 0,lg = brick.length; i < lg; i++) {
                        if (crash(this,brick[i])) {
                            this.o = 1;
                            break;
                        }
                    }
                    for (var i = 0,lg = nv.length; i < lg; i++) {
                        if (crash(this,nv[i])) {
                            this.o = 1;

                            // 游戏通关
                            $(".over_2").show();
                            break;
                        }
                    }
                }
            }

            // 游戏失败
            if (this.o == 2 && this.s != 1) {
                date_tf = false;
                dian_tf = false;
                this.y += 20;
                if (this.y > canvasHeight) {
                    this.y = canvasHeight;
                    this.o = 0;
                    $(".over_1").show();
                }
            }
        }
    }






    //砖头
    function Brick(c_zy){
        this.fn = fnRand(0,15);
        this.w = 82;
        this.h = 62;
        this.o = 1;
        if (brick.length > 0) {
            this.zy = brick[brick.length-1].zy;
            if (this.fn > 10) {
                this.zy==1 ? this.zy=2 : this.zy=1;
            }
            c_zy ? (this.zy=c_zy) : undefined;
            if (this.zy  == 1) {
                this.x = brick[brick.length-1].x-46;
                this.y = brick[brick.length-1].y-35;
            } else {
                this.x = brick[brick.length-1].x+34;
                this.y = brick[brick.length-1].y-50;
            }
        } else {
            this.zy = 2;
            this.x = canvasWidth/2-42;
            this.y = canvasHeight-178;
        };
        this.draw = function (){
            this.move();
            ctx.drawImage(imgbrick, 0, 0, this.w, this.h, this.x, this.y, this.w, this.h);
        };
        this.move = function (){
            if (this.o == 2) {
                this.y += 20;
            }
        };

        // 创建敌人
        if (!c_zy) {
            enemys_draw++;
            if (enemys_draw > enemys_max) {
                enemys_draw = 0;
                enemys_max = fnRand(5,10);

                enemys.push(new Enemy(this.zy));
            }

            brick_lg++;
        }
        
    }






    //敌人
    function Enemy(c_zy){
        this.fn = fnRand(1,6);
        switch(this.fn){
            case 1:
                this.img = imgenemy1;
                break;
            case 2:
                this.img = imgenemy2;
                break;
            case 3:
                this.img = imgenemy3;
                break;
            case 4:
                this.img = imgenemy4;
                break;
            case 5:
                this.img = imgenemy5;
                break;
        }
        this.w = 82;
        this.h = 140;
        if (fnRand(0,2) == 1) {
            var a = c_zy==1 ? 2 : 1;
            var b = fnRand(1,3);
            for (var i = 0; i < b; i++) {
                brick.push(new Brick(a));
            }
        }
        if (c_zy == 1) {
            this.x = brick[brick.length-1].x+34;
            this.y = brick[brick.length-1].y-128;
        } else {
            this.x = brick[brick.length-1].x-46;
            this.y = brick[brick.length-1].y-113;
        }
        this.draw = function (){
            this.move();
            ctx.drawImage(this.img, 0, 0, this.w, this.h, this.x, this.y, this.w, this.h);
        };
        this.move = function (){};
    }






    // 嫦娥
    function Nv(){
        this.w = 82;
        this.h = 200;
        this.x = brick[brick.length-1].x+34;
        this.y = brick[brick.length-1].y-188;
        this.draw = function (){
            this.move();
            ctx.drawImage(imgnv, 0, 0, this.w, this.h, this.x, this.y, this.w, this.h);
        };
        this.move = function (){};
    }






    start();






    // 开始游戏
    $(".shouye_anniu").on("click",function (){
        tf = true;
        $(".game").show();
    });






    // 关闭说明页
    $(".game_sm_anniu").on("click",function (){
        $(".game_sm").hide();
        brick_tf = true;
        $(".game").addClass("start");
    });






    // 游戏左右按钮
    $(".game_left").on("touchstart",function (){
        if (dian_tf) {
            dian_tf = false;
            ren.tf = true;
            ren.zy = 1;
            ren.img_x = 65;
            date_tf = true;

            if (brick_lg > brick_max) {
                if (nv.length == 0) {
                    nv.push(new Nv());
                }
            } else {
                brick.push(new Brick());
            }

            bg_speed += (1500-canvasHeight) / (brick_max-10);

            brick_dian++;
            if (brick_dian > 4) {
                brick_speed = 200;
            }
        }
    });
    $(".game_right").on("touchstart",function (){
        if (dian_tf) {
            dian_tf = false;
            ren.tf = true;
            ren.zy = 2;
            ren.img_x = 0;
            date_tf = true;

            if (brick_lg > brick_max) {
                if (nv.length == 0) {
                    nv.push(new Nv());
                }
            } else {
                brick.push(new Brick());
            }

            bg_speed += (1500-canvasHeight) / (brick_max-10);

            brick_dian++;
            if (brick_dian > 4) {
                brick_speed = 200;
            }
        }
    });






    // 再玩一次
    $(".over_1_anniu,.mei_anniu").on("click",function (){
        cz();
    });
    // 重置游戏函数
    function cz(){
        brick = [];
        brick_tf = true;
        brick_s = 0;
        brick_for = 0;
        brick_lg = 0;
        brick_max = 80;
        brick_speed = 1000;
        brick_dian = 0;

        enemys = [];
        enemys_draw = 0;
        enemys_max = fnRand(5,10);

        nv = [];

        date_st = 0;
        date_ed = 0;
        date_tf = false;

        dian_tf = false;

        bg_speed = canvasHeight - 1500;

        bg.y = canvasHeight - 1500;
        ren.x = canvasWidth/2-33;
        ren.y = canvasHeight-240;
        ren.img_x = 0;
        ren.o = 1;

        $(".over_1").hide();
        $(".mei").hide();
    }






    // 抽红包
    $(".over_2_anniu").on("click",function (){
        $(".over_2").hide();
        $(".zhong").show();
        // $(".mei").show();
    });






    // 打开分享页
    $(".zhong_anniu").on("click",function (){
        $(".fx").show();
    });
    // 关闭分享页
    $(".fx").on("click",function (){
        $(".fx").hide();
    });






    // 关闭分享成功页
    $(".cg_anniu").on("click",function (){
        $(".cg").hide();
    });






    //随机数
    function fnRand(min,max){
        return parseInt(Math.random()*(max-min)+min);
    }

    //撞击判断
    function crash(obj1,obj2){

        var l1 = obj1.x+obj1.w/2-1;
        var r1 = obj1.x+obj1.w/2+1;
        var t1 = obj1.y+obj1.h+10; 
        var b1 = obj1.y+obj1.h+12;
        var l2 = obj2.x;
        var r2 = obj2.x+obj2.w;
        var t2 = obj2.y; 
        var b2 = obj2.y+obj2.h;

        if (l1<r2&&r1>l2&&t1<b2&&b1>t2){
            return true;
        }else{
            return false;
        }
    }

    //全部点击事件结束











// function GetQueryString(name){
//     var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
//     var r = window.location.search.substr(1).match(reg);
//     if(r!=null)return  unescape(r[2]); return 0;
// }


// var tiao = GetQueryString("z")*1;
// switch(tiao){
//     case 1:
//         break;
// }







// });
//js结束