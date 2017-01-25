/**
 * Created by 钟瀚涛 on 2017/1/21.
 */

var lock = false;
var timeLimit = 100;
$(function() {

    $("#rob").click(function () {
        if (lock) {
            return;
        }
        lockAction();

        prompt();

        var probability = Math.round(Math.random() * 100);
        if( probability > 95 ){
            $("#rob").addClass("animation_run");
            $.ajax({
                type : "GET",
                url:ApiUrl,
                data:{pluginName:"getRed"},
                dataType:'json',
                success: function(data){
                    if(data.state == 1){
                    }else{
                        unLockAction();
                    }
                    $("#rob").removeClass("animation_run");
                    alert(data.msg);
                },
                error:function(){
                    alert("网络错误！");
                    location.reload();
                }
            });

        }else{
            setTimeout(function(){
                unLockAction();
            },timeLimit);
        }
    });




});

/**
 * 锁定动作
 */
function lockAction(){
    lock = true;
}

/**
 * 解锁动作
 */
function unLockAction(){
    lock = false;
}

/**
 *
 */
function prompt(){
    $(".red").addClass("red_shake");
    setTimeout(function(){
        $(".red").removeClass("red_shake");
    },100);
    // var html = "<span>狂戳!</span>";
    // $("#prompt").append(html);
}


