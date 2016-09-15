$(document).ready(function(e) {			
	t = $('.account-sidebar').offset().top;
	mh = $('.fn-clear').height();
	fh = $('.account-sidebar').height();
	$(window).scroll(function(e){
		s = $(document).scrollTop();	
		if(s > t){
			$('.account-sidebar').css('position','fixed');
			$('.account-sidebar').css('top','0px');

			if(s + fh > mh){
				$('.account-sidebar').css('top',mh-s-fh+'px');	
			}				
		}else{
			$('.account-sidebar').css('position','');
		}
	})
});
