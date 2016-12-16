$(function () {
    //$(".buy").click(function () {
    //    $(".buy").html("立即购买");
    //    $(".buy").css({ "background-color": "#fff", "color": "#ead9b1" });
    //    $(".meng").hide("fast");
    //    $(this).html("扫码立即购买");
    //    $(this).css({ "background-color": "#ead9b1", "color": "#fff" });
    //    $(this).parent().siblings().children(".meng").slideDown("fast");
    //});

    $('#myCarousel').carousel({
        interval: 3000
    });

    $('.top').click(function () {
        $('html, body').animate({ scrollTop: 0 }, 'fast');
        return false;
    });

    $(".goods .item").hover(function () {
        $(".buy").html("立即购买");
        $(".buy").css({ "background-color": "#fff", "color": "#ead9b1" });
        $(".meng").hide();
        $(this).find(".buy").html("扫码立即购买");
        $(this).find(".buy").css({ "background-color": "#ead9b1", "color": "#fff" });
        $(this).find(".buy").parent().siblings().children(".meng").slideDown();
    }, function () {
        $(".buy").html("立即购买");
        $(".buy").css({ "background-color": "#fff", "color": "#ead9b1" });
        $(".meng").hide();
    });

    $(".btn-lc").hover(function () {
        $(this).css({ "background": "#e5d09d", "color": "#fff" });
    }, function () {
        $(this).css({ "background": "none", "color": "#e5d09d" });
    });

    $(".cpkouwei").click(function () {
        var odiv = document.getElementById('product');
        setInterval(function () {
            var speed = (300 - odiv.offsetLeft) / 10;// Math.ceil是向上取整。而Math.floor是向下取整。。
            if (speed > 0) {
                speed = Math.ceil(speed)
            } else {
                speed = Math.floor(speed)
            }
            odiv.style.left = odiv.offsetLeft + speed + 'px';
            document.title = odiv.offsetLeft + ',' + speed  //用来验证 odiv移动的距离和速度
        }, 30);
    });

    $('.guanyu').click(function () {
        var Topheight = Number($(".head").outerHeight()) + Number($(".nav").outerHeight()) + Number($("#lun").outerHeight());
        $('html, body').animate({ scrollTop: Topheight }, 'fast');
        return false;
    });
    $('.yuan').click(function () {
        var Topheight2 = Number($(".head").outerHeight()) + Number($(".nav").outerHeight()) + Number($("#lun").outerHeight()) + Number($("#about").outerHeight());
        $('html, body').animate({ scrollTop: Topheight2 }, 'fast');
        return false;
    });
    $('.chanpin').click(function () {
        var Topheight3 = Number($(".head").outerHeight()) + Number($(".nav").outerHeight()) + Number($("#lun").outerHeight()) + Number($("#about").outerHeight()) + Number($("#vitality").outerHeight()) + 150;
        $('html, body').animate({ scrollTop: Topheight3 }, 'fast');
        return false;
    });
    $('.daren').click(function () {
        var Topheight4 = Number($(".head").outerHeight()) + Number($(".nav").outerHeight()) + Number($("#lun").outerHeight()) + Number($("#about").outerHeight()) + Number($("#vitality").outerHeight()) + Number($("#product").outerHeight()) + 200;
        $('html, body').animate({ scrollTop: Topheight4 }, 'fast');
        return false;
    });
    $('.lianxi').click(function () {
        var Topheight6 = Number($(".head").outerHeight()) + Number($(".nav").outerHeight()) + Number($("#lun").outerHeight()) + Number($("#about").outerHeight()) + Number($("#vitality").outerHeight()) + Number($("#product").outerHeight()) + Number($("#praise").outerHeight()) + Number($("#order").outerHeight());
        $('html, body').animate({ scrollTop: Topheight6 }, 'fast');
        return false;
    });
    $('.ding').click(function () {
        var Topheight5 = Number($(".head").outerHeight()) + Number($(".nav").outerHeight()) + Number($("#lun").outerHeight()) + Number($("#about").outerHeight()) + Number($("#vitality").outerHeight()) + Number($("#product").outerHeight()) + Number($("#praise").outerHeight()) + 300;
        $('html, body').animate({ scrollTop: Topheight5 }, 'fast');
        return false;
    });


});
