(function($, Drupal, once) {

/* Scroll to results if an exposed filter was used & set placeholders for select 2
------------------------------------ */
Drupal.behaviors.viewsScroll = {
  attach: function (context, settings) {
    $(once('wasSearched', '.views-exposed-form', context)).each(function(){
      //check if searched
      var urlCurrent = window.location.href;
      if((urlCurrent.indexOf("?f") > -1) || (urlCurrent.indexOf("?c") > -1) || (urlCurrent.indexOf("?s") > -1)){
				$(document).ready(function(){
					setTimeout(function() {
			   			$('html, body').animate({
							scrollTop: $('.view .exposed-form-container').offset().top - 10
			   			});
		   			}, 300);
		 		});
      }
    });
  }
};

})(jQuery, Drupal, once);
