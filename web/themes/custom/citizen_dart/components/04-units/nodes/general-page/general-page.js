(function($, Drupal, once) {

/* GENERAL PAGE HELPER JS
------------------ */
Drupal.behaviors.gpContact = {
  attach: function (context, settings) {
    $(once('gpContact', '.block-field-page-family', context)).each(function(){
      !$('.field-page-family',this).children().length ? $(this).closest('.block-field').remove() : '';
      !$('#node-section-3 > .layout > .layout__region').children().length ? $('#node-section-3').remove() : '';
    });
  }
}

Drupal.behaviors.additionalLinksCleanup = {
  attach: function (context, settings) {
    $(once('addLinks', '.paragraph--type--links-files', context)).each(function(){
      !$('.field.field-link-multi',this).children().length ? $(this).remove() : '';
    });
  }
}

Drupal.behaviors.heroAdjustments = {
  attach: function (context, settings) {
    $(once('heroTweaks', '.page-header-wrapper.has-hero', context)).each(function(){
      const windowW = $(window).outerWidth();
      const windowH = $(window).outerHeight();
      if(windowH > windowW){
        $(this).addClass('vertical-window');
      }
    });
  }
}

})(jQuery, Drupal, once);
