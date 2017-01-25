/**
 * Created by 钟瀚涛 on 2017/1/21.
 */

var timeLimit = 100;
$(function() {

    $("#rob").click(function () {
        if (lock) {
            return;
        }
        lockAction();

        // prompt();
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
// function prompt(){
//     $(".red").addClass("red_shake");
//     setTimeout(function(){
//         $(".red").removeClass("red_shake");
//     },100);
//
// }


