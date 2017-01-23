/**
 * Created by 钟瀚涛 on 2017/1/21.
 */

var lock = false;
var timeLimit = 200;
$(function() {

    $("#rob").click(function () {
        if (lock) {
            return;
        }
        lockAction();
        var probability = Math.round(Math.random() * 100);

        if( probability > 90 ){
            $("#rob").addClass("animation_run");
            $.ajax({
                type : "GET",
                url:ApiUrl,
                data:{pluginName:"getRed"},
                dataType:'json',
                success: function(data){
                    if(data.state == 1){

                    }else{
                        $("#rob").removeClass("run");
                        unLockAction();
                    }
                    alert(data.msg);
                },
                error:function(){
                    alert("网络错误！");
                    location.reload();
                }
            });

        }else{
            prompt();
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
    var html = "<span>狂戳!</span>";
    $("#prompt").append(html);
}