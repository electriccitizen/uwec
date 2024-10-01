(function($, Drupal, once) {

/* Views exposed filter JS helpers
------------------------------------ */
Drupal.behaviors.viewsExposed = {
  attach: function (context, settings) {
    $(once('wasSearched', '.views-exposed-form', context)).each(function(){
      //check if searched and scroll to if so
      var urlCurrent = window.location.href;
      if((urlCurrent.indexOf("?f") > -1) || (urlCurrent.indexOf("?c") > -1) || (urlCurrent.indexOf("?p") > -1) || (urlCurrent.indexOf("?s") > -1)){
				$(document).ready(function(){
					setTimeout(function() {
            const formHeight = $('.views-exposed-form').outerHeight() - 100;
			   		$('html,body').animate({
							scrollTop: $('.views-exposed-form').offset().top + formHeight
			   			});
			   			$('.results-tally').delay(300).fadeIn(500);
		   			}, 300);
		 		});
      }
      $('.view-header .search-terms').append(vars);
    });
  }
};

})(jQuery, Drupal, once);
