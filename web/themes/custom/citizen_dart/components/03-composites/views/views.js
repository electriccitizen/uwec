(function($, Drupal, once) {

/* Views exposed filter JS helpers
------------------------------------ */
Drupal.behaviors.viewsExposed = {
  attach: function (context, settings) {
    $(once('wasSearched', '.views-exposed-form', context)).each(function(){
      //check if searched and scroll to if so
      var urlCurrent = window.location.href;
      if((urlCurrent.indexOf("?f") > -1) || (urlCurrent.indexOf("?c") > -1) || (urlCurrent.indexOf("?s") > -1)){
				$(document).ready(function(){
					setTimeout(function() {
			   		$('html,body').animate({
							scrollTop: $('.views-exposed-form').offset().top - 10
			   			});
			   			$('.results-tally').delay(300).fadeIn(500);
		   			}, 300);
		 		});
      }

      let vars = '';
			$('.views-exposed-form input:not(#edit-submit-profile-search):not(#edit-submit-program-search):not(#edit-reset)').each((i, v) => {
			  var isLastElement = i == v.length -1;
			  if (isLastElement) {
			    vars += $(v).val();
			  }else{
			  	if ($(v).val() != ''){
			  		vars += '<span class="search-term">' + $(v).val() + '</span>';
			  	}
			  }
			})
      $('.view-header .search-terms').append(vars);
    });
  }
};

})(jQuery, Drupal, once);
