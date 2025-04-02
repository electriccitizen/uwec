(function($, Drupal, once) {

  Drupal.behaviors.hours = {
    attach: function (context, settings) {
      $(once('hours', '.field-hours.entity-reference-revisions', context)).each(function(){
        const hasSelectPanel = $('select.panel-select', this).length;
        //show the first panel on load no matter way
        $('.select-panel:first-of-type').addClass('show-panel');

        //change visibility of hours listings
        $('.panel-select', this).change(function(e){
          e.preventDefault();

          // visually hide all hours
          $(this).closest('.field-hours').find('.select-panel').removeClass('show-panel');

          // show selected hours
          let activeHours = $(this).children('option:selected').val();
          $('.' + activeHours).addClass('show-panel');
        });
      });
    }
  }

})(jQuery, Drupal, once);
