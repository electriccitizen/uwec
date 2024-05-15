(function($, Drupal, once) {

  Drupal.behaviors.programToggle = {
    attach: function (context, settings) {
      $(once('program', '.academic-program-teaser', context)).each(function(){
        const teaserHeight = $(this).outerHeight() + 'px';
        $(this).css('min-height',teaserHeight);
        $('.program-toggle',this).click(function(e){
          e.preventDefault();
          if($(this).hasClass('open')){
            $(this).attr('aria-expanded', "false")
              .removeClass('open')
              .closest('.program-types')
              .prev('.program-body')
              .slideUp(300, function() {
                // This function is called after the slideUp animation completes
                $(this).attr('aria-hidden', "true")
                    .closest('.academic-program-teaser')
                    .removeClass('open-program');
            });
          }else{
            $(this).closest('.academic-program-teaser').addClass('open-program');
            $(this).attr('aria-expanded', "true").addClass('open').closest('.program-types').prev('.program-body').slideDown(300).attr('aria-hidden', "false");
          }
        });
      });
    }
  }

})(jQuery, Drupal, once);