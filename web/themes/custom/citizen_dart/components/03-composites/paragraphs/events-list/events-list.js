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
              $listView.closest('.paragraph--type--events-list').remove();
            }
          });
        }, 3000);
      });
    });

    // generate href for "View all events" link
    document.querySelectorAll('.livewhale-view-all').forEach(function(ele){
      // don't do anything if there is already a link in this container
      if(ele.childElementCount > 0) return;

      // get gid from container data-gid
      let gid = ele.dataset.gid;
      if(!gid) return;

      fetch('http://calendar.uwec.edu/live/json/groups/id/'+gid)
      .then(response => response.json())
      .then(json => {
        if(json.length > 0){
          let group = json[0];
          let groupName = group.fullname;

          let a = document.createElement('a');
          a.href = 'https://calendar.uwec.edu/all/groups/'+groupName;
          a.innerText = 'View all '+groupName+' events';
          a.target = '_blank';

          ele.appendChild(a);
        }
      });
    });
  }
}

})(jQuery, Drupal, once);

