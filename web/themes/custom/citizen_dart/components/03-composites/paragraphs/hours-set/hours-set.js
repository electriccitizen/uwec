(function($, Drupal, once) {

  Drupal.behaviors.hours = {
    attach: function (context, settings) {
      $(once('hours', '.field-hours.entity-reference-revisions', context)).each(function(){
        const hasSelectPanel = $('select.panel-select', this).length;
        //show the first panel on load no matter way
      	$('.select-panel:first-of-type').addClass('show-panel');
        //hide the set label since we're using it in the panel select
        if (hasSelectPanel) {
          $('.select-panel .field-set-title', this).addClass('visually-hidden');
        }
        //change visibility of hours listings
        $('.panel-select', this).change(function(e){
          e.preventDefault();
          // visually hide all hours
          $(this).siblings('.select-panel').removeClass('show-panel');
          let activeHours = $(this).children('option:selected').val();
          // show selected hours
          $('.' + activeHours).addClass('show-panel');
        });
      });
    }
  }

})(jQuery, Drupal, once);
