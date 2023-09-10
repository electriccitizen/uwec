(function($, Drupal, once) {

  Drupal.behaviors.hours = {
    attach: function (context, settings) {
      $(once('hours', '.field-hours', context)).each(function(){
        //change visibility of hours listings
        $('.panel-select', this).change(function(e){
          e.preventDefault();
          // visually hide all hours
          $('.select-panel').addClass('visually-hidden');
          let activeHours = $(this).children('option:selected').val();
          // show selected hours
          $('.' + activeHours).removeClass('visually-hidden');
        });
      });
    }
  }

})(jQuery, Drupal, once);
