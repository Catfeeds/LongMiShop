/*
 * @Author: zhonght
 * @Date:   2016-12-08
 */


var lock = false;


$(function(){
    $("#tab").show();
    $("#tab_1").show();
    $('.f_bottom a').on('click',function(){
        $("#tab").show();
        $(".n_tab_div").hide();
        var number = $(this).data("number");
        $("#tab_" + number).show();
    });
    $(".n_tab_div_tip_shrink").click(function(){
        $("#tab").hide();
    });

    $(".f_c_button").click(function(){
        cookRiceButtonClick();
    });
    $("#showGuide").click(function(){
        show_guide();
    });
});


//按钮事件
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
            data = {pluginName: "help", activityId: activityId};
            break;
        case "4":
            window.location.href=ApiUrl;
            return;
        case "5":
            needAjax = true;
            var user_name = $("#user_name").val();
            var user_phone = $("#user_phone").val();
            var user_site = $("#user_site").val();
            if (user_name == "") {
                alert("得奖人姓名不能为空");
                lock = false;
                return;
            }
            if (user_phone == "") {
                alert("手机号不能为空");
                lock = false;
                return;
            }
            if (!checkMobile(user_phone)) {
                alert("请填写正确的手机号");
                lock = false;
                return;
            }
            if (user_site == "") {
                alert("回寄地址不能为空");
                lock = false;
                return;
            }
            data = {pluginName: "setData", activityId: activityId,user_name:user_name,user_phone:user_phone,user_site:user_site};
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
            }
        });
    } else {

    }

    lock = false;
}


/**
 * 遮罩部分
 */

//显示遮罩
function show_guide(){
    $("#guide").show();
}
//隐藏遮罩
function hide_guide(){
    $("#guide").hide();
}
//遮罩初始化
$(function(){
    $("#guide").click(function(){
        hide_guide();
    });
});

//手机检测
function checkMobile(tel) {
    var reg = /(^1[3|4|5|7|8][0-9]{9}$)/;
    if (reg.test(tel)) {
        return true;
    }else{
        return false;
    };
}

//显示表单
function showSubmit(){
    $(".n_tip").hide();
    $(".f_c_button2").hide();
    $(".f_c_button").show();
    $(".n_application").show();
}