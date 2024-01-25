(function ($, Drupal, once) {

  /* LIST WIDGETS FIELD CONTROLS
  ----------------------- */
  Drupal.behaviors.listWidget = {
    attach: function (context, settings) {
    	$(once('isListWidget', '.field--name-field-list-type .js-form-type-select', context)).each(function(){
 
 				let manualFields = $('.field--name-field-manual-select,.field--name-field-testimonial,.field--name-field-facts,.field--name-field-profiles,.field--name-field-programs');

        let autoFields = $('.field--name-field-placement-tag,.field--name-field-page-family,.field--name-field-type,.field--name-field-program,.field--name-field-snapshot-type,.field--name-field-limit-list,.field--name-field-randomize,.field--name-field-college,.field--name-field-department,.field--name-field-program');

        $(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(manualFields).hide();

        $(document).ajaxComplete(function () {
        // detect the chosen list type and show the proper select or manual field options
          $('.field--name-field-list-type .js-form-type-select').each(function () {
          	
            
          	//when an existing content placer is opened, check the type chosen and show/hide the appropriate fields 
	          if($(this).find("option:selected").val()){
	            var chosen = $(this).find("option:selected").text().toLowerCase().replace(/_/g, '-');
	            if (chosen == 'auto'){
	            	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(manualFields).hide();
	            	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(autoFields).show();
	            }else{
	            	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(autoFields).hide();
	            	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(manualFields).show();
	            }
	          }

            //when the content type select is changed run the same checks
            $(this).find('select').change(function () {
            	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(manualFields).hide()
              //get the option
              var choice = $(this).find("option:selected").text().toLowerCase().replace(/_/g, '-');
              
              if (choice == 'auto'){
              	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(manualFields).hide();
              	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(autoFields).show();
              }else{
              	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(autoFields).hide();
              	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(manualFields).show();
              }
            });

          });
        });//end ajax complete
      });
    }
  };//end list widget

  Drupal.behaviors.wayfindingWidget = {
    attach: function (context, settings) {
      $(once('isWayfindingWidget', '.field--name-field-version .js-form-type-select', context)).each(function(){
      
      let comboFields = $('.field--name-field-link-multi');
      let boxSingleFields = $('.field--name-field-link,.field--name-field-image');
      let singleFields = $('.field--name-field-text-placement');

      //hide combo by default
      $(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(comboFields).hide();

       $(document).ajaxComplete(function () {

       	//when an existing wayfind is opened, ckeck the version and set show the correct fields
       	if($('.field--name-field-version .js-form-type-select', this).find("option:selected").val()){
	        var chosen = $('.field--name-field-version .js-form-type-select', this).find("option:selected").text().toLowerCase().replace(/_/g, '-');
	        if (chosen == 'combo'){
            boxSingleFields.hide();
            singleFields.hide();
            comboFields.show();
          } else if (chosen == 'single'){
            singleFields.show();
            boxSingleFields.show();
            comboFields.hide();
          } else {
          	comboFields.hide();
            singleFields.hide();
            boxSingleFields.show();
          }
	      }

        // detect the chosen list type and show the proper select or manual field options
        $('.field--name-field-version .js-form-type-select').each(function () {
          //when the content type select is changed
          $(this).find('select').change(function () {
            //get the option
            var choice = $(this).find("option:selected").text().toLowerCase().replace(/_/g, '-');
            
            if (choice == 'combo'){
	            boxSingleFields.hide();
	            singleFields.hide();
	            comboFields.show();
	          } else if (choice == 'single'){
	            singleFields.show();
	            boxSingleFields.show();
	            comboFields.hide();
	          } else {
	          	comboFields.hide();
	            singleFields.hide();
	            boxSingleFields.show();
	          }
          });
        });
         
       });//end ajax complete
     });
    }
  };//end wayfinding

  Drupal.behaviors.ctaWidget = {
    attach: function (context, settings) {
      $(once('isCTAWidget', '.field--name-field-is-trio .js-form-type-checkbox', context)).each(function(){
      
      let barField = $('.field--name-field-cta-bar');
      let trioField = $('.field--name-field-cta-items');

      $(this).closest('.layout-paragraphs-component-form').find(trioField).hide();

       $(document).ajaxComplete(function () {

       	//when an existing wayfind is opened, ckeck the version and set show the correct fields
       	if($('.field--name-field-is-trio .form-boolean--type-checkbox', this).is(':checked')){
       		console.log('checked');
	       	$('.layout-paragraphs-component-form').find(trioField).show();
	       	$('.layout-paragraphs-component-form').find(barField).hide();
	      }else{
	      	console.log('checked');
	      	$('.layout-paragraphs-component-form').find(trioField).hide();
	       	$('.layout-paragraphs-component-form').find(barField).show();
	      }

        // detect the chosen list type and show the proper select or manual field options
        $('.field--name-field-is-trio .form-boolean--type-checkbox').each(function () {
          //when the content type select is changed
          $(this).change(function () {
            //get the option
            if($(this).is(':checked')){
            	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(trioField).show();
	       			$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(barField).hide();
            }else{
            	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(trioField).hide();
	       			$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(barField).show();
            }

          });
        });
         
       });//end ajax complete
     });
    }
  };//end cta

  Drupal.behaviors.slateWidget = {
    attach: function (context, settings) {
      $(once('isSlateForm', '.form-item--field-script-tag-type', context)).each(function(){

      //custom field by default
      $('.field--name-field-slate-script-tag').hide();


       $(document).ajaxComplete(function () {
        // detect the chosen list type and show the proper select or manual field options
        $('.layout-paragraphs-component-form .form-item--field-script-tag-type').each(function () {
          //when the content type select is changed
          $(this).find('select').change(function () {
            //get the option
            var chosen = $(this).find("option:selected").text().toLowerCase().replace(/_/g, '-');
            
            if (chosen == 'custom'){
             	$('.field--name-field-slate-script-tag').show();
            } else {
              $('.field--name-field-slate-script-tag').hide();
            }
          });
        });
         //when an existing content placer is opened, run the same checks
        if($('.layout-paragraphs-component-form .form-item--field-script-tag-type select').val()){
          var chosen = $('.layout-paragraphs-component-form .form-item--field-script-tag-type select').find("option:selected").text().toLowerCase().replace(/_/g, '-');
          if (chosen == 'custom'){
           	$('.field--name-field-slate-script-tag').show();
          } else {
            $('.field--name-field-slate-script-tag').hide();
          }
         }
       });//end ajax complete
     });
    }
  };//end slate

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
