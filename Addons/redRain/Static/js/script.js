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
                unLockAction();
                $("#rob").removeClass("animation_run");
                if(data.state == 1){
                    var myVid=document.getElementById("audio");
                    myVid.muted=false;
                    myVid.play();
                    alert(data.msg);
                    window.location.href=ApiUrl+'?pluginName=lists';
                }else{
                    alert(data.msg);
                }
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
var  is_one = true;
document.addEventListener('touchstart', function(){
    if( is_one ){
        is_one = false;
        var myVid=document.getElementById("audio");
        myVid.muted=true;
        myVid.play();
    }
}, false);
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


