$(document).ready(function(){
  $(".collect").click(function(){
  $(this).toggleClass("collect_click");
  });
});



$(function(){

	$('.m33').find('li').each(function(i){
		$(this).click(function(){
			$('.m33').find('li').removeClass('m33_hover');
			$(this).addClass('m33_hover');
			
			})
		})
	
	})
	
	
	// JavaScript Document
$(document).ready(function(){
//加的效果
$(".add").click(function(){
var n=$(this).prev().val();
var num=parseInt(n)+1;
if(num==0){ return;}
$(this).prev().val(num);
});
//减的效果
$(".jian").click(function(){
var n=$(this).next().val();
var num=parseInt(n)-1;
if(num==0){ return}
$(this).next().val(num);
});
})

$(function() {
           $("#checkAll").click(function() {
                $('input[name="subBox"]').attr("checked",this.checked); 
            });
            var $subBox = $("input[name='subBox']");
            $subBox.click(function(){
                $("#checkAll").attr("checked",$subBox.length == $("input[name='subBox']:checked").length ? true : false);
            });
        });
		
		
		
$(document).ready(function(){
  $(".site_txt1 a").click(function(){
  $(".mui-input-group").toggle()
  });
});



$(document).ready(function(){
  $(".c_btn55").click(function(){
  $(".c_btn55_box").fadeOut(300);
  $(".me1_box").fadeToggle(300);
  });
});
$(document).ready(function(){
  $(".c_btn555").click(function(){
  $(".c_btn55_box").fadeIn(300);
  $(".me1_box").fadeToggle(300);
  });
});
$(document).ready(function(){
  $(".c_btn666").click(function(){
  $(".c_btn55_box").fadeIn(300);
  $(".me1_box").fadeToggle(600);
  });
});