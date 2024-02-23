(function($, Drupal, once) {
  Drupal.behaviors.superfishMenu = {
    attach: function (context, settings) {
      $(once('superfish-menu', '#superfish-main-toggle', context)).each(function(){
        var menuToggle = $(this);

        // add an image tag inside menuToggle
        menuToggle.append('<img src="/themes/custom/citizen_dart/images/elements/menu-open-white.svg" alt="Open Menu" class="open-menu-icon" />');
        menuToggle.append('<img src="/themes/custom/citizen_dart/images/elements/menu-close-white.svg" alt="Close Menu" class="close-menu-icon" />');

        $(window).on('resize', debounce(mobileSuperfish, 150)).trigger('resize');

      });
    }
  }

  function mobileSuperfish() {
    var wwidth = $(window).outerWidth();
    if (wwidth < 1200) {
      var menuButton = $('#superfish-main-toggle');

      // if image is not already inside menuToggle, add it
      if (!menuButton.find('.open-menu-icon').length) {
        menuButton.append('<img src="/themes/custom/citizen_dart/images/elements/menu-open-white.svg" alt="Open Menu" class="open-menu-icon" />');
        menuButton.append('<img src="/themes/custom/citizen_dart/images/elements/menu-close-white.svg" alt="Close Menu" class="close-menu-icon" />');
      }
    }
  };

})(jQuery, Drupal, once);
  