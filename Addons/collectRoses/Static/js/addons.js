/*
 * @Author: zhonght
 * @Date:   2016-02-07
 */


var lock = false;
var date_obj = new Date();
var roses_list = {
    1:{"name":"信任","text":"爱是妒忌、爱是怀疑、爱是种近乎幻想的真理。"},
    2:{"name":"理解","text":"你不懂我，我就怪你。"},
    3:{"name":"沟通","text":"大声说出我爱你。"},
    4:{"name":"尊重","text":"我愿是你身旁的一棵木棉。"},
    5:{"name":"关怀","text":"多喝热水。"},
    6:{"name":"忠诚","text":"忠于国家忠于党忠于你。"},
    7:{"name":"赞美","text":"在我心里，你永远都是最胖哒。"},
    8:{"name":"感恩","text":"谢谢把我当成小公举。"},
    9:{"name":"相爱","text":"相爱才能互相伤害呀，么么哒"}
};
//获取url参数
function get_query_str(){
    var location_url = window.location.href;
    var parameter_str = location_url.split('?')[1];
    var $_GET = {};
    if(parameter_str!=undefined){
        parameter_str = parameter_str.split('#')[0];
        var parameter_arr = parameter_str.split('&');
        var tmp_arr;
        for(var i = 0, len = parameter_arr.length; i <= len -1; i++){
            tmp_arr = parameter_arr[i].split('=');
            $_GET[tmp_arr[0]] = decodeURIComponent(tmp_arr[1]);
        }
    }
    window.$_GET = $_GET;
}


$(function(){
    $("#tab").show();
    $("#tab_1").fadeIn();
    $('.f_bottom a').on('click',function(){
        $("#tab").show();
        $(".r_content_flower").addClass("r_alert_start");
        $(".n_tab_div").fadeOut();
        var number = $(this).data("number");
        $("#tab_" + number).fadeIn();
    });
    $(".n_tab_div_tip_shrink").click(function(){
        $("#tab").slideToggle(300);
    });

    $(".f_c_button").click(function(){
        cookRiceButtonClick();
    });
    $("#showGuide").click(function(){
        show_guide();
    });
    get_query_str();
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
                if(data.data.getRose==1){
                    $(".r_content_flower").addClass("r_alert_start");
                    $("#r_alert_span").html(roses_list[data.data.roseNumber]["name"]);
                    $("#r_alert_div_span").html(roses_list[data.data.roseNumber]["text"]);
                    $("#r_alert_flower_img").attr("src",imagesUrl+"r_alert_flower_"+data.data.roseNumber+".png");
                    $('#r_alert').show();
                }else{
                    new_jop();
                }

                return;
            },
            error: function () {
                alert("系统繁忙，请稍后再试！");
                lock = false;
            }
        });
    } else {

        lock = false;
    }
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




function new_jop(){
    $('#r_alert').hide();
    $_GET['timestamp'] = date_obj.getTime();

    var location_url = window.location.href;
    var url = location_url.split('?')[0];
    var hash_str = location_url.split('#')[1];
    var query_arr = [];
    for(var i in $_GET){
        query_arr.push(i+'='+$_GET[i]);
    }
    if(query_arr){
        url += '?' + query_arr.join('&');
    }
    if(hash_str){
        url += '#' + hash_str;
    }
    window.location.href = url;
}







