

$(function(){

	$('.m33').find('li').each(function(i){
		$(this).click(function(){
			$('.m33').find('li').removeClass('m33_hover');
			$(this).addClass('m33_hover');
			
			})
		})
	
	})
$(function(){

	$('.m333').find('li').each(function(i){
		$(this).click(function(){
			$('.m333').find('li').removeClass('m333_hover');
			$(this).addClass('m333_hover');
			
			})
		})
	
	})	
$(function(){

	$('.m44').find('li').each(function(i){
		$(this).click(function(){
			$('.m44').find('li').removeClass('m44_hover');
			$(this).addClass('m44_hover');
			
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
		
		
		
function showDiv(){
document.getElementById('popDiv').style.display='block';
document.getElementById('popIframe').style.display='block';
document.getElementById('bg').style.display='block';
}
function closeDiv(){
document.getElementById('popDiv').style.display='none';
document.getElementById('bg').style.display='none';
document.getElementById('popIframe').style.display='none';

}



$(function(){
$(".user").hover(function(){
$(".show").toggle();
})	
})


$(function(){
$(".sort-time-menu1").hover(function(){
$(".menu-list1").toggle();

})	
$(".sort-time-menu2").hover(function(){
$(".menu-list2").toggle();

})
})


