(function ($, Drupal, once) {

  /* LIST WIDGETS FIELD CONTROLS
  ----------------------- */
  Drupal.behaviors.listWidget = {
    attach: function (context, settings) {
    	$(once('isListWidget', '.field--name-field-list-type .js-form-type-select', context)).each(function(){
 
 				let manualFields = $('.field--name-field-manual-select,.field--name-field-testimonial,.field--name-field-facts,.field--name-field-profiles,.field--name-field-programs');

        let autoFields = $('.field--name-field-placement-tag,.field--name-field-page-family,.field--name-field-type,.field--name-field-program,.field--name-field-snapshot-type,.field--name-field-limit-list,.field--name-field-randomize');

        $(document).ajaxComplete(function () {
        // detect the chosen list type and show the proper select or manual field options
          $('.field--name-field-list-type .js-form-type-select').each(function () {
          	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(manualFields).hide()
            //when the content type select is changed
            $(this).find('select').change(function () {
              //get the option
              var chosen = $(this).find("option:selected").text().toLowerCase().replace(/_/g, '-');
              
              if (chosen == 'auto'){
              	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(manualFields).hide();
              	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(autoFields).show();
              }else{
              	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(manualFields).show();
              	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(autoFields).hide();
              }
            });
          });
          //when an existing content placer is opened, run the same checks
          if($('.field--name-field-list-type select').val()){
            var chosen = $('.field--name-field-list-type select').find("option:selected").text().toLowerCase().replace(/_/g, '-');
            if (chosen == 'auto'){
            	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(manualFields).hide();
            	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(autoFields).show();
            }else{
            	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(manualFields).show();
            	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(autoFields).hide();
            }
          }
        });
      });
    }
  };
  Drupal.behaviors.wayfindingWidget = {
    attach: function (context, settings) {
      $(once('isWayfindingWidget', '.field--name-field-version .js-form-type-select', context)).each(function(){
      let comboFields = $('.field--name-field-link-multi');

      let singleDetailFields = $('.field--name-field-link,.field--name-field-image');

      let detailFields = $('.field--name-field-text-placement');

      //hide combo & detail fields by default
      comboFields.hide();
      detailFields.hide(); 

       $(document).ajaxComplete(function () {
        // detect the chosen list type and show the proper select or manual field options
        $('.field--name-field-version .js-form-type-select').each(function () {
          //when the content type select is changed
          $(this).find('select').change(function () {
            //get the option
            var chosen = $(this).find("option:selected").text().toLowerCase().replace(/_/g, '-');
            
            if (chosen == 'combo'){
              singleDetailFields.hide();
              comboFields.show();
              detailFields.hide();
            } else if (chosen == 'detail'){
              detailFields.show();
              singleDetailFields.show();
              comboFields.hide();
            } else {
              singleDetailFields.show();
              comboFields.hide();
              detailFields.hide();
            }
          });
        });
         //when an existing content placer is opened, run the same checks
        if($('.field--name-field-version select').val()){
          var chosen = $('.field--name-field-version select').find("option:selected").text().toLowerCase().replace(/_/g, '-');
          if (chosen == 'combo'){
            singleDetailFields.hide();
            comboFields.show();
            detailFields.hide();
          } else if (chosen == 'detail'){
            detailFields.show();
            singleDetailFields.show();
            comboFields.hide();
          } else {
            singleDetailFields.show();
            comboFields.hide();
            detailFields.hide();
          }
         }
       });
     });
    }
  };

  /* Add paragraph preview labels
  ----------------------- */
  Drupal.behaviors.previewLabel = {
    attach: function (context, settings) {
    	$(once('isParaPreview', '.lp-builder .paragraph--view-mode--preview', context)).each(function(){
    		var label = $(this).attr('data-type');
    		if (typeof label !== 'undefined') {
	    		$(this).prepend('<div class="para-preview-label">' + label.replace(/_/g, ' ') + ' widget</div>');
	    	}
    	});
    }
  };

})(jQuery, Drupal, once);
