(function($, Drupal, once) {
Drupal.behaviors.pager = {
	attach: function (context, settings) {
		$(once('page-menu', '.count-shim', context)).each(function(){
			//add pager counter to results
			var count = $('.count-shim').html();
			$('.page-total').html(count);
			if(count > 7){
				$('nav.pager').addClass('results-pager');
				$('.results-count').prependTo('.pager').wrap('<div class="results-count-wrapper"></div>');
				$('.pager__number,.results-count').show();
			}else{
				$('.pager__current').show();
			}
		});
	}
}//end page menu function

})(jQuery, Drupal, once);
