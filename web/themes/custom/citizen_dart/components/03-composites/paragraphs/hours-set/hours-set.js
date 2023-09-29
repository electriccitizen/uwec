(function($, Drupal, once) {

  Drupal.behaviors.hours = {
    attach: function (context, settings) {
      $(once('hours', '.field-hours', context)).each(function(){
      	$('.select-panel:first-of-type').addClass('show-panel');
        //change visibility of hours listings
        $('.panel-select', this).change(function(e){
          e.preventDefault();
          // visually hide all hours
          $('.select-panel').removeClass('show-panel');
          let activeHours = $(this).children('option:selected').val();
          // show selected hours
          $('.' + activeHours).addClass('show-panel');
        });
      });
    }
  }

})(jQuery, Drupal, once);
