(function($, Drupal, once) {

Drupal.behaviors.sectionMenu = {
	attach: function (context, settings) {
    $(once('section-menu', '#block-section-menu:not(.js-layout-builder-block)', context)).each(function() {
      const wrapper = $(this);
			//toggle
			$('.section-menu-toggle').click(function(e){
				e.preventDefault();
        if (wrapper.is('.active-nav')){
          wrapper.attr('aria-expanded', 'false').removeClass('active-nav').find('.toggle-label').text('Section Menu').removeClass('expanded');
          $("body").removeClass("sidebar-open");
          $('#layout-content').focus();
        }else{
          wrapper.attr('aria-expanded', 'true').addClass('active-nav').find('.toggle-label').text('Close Menu').addClass('expanded');
          $("body").addClass("sidebar-open");
          //detect height of menu Ul vs screen and add scroll for extra menu links if needed
          const menuTitleHeight = $('#section-menu-wrapper > .block-page-title').outerHeight() + 24
          const menuHeight = $('#section-menu-wrapper > ul').outerHeight();
          let allowedHeight = 0;
          if($(window).outerWidth() < 1200){
            allowedHeight = $(window).outerHeight() * .75 - menuTitleHeight;  
          }else{
            allowedHeight = $(window).outerHeight() - menuTitleHeight - 89;
          }
          if(menuHeight > allowedHeight){
            $('#section-menu-wrapper > ul').css({'max-height':allowedHeight + 'px','overflow-y':'scroll'});
          }
        }
			});

			$(window).on('resize', debounce(mobileSectionnav, 150)).trigger('resize');

      // hide top level non-active items if offices & services menu
      if(($(this).attr('data-menu') == 'offices-services') && ($('h2.block-page-title > a',this).attr('data-drupal-link-system-path') == 'node/9583')){
        $('#section-menu-wrapper',this).addClass('hide-non-active-top');
      }

			//remove nav region if nav is hidden
			if($('body.hide-nav').length){
				$('#node-section-2 > .layout--twocol-sideleft > .layout__region--first').remove();
			}

      // add a class if we're on a general page to let other pieces know its in the menu
      if($('body.node-type-page').length){
				$('body.node-type-page').addClass('in-main-menu');
			}

      //constrain support book menu height if needed
      if($('body.node-type-support-book').length){
        const supportHeight = $('.node-support-book #node-section-2').outerHeight() - 100;
        const supportMenuHeight = $('.node-support-book #section-menu-wrapper > ul').outerHeight();
        if(supportMenuHeight > supportHeight){
          $('.node-support-book #section-menu-wrapper > ul').css({'max-height':supportHeight + 'px','overflow-y':'scroll'});
        }
      }
		});
	}//end context attach
}//end section menu function

function mobileSectionnav() {
  var wwidth = $(window).outerWidth();
  if (wwidth < 1200) {
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
