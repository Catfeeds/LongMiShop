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
                alert("系统繁忙，请稍后再试！");
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
            $("#rob").css("width","64px");
            $("#rob").css("height","64px");
            $("#rob").css("margin-left","-32px");
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




// function prompt(){
//     $(".red").addClass("red_shake");
//     setTimeout(function(){
//         $(".red").removeClass("red_shake");
//     },100);
//
// }


var manDataLock = false;
var mamOne = true;
function getManData(){
    if(manDataLock){
        return;
    }
    manDataLock = true;
    var p_data={pluginName:"getManData"};
    if( mamOne ){
        p_data={pluginName:"getManData",needList:1};
    }
    $.ajax({
        type : "GET",
        url:ApiUrl,
        data:p_data,
        dataType:'json',
        success: function(data){
            manDataLock = false;
            if( data.state == 1){
                $("#number").html("已有"+data.number+"人领取了红包");
                if( data.needList ==1){
                    mamOne = false;
                    var listHtml = "";
                    var lists = data.list;
                    for(var i in lists){
                        listHtml +="<span>恭喜"+lists[i]+"</span><br>";
                    }
                    $("#marquee").html(listHtml);
                }
            }else{
                $("#number").html("年年有米");
            }
        }
    });
}



$(function(){
    getManData();
    var timer2 = setInterval(function(){
        if(isRun != true){
            return;
        }else{
            getManData()
        }
    },5000);
});