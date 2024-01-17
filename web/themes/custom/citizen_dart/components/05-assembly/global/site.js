(function($, Drupal, once) {

/* LAYOUT 
------------------ */
Drupal.behaviors.removeEmptyRegions = {
  attach: function (context, settings) {
    $(once('removeEmpty', '.layout > .layout__region:not(.layout-builder__region)', context)).each(function(){
      if(!$(this).children().length){
        $(this).remove();
      }
    });
    $(once('removeEmptyContact', '.node-bios #node-section-2,.node-department #node-section-4,.node-college #node-section-4', context)).each(function(){
      if(!$('.field',this).length){
        $(this).remove();
      }
    });
  }
}

/* BACK TO TOP
------------------ */
Drupal.behaviors.backToTop = {
  attach: function (context, settings) {
  	$(once('backTop', 'html.js', context)).each(function(){
      $(window).scroll(function(){
        var back = $(window).height() * .8;
        if ($(this).scrollTop() > back ) {
          $('.back-anchor').fadeIn(200);
        }else{
          $('.back-anchor').fadeOut(200);
        }
      });
      //scroll to toc
      $('.back-anchor a').click(function(e){
        e.preventDefault();
        $('html, body').animate({
          scrollTop: $('body').offset().top - 10
        });
      });
    });
  }
}

Drupal.behaviors.mobileMenu = {
		attach: function (context, settings) {
			$(once('insertHeaderElements', '.block-superfishmain', context)).each(function() {
				$(document).ready(function() {
					$(window).on('resize', debounce(mobileMenuInsert, 10)).trigger('resize');
				});
			});
		}
	};

//function to move blocks to mobile menu
function mobileMenuInsert() {
	var wwidth = $(window).outerWidth();
	if (wwidth < 984) {
		//if not in desktop or higher, clone the search and secondary menu and move them to the mobile menu
		if(!$('.sf-accordion > li.mobile-search-container').length){
			var $searchContainer = $('.site-header .search-box-wrapper');

			// Continue with cloning and moving
			$searchContainer.clone(true)
				.prependTo('.sf-accordion')
				.wrap('<li class="mobile-search-container"></li>')
				.addClass('mobile-search');

			$('.mobile-search-container form.gsc-search-box label').remove();
			if(!$('.sf-accordion > li.mobile-search-container label').length){
	      $('.mobile-search-container form.gsc-search-box').prepend('<label for="gsc-i-id1">Search</label>');
	    }else{
	    	$('.sf-accordion > li.mobile-search-container label').text('Search');
	    }
      $('.mobile-search-container input.gsc-input').attr('placeholder', 'What are you searching for?');

      var $secondaryMenu = $('.site-header #block-secondary-menu > ul');

      // Continue with cloning and moving
			$secondaryMenu.clone(true)
				.appendTo('.sf-accordion')
				.wrap('<li class="mobile-secondary-container"></li>')
				.addClass('mobile-secondary');
		}
	}
}

})(jQuery, Drupal, once);
