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

/* Remove Story and Snapshot List View if there are less than items
------------------------------------ */
Drupal.behaviors.removeShortList = {
  attach: function (context, settings) {
    $(once('shortSnaps', '.paragraph--type--snapshots-list,.paragraph--type--stories-video-list', context)).each(function(){
    	$('.paragraph--type--snapshots-list,.paragraph--type--stories-video-list').each(function(){
    		var count = $('.node-teaser',this).length;
    		if (count < 3){
	        $(this).remove();
	      }
    	});
    });
  }
};

})(jQuery, Drupal, once);
