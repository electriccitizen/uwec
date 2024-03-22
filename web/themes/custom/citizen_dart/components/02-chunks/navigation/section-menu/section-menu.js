(function($, Drupal, once) {

Drupal.behaviors.sectionMenu = {
	attach: function (context, settings) {
		$(once('section-menu', '#section-menu-wrapper', context)).each(function(){
			//mobile toggle
			$('.section-menu-toggle').click(function(e){
				e.preventDefault();
        if($(this).is('.active-nav')){
          $(this).attr('aria-expanded', 'false').removeClass('active-nav').find('span').text('Explore Section').closest('#section-menu-title').next('#section-menu-wrapper').attr('aria-hidden', 'true').slideUp(500);
        }else{
          $(this).attr('aria-expanded', 'true').addClass('active-nav').find('span').text('Close').addClass('active-nav').closest('#section-menu-title').next('#section-menu-wrapper').attr('aria-hidden', 'false').slideDown(500);
        }
			});

			$(window).on('resize', debounce(mobileSectionnav, 150)).trigger('resize');

			//remove nav region if nav is hidden
			if($('body.hide-nav').length){
				$('#node-section-2 > .layout--twocol-sideleft > .layout__region--first').remove();
			}
		});
	}
}//end section menu function

function mobileSectionnav() {
  var wwidth = $(window).outerWidth();
  if (wwidth < 984) {
  	$('.section-menu-toggle').attr('href','#');
    //add aria roles to menu title and wrapper if not already set by click above
    if(!$('.section-menu-toggle').attr('aria-controls')){
      $('.section-menu-toggle').attr({
        'aria-controls': 'section-menu-wrapper',
        'aria-expanded': 'false'
      });
      $('#section-menu-wrapper').attr('aria-hidden', 'true');
    }
  }else{
    //strip all aria roles & prevent click
    $('.section-menu-toggle').removeAttr('aria-controls aria-expanded role href');
    $('#section-menu-wrapper').removeAttr('aria-hidden');
  }
};


})(jQuery, Drupal, once);
