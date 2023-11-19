(function($, Drupal, once) {

/* Remove List View if no nodes are listed
------------------------------------ */
Drupal.behaviors.removeListView = {
  attach: function (context, settings) {
    $(once('noPublishedNodes', '.custom-views-list', context)).each(function(){
      let $listView = $('.custom-views-list');
      for (let i = 0; i < $listView.length; i++){
        let $items = $($listView[i]).find('article');
        if ($items.length == 0){
          $($listView[i]).remove();
        }
      }
    });
  }
};

})(jQuery, Drupal, once);
