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
        // $("#rob").addClass("animation_run");
        $.ajax({
            type : "GET",
            url:ApiUrl,
            data:{pluginName:"getRed"},
            dataType:'json',
            success: function(data){
                unLockAction();
                // $("#rob").removeClass("animation_run");
                if(data.state == 1){
                    var myVid=document.getElementById("audio");
                    myVid.muted=false;
                    myVid.play();
                }
                alert(data.msg);
                window.location.href=ApiUrl;
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


function myTouchMove(event){
    event = event || window.event;
    switch(event.type){
        case "touchstart":
            if (lock) {
                return;
            }
            $("#rob").css("bottom","27%");
            $("#rob").css("width","70px");
            $("#rob").css("height","70px");
            $("#rob").css("margin-left","-35px");
            break;
        case "touchend":
            $("#rob").css("bottom","28%");
            $("#rob").css("width","80px");
            $("#rob").css("height","80px");
            $("#rob").css("margin-left","-40px");
            break;
    }

}


var is_one = true;
document.addEventListener('touchstart', function(){
    if( is_one ){
        is_one = false;
        var myVid=document.getElementById("audio");
        myVid.muted=true;
        myVid.play();
    }
}, false);

document.getElementById("rob").addEventListener('touchend',myTouchMove, false);
document.getElementById("rob").addEventListener('touchstart',myTouchMove, false);



var timer = setInterval(function(){
    if( thisTime >= startTime ){
        $("#tipMsg").html(tipMsg);
        lock = false;
        clearInterval(timer);
    }else{
        thisTime ++;
    }
},1000);


// function prompt(){
//     $(".red").addClass("red_shake");
//     setTimeout(function(){
//         $(".red").removeClass("red_shake");
//     },100);
//
// }


