$(function(){
    $(".loading_css").fadeOut();
    $.ajax({
        type : "POST",
        url:"{:U('Wap/Index/index')}",
        dataType:'json',
        success: function(data){
            $("#userMoney").html(data.data.top_menu.userMoney);
            $("#couponCount").html(data.data.top_menu.couponCount);
            $("#activityCount").html(data.data.top_menu.activityCount);
            $("#inviteNumber").html(data.data.top_menu.inviteNumber);
            $("#slider").html(template.render("banner", data.data.ad));
            $("#main-container1").html(template.render("goods1", data.data.newGoods));
            $("#main-container2").html(template.render("goods2", data.data.favouriteGoods));
            TouchSlide({
                slideCell:"#picScroll",
                titCell:".hd ul", //开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
                autoPage:true, //自动分页
                pnLoop:"false", // 前后按钮不循环
                switchLoad:"_src", //切换加载，真实图片路径为"_src"
                autoPlay:true
            });
            var gallery = mui('.mui-slider');
            gallery.slider({
                interval: 2000//自动轮播周期，若为0则不自动播放，默认为0；
            });
        },
        error:function(){
            alert("网络错误！");
        }
    });
});

var before_request = 1; // 上一次请求是否已经有返回来, 有才可以进行下一次请求
function switch_num(number,goods_id,key,store_count) {
    if (before_request == 0)
    {
        alert("点击太频繁 ,休息一下");
        return false;
    }
    var cart_number = $(".my_cart .figure").html();
    cart_number = parseInt(cart_number);
    var num2 = parseInt($("#goods_num_"+goods_id+"_" + key).val());
    if( number < 0 && num2 < 1){
        return false;
    }
    num2 += number;
    if (num2 > store_count) {
        alert("库存只有 " + store_count + " 件, 你只能买 " + store_count + " 件");
        num2 = store_count; // 保证购买数量不能多余库存数量
        $("#goods_num_"+goods_id+"_" + key).val(num2);
        return false;
    }
    before_request = 0;
    $.ajax({
        type : "POST",
        url:"{:U('Mobile/Cart/ajaxChangeCartData')}",
        data : {number:number,goods_id:goods_id,key:key},
        dataType:"json",
        success: function(data){
            alert(data.msg);
            if( data.status==1){
                $("#goods_num_"+goods_id+"_" + key).val(num2);
                if( number > 0){
                    flyCart("#goods_num_"+goods_id+"_" + key);
                }
                cart_number += number;
                ajaxGetCartData();
            }
            before_request = 1;
        },
        error:function(){
            before_request = 1;
        }
    });
}
function flyCart( div_id ) {
    var img = $(div_id).parent().parent().parent().find('img');
    var flyElm = img.clone().css('opacity', 0.75);
    $('body').append(flyElm);
    flyElm.css({
        'z-index': 9000,
        'display': 'block',
        'position': 'absolute',
        'top': $(div_id).offset().top +'px',
        'left': $(div_id).offset().left +'px',
        'width': img.width() +'px',
        'height': img.height() +'px'
    });
    flyElm.animate({
        top: $('.my_cart').offset().top,
        left: $('.my_cart').offset().left + ($('.my_cart').width()/2),
        width: 20,
        height: 32
    }, 'slow', function() {
        flyElm.remove();
    });
}