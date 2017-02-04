/*
 * @Author: zhonght
 * @Date:   2016-12-08
 */

var lock = false;

$(function(){
    $("#tab_1").show();
    $('.f_bottom a').on('click',function(){
        $(".f_tab").hide();
        var number = $(this).data("number");
        $("#tab_" + number).show();
    });
    $(".f_c_button").click(function(){
        cookRiceButtonClick();
    });
    $("#showGuide").click(function(){
        show_guide();
    });
});

function cookRiceButtonClick() {
    if (lock) {
        return;
    }
    lock = true;
    var needAjax = false;
    var data = {};
    switch (status) {
        case "1":
            needAjax = true;
            data = {pluginName: "createActivity"};
            break;
        case "2":
            show_guide();
            break;
        case "3":
            needAjax = true;
            data = {pluginName: "help",activityId:activityId};
            break;
        default:
            break;
    }
    if (needAjax) {
        $.ajax({
            type: "GET",
            url: ApiUrl,
            data: data,
            dataType: 'json',
            success: function (data) {
                alert(data.msg);
                lock = false;
                location.reload();
            },
            error: function () {
                alert("系统繁忙，请稍后再试！");
                lock = false;
                location.reload();
            }
        });
    } else {

    }

    lock = false;
}




function show_guide(){
    $("#guide").show();
}
function hide_guide(){
    $("#guide").hide();
}
$(function(){
    $("#guide").click(function(){
        hide_guide();
    });
});

