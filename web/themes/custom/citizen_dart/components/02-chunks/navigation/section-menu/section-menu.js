(function($, Drupal, once) {

Drupal.behaviors.sectionMenu = {
	attach: function (context, settings) {
		$(once('section-menu', '#section-menu-wrapper', context)).each(function(){
			//mobile toggle
			$('.section-menu-toggle').click(function(e){
				e.preventDefault();
        if($(this).is('.active-nav')){
          $(this).attr('aria-expanded', 'false').removeClass('active-nav').find('span').text('Explore Section').removeClass('expanded').closest('#section-menu-title').next('#section-menu-wrapper').attr('aria-hidden', 'true').slideUp(500);
        }else{
          $(this).attr('aria-expanded', 'true').addClass('active-nav').find('span').text('Close').addClass('expanded').closest('#section-menu-title').next('#section-menu-wrapper').attr('aria-hidden', 'false').slideDown(500);
        }
			});

			$(window).on('resize', debounce(mobileSectionnav, 150)).trigger('resize');

			//remove nav region if nav is hidden
			if($('body.hide-nav').length){
				$('#node-section-2 > .layout--twocol-sideleft > .layout__region--first').remove();
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

/* DETECT POSITION & HEIGHT
------------------ */
Drupal.behaviors.navPosition = {
  attach: function (context, settings) {
  	$(once('navPosition', '.layout--twocol-sideleft:not(.layout-builder__layout)', context)).each(function(){
      if($('#block-section-menu .menu-item',this).length){
        $('#block-section-menu',this).addClass('show-nav');
      }else{
        $('.layout__region--first',this).remove();
      }
      $(document).ready(function() {
        var windowWidth = $(window).outerWidth();
        var $targetElement = $('.layout--twocol-sideleft'); 
        var $titleHeight = $('#section-menu-title').outerHeight();
        var viewportBottom = $(window).scrollTop() + $(window).height();
        var elementTop = $targetElement.offset().top;
        if (viewportBottom > elementTop) {
          $('#block-section-menu',$targetElement).addClass('post-intro');
        } else {
          $('#block-section-menu',$targetElement).removeClass('post-intro');
        }
        if(windowWidth > 1199){
          $('#block-section-menu',$targetElement).css('top','calc(100% - ' + $titleHeight + 'px)');
          // find menu height minus offset & spacing so can make sure there is enough body height to push down paragraphs
          var menuHeight = $('#block-section-menu',$targetElement).outerHeight() - 280;
          if(menuHeight > 0){
            $('.layout--twocol-sideleft:not(.layout-builder__layout) .block-field.block-body,.node-type-page.hero-none .page-header-text-inner .field-body').css('min-height',menuHeight + 'px');
            
          }
        }else{
          $('#block-section-menu',$targetElement).css('top','auto');
        }
        $(window).scroll(function() {
          var viewportBottom = $(window).scrollTop() + $(window).height();
          var elementTop = $targetElement.offset().top;
          if (viewportBottom > elementTop) {
            $('#block-section-menu',$targetElement).addClass('post-intro');
          } else {
            $('#block-section-menu',$targetElement).removeClass('post-intro');
          }
        });
      });
    });
  }
}

})(jQuery, Drupal, once);
