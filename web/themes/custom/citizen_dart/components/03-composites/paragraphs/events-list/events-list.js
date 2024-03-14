(function($, Drupal, once) {

/* REMOVE EMPTY EVENTS LIST
------------------ */
Drupal.behaviors.eventsList = {
  attach: function (context, settings) {
  	$(once('eventsList', '.paragraph--type--events-list', context)).each(function(){
      $(document).ready(function(){
        setTimeout(function() {
          $('.events-list').each(function(){
            let $listView = $(this);
            if(!($listView.children().length)){
              console.log('List removed', this);
              $listView.closest('.paragraph--type--events-list').remove();
            }
          });
        }, 1000);
      });
    });
  }
}

})(jQuery, Drupal, once);
