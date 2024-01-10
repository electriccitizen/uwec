(function($, Drupal, once) {

/* DRAWERS (SHOW MORE)
------------------------------------ */

Drupal.behaviors.drawerBelow = {
  attach: function (context, settings) {
    $(once('modal_window', '.drawer-toggle', context)).each(function(e){
      $(this).click(function(e){
        e.preventDefault();
        if($(this).closest('.field').hasClass('field-dates')){
          var toggleText = 'dates';
        }else{
          var toggleText = 'links';
        }
        if(!$(this).is('.active-drawer')){
          $(this).next('.drawer-content').slideDown(300).attr('aria-hidden','false').addClass('open-drawer').end().text('See fewer ' + toggleText).addClass('active-drawer').attr('aria-expanded', 'true');
        }else{
          $(this).next('.drawer-content').slideUp(300).attr('aria-hidden','true').removeClass('open-drawer').end().text('See all ' + toggleText).removeClass('active-drawer').attr('aria-expanded', 'false');
        }
      });
    });
  }
};

Drupal.behaviors.filterDrawer = {
  attach: function (context, settings) {
    $(once('advancedForm','.views-exposed-form .filter-toggle', context)).each(function(e){
    	var urlCurrent = window.location.href;

    	if(urlCurrent.indexOf("?p") > -1){
    		$(this).next('#advanced-filters').show().attr('aria-hidden','false').addClass('open-drawer').end().text('Simple Search').addClass('active-drawer').attr('aria-expanded', 'true');
    	}
      $(this).click(function(e){
        e.preventDefault();
        if(!$(this).is('.active-drawer')){
          $(this).next('#advanced-filters').slideDown(300).attr('aria-hidden','false').addClass('open-drawer').end().text('Simple Search').addClass('active-drawer').attr('aria-expanded', 'true');
        }else{
          $(this).next('#advanced-filters').slideUp(300).attr('aria-hidden','true').removeClass('open-drawer').end().text('Advanced Search').removeClass('active-drawer').attr('aria-expanded', 'false');
        }
      });
    });
  }
};

})(jQuery, Drupal, once);
