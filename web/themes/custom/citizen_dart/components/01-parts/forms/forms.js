(function($, Drupal, once) {

/* USER LOGIN PASSWORD SHOW
------------------------------------ */
Drupal.behaviors.userLogin = {
	attach: function (context, settings) {
		$(once('showPass', '#user-login-form', context)).each(function(){
      $('.show-password').click(function(e){
        e.preventDefault();
        if($(this).is('.show')){
          $(this).removeClass('show').text('Show');
          $('#edit-pass').attr('type', 'password');
        }else{
          $(this).addClass('show').text('Hide');
          $('#edit-pass').attr('type', 'text');
        }
      });
		});
	}
};

/* SELECT 2
------------------------------------ */
Drupal.behaviors.select2 = {
  attach: function (context, settings) {
    $(once('selects', 'select', context)).each(function(){
      $( 'form.views-exposed-form select,form.webform-submission-form select' ).select2({
        placeholder: "Select an option"
      });
      $(once('selectAccessiblity', '.js-form-type-select', context)).each(function(){
        $(document).ready(function(){
          $('.select2-search__field').each(function(){
            var label = $(this).closest('.select2-container').siblings('label').text();
            $(this).attr('aria-label',label).removeAttr('role');
            $(this).closest('.select2-selection').removeAttr('role');
          });
        });
      });
    });
   }
};

})(jQuery, Drupal, once);
