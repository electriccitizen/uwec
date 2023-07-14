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

})(jQuery, Drupal, once);
