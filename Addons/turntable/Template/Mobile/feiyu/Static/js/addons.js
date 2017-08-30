

var click = false;
var myPrize = -1;
var lottery = {
    index: -1, //当前转动到哪个位置，起点位置
    count: 0, //总共有多少个位置
    timer: 0, //setTimeout的ID，用clearTimeout清除
    speed: 30, //初始转动速度
    times: 0, //转动次数
    cycle: 100, //转动基本次数：即至少需要转动多少次再进入抽奖环节
    prize: -1, //中奖位置
    init: function (id) {
        if ($("#" + id).find(".lottery-unit").length > 0) {
            $lottery = $("#" + id);
            $units = $lottery.find(".lottery-unit");
            this.obj = $lottery;
            this.count = $units.length;
            $lottery.find(".lottery-unit-" + this.index).addClass("active");
        }
    },
    roll: function () {
        var index = this.index;
        var count = this.count;
        var lottery = this.obj;
        $(lottery).find(".lottery-unit-" + index).removeClass("active");
        index += 1;
        if (index > count - 1) {
            index = 0;
        }
        $(lottery).find(".lottery-unit-" + index).addClass("active");
        this.index = index;
        return false;
    },
    stop: function (index) {
        this.prize = index;
        return false;
    }
};

function choujiang() {
    $.ajax({
        type: "post",
        url: lotteryUrl,
        data: {},
        dataType: "json",
        success: function (json) {
            if (json.success == 1) {
                var prize = json.prize;
                var index = $('[data-id="' + prize + '"]').data("key");
                if (json.period_id != 0) {
                    $('#take').find('a').attr('href', "{php echo $this->createMobileUrl('turntable_order', array('op' => 'detail'))}" + "&id=" + json.period_id);
                }
                myPrize = index;
                roll();
            } else {
                click = false;
                alert(json.msg);
            }
        }
    });
}


function prize_msg() {
    var prize_img = $('.lottery-unit.active .img-box img').attr('src');
    var prize_name = $('.lottery-unit.active div span').text();
    $("#mask").show();
    $("#mess p img").attr("src", prize_img);
    $("#mess p span").text(prize_name);
}

function roll() {
    lottery.times += 1;
    lottery.roll();
    if (lottery.times > lottery.cycle + 10 && lottery.index == myPrize) {
        clearTimeout(lottery.timer);
        lottery.prize = -1;
        lottery.times = 0;
        myPrize = -1;
        click = false;
        var prize_type = $('.lottery-unit.active').data('type');

        if (prize_type === 0) {
            $("#prize_type").text("很遗憾。。。");
            $("#continue").css("width", "100%");
            $("#take").hide();
            prize_msg();
        } else if (prize_type === 1) {
            $("#prize_type").text("恭喜中奖！");
            $("#continue").css("width", "100%");
            $("#take").hide();
            prize_msg();
        } else {
            $("#prize_type").text("恭喜中奖！");
            prize_msg();
        }
    } else {
        if (lottery.times < lottery.cycle) {
            lottery.speed -= 10;
        } else if (lottery.times == lottery.cycle) {
            var index = Math.random() * (lottery.count) | 0;
            lottery.prize = index;
        } else {
            if (lottery.times > lottery.cycle + 10 && ((lottery.prize == 0 && lottery.index == 7) || lottery.prize == lottery.index + 1)) {
                lottery.speed += 110;
            } else {
                lottery.speed += 20;
            }
        }
        if (lottery.speed < 40) {
            lottery.speed = 40;
        }
        lottery.timer = setTimeout(roll, lottery.speed);
    }
    return false;
}

$(".go").click(function () {
    if (click) {
        return false;
    } else {
        lottery.speed = 100;
        choujiang();
        click = true;
        return false;
    }
});

function closeMask() {
    $('#mask').hide();
    window.location.reload();
}

$("#continue").click(function () {
    closeMask();
});

// {loop $prize $key $p}
// $(".lottery-unit-{$key}").attr("data-id", "{$p['id']}");
// $(".lottery-unit-{$key}").attr("data-type", "{$p['prize_type']}");
// $(".lottery-unit-{$key}").attr("data-key", "{$key}");
// $(".lottery-unit-{$key} div span").html("{$p['prize_name']}");
// if({$p['prize_type']}==2){
//     $(".lottery-unit-{$key} .details p:eq(0)").html("{$p['goods_info']['title']}");
//     $(".lottery-unit-{$key} .details p:eq(1)").html("市场价：{$p['goods_info']['price']}元");
// }
// $(".lottery-unit-{$key} div img").attr('src', "{php echo tomedia($p['prize_img'])}");
// {/loop}