(function ($, Drupal, once) {

  /* LIST WIDGETS FIELD CONTROLS
  ----------------------- */
  Drupal.behaviors.listWidget = {
    attach: function (context, settings) {
    	$(once('isListWidget', '.field--name-field-list-type .js-form-type-select', context)).each(function(){
 
 				let manualFields = $('.field--name-field-manual-select,.field--name-field-testimonial,.field--name-field-facts,.field--name-field-profiles,.field--name-field-programs');

        let autoFields = $('.field--name-field-placement-tag,.field--name-field-page-family,.field--name-field-type,.field--name-field-program,.field--name-field-snapshot-type,.field--name-field-limit-list,.field--name-field-randomize,.field--name-field-college,.field--name-field-department,.field--name-field-program,.field--name-field-office,.field--name-field-degree-type,.field--name-field-program-type,.field--name-field-campus,.field--name-field-degree-level');

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
      let singleFields = $('.field--name-field-text-placement,.field--name-field-no-background');

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
      let titleField = $('.field--name-field-widget-title');

      $(this).closest('.layout-paragraphs-component-form').find(trioField).hide();
      $(this).closest('.layout-paragraphs-component-form').find(titleField).hide();

       $(document).ajaxComplete(function () {

       	//when an existing wayfind is opened, ckeck the version and set show the correct fields
       	if($('.field--name-field-is-trio .form-boolean--type-checkbox', this).is(':checked')){
	       	$('.layout-paragraphs-component-form').find(trioField).show();
	       	$('.layout-paragraphs-component-form').find(barField).hide();
          $('.layout-paragraphs-component-form').find(titleField).show();
	      }else{
	      	$('.layout-paragraphs-component-form').find(trioField).hide();
	       	$('.layout-paragraphs-component-form').find(barField).show();
          $('.layout-paragraphs-component-form').find(titleField).hide();
	      }

        // detect the chosen list type and show the proper select or manual field options
        $('.field--name-field-is-trio .form-boolean--type-checkbox').each(function () {
          //when the content type select is changed
          $(this).change(function () {
            //get the option
            if($(this).is(':checked')){
            	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(trioField).show();
	       			$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(barField).hide();
              $(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(titleField).show();
            }else{
            	$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(trioField).hide();
	       			$(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(barField).show();
              $(this).closest('.paragraphs-subform,.layout-paragraphs-component-form').find(titleField).hide();
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

  Drupal.behaviors.stepWidget = {
    attach: function (context, settings) {

      function toggleFields(fields, display){
        for(var i = 0; i < fields.length; i++){
          fields[i].style.display = display; 
        }
      }
      // Select all the checkbox elements with the class 'js-form-type-checkbox' under '.field--name-field-use-icon'
      $(once('isStepForm', '.field--name-field-use-icon .js-form-type-checkbox', context)).each(function () {
        // Select the checkbox element with the class 'form-boolean--type-checkbox' within the current iteration
        let checkbox = $(this).find('.form-boolean--type-checkbox');
       
        $(document).ajaxComplete(function () {
          let iconFields = document.querySelectorAll('.paragraph-type--step-item .field--name-field-icon');
          if(checkbox.is(':checked')){
            toggleFields(iconFields, "block");
          } else {
            toggleFields(iconFields, "none");
          }
          // Attach a change event handler to the checkbox
          checkbox.change(function () {
            // Toggle the display of iconFields based on the checkbox state
            if($(this).is(':checked')){
              toggleFields(iconFields, "block");
            } else {
              toggleFields(iconFields, "none");
            }
          });
        });
      });
    }
  }; //end step

  Drupal.behaviors.hero = {
    attach: function (context, settings) {
      $(once('isHero', '.field--name-field-hero-type .js-form-type-select', context)).each(function(){
      
        let imageRightFields = $('.field--name-field-image,.field--name-field-links');
        let bgImageFields = $('.field--name-field-background-image,.field--name-field-image,.field--name-field-links');
        let fullImageFields = $('.field--name-field-background-image,.field--name-field-links');
        let videoFields = $('.field--name-field-background-image,.field--name-field-hero-video,.field--name-field-video-poster');
        let patternFields = $('.field--name-field-links');
  
        //hide all fields by default
        $(this).closest('.paragraphs-subform').find('.field--name-field-hero-type').siblings().hide();
  
         $(document).ajaxComplete(function () {
  
           //when an existing hero is opened, check the version and show the correct fields
           if($('.field--name-field-hero-type .js-form-type-select', this).find("option:selected").val()){
            var chosen = $('.field--name-field-hero-type .js-form-type-select', this).find("option:selected").val().replace(/_/g, '-');
            if (chosen == 'image'){
              bgImageFields.hide();
              fullImageFields.hide();
              videoFields.hide();
              patternFields.hide();
              imageRightFields.show();
            } else if (chosen == 'background-image'){
              imageRightFields.hide();
              fullImageFields.hide();
              videoFields.hide();
              patternFields.hide();
              bgImageFields.show();
            } else if (chosen == 'full-image'){
              imageRightFields.hide();
              bgImageFields.hide();
              videoFields.hide();
              patternFields.hide();
              fullImageFields.show();
            } else if (chosen == 'pattern'){
              imageRightFields.hide();
              bgImageFields.hide();
              videoFields.hide();
              fullImageFields.hide();
              patternFields.show();
            } else if (chosen == 'video'){
              imageRightFields.hide();
              bgImageFields.hide();
              fullImageFields.hide();
              patternFields.hide();
              videoFields.show();
            }
          }
  
          // detect the chosen list type and show the proper select or manual field options
          $('.field--name-field-hero-type .js-form-type-select').each(function () {
            //when the content type select is changed
            $(this).find('select').change(function () {
              //get the option
              var choice = $(this).find("option:selected").val().replace(/_/g, '-');
              
              if (choice == 'image'){
                bgImageFields.hide();
                fullImageFields.hide();
                videoFields.hide();
                patternFields.hide();
                imageRightFields.show();
              } else if (choice == 'background-image'){
                imageRightFields.hide();
                fullImageFields.hide();
                videoFields.hide();
                patternFields.hide();
                bgImageFields.show();
              } else if (choice == 'full-image'){
                imageRightFields.hide();
                bgImageFields.hide();
                videoFields.hide();
                patternFields.hide();
                fullImageFields.show();
              } else if (choice == 'pattern'){
                imageRightFields.hide();
                bgImageFields.hide();
                videoFields.hide();
                fullImageFields.hide();
                patternFields.show();
              } else if (choice == 'video'){
                imageRightFields.hide();
                bgImageFields.hide();
                fullImageFields.hide();
                patternFields.hide();
                videoFields.show();
              }
            });
          });
         
       });//end ajax complete
     });
    }
  };//end cta
  

	/* Add paragraph preview labels
	----------------------- */
	$(document).ajaxComplete(function(){
		// select all paragraph previews that do not already have a preview label
		$('.lp-builder .paragraph--view-mode--preview:not(:has(.para-preview-label))').each(function(){
			let label = $(this).find('.lpb-controls-label').html();
			if(label){
				$(this).prepend('<div class="para-preview-label">'+label+' Component</div>');
			}
		});
	});

   /* ADVANCED FILTER DRAWERS
  ----------------------- */
  Drupal.behaviors.advancedFilters = {
    attach: function (context, settings) {
    	$(once('advancedFilters', '.views-exposed-form .advanced-filters-drawer', context)).each(function(){
        $('.advanced-toggle',this).click(function(e){
          e.preventDefault();
          $('.views-exposed-form').toggleClass('move-actions');
          $(this).toggleClass('open').next('.advanced-filters-wrapper').slideToggle(500);
        });
        $('.advanced-filters-wrapper select').each(function(){
          if($(this).find("option:selected").text() != '- Any -'){
            $('.advanced-toggle').addClass('open');
            $('.advanced-filters-wrapper').show();
            $('.views-exposed-form').addClass('move-actions');
          }
        });
      });
    }
  };

})(jQuery, Drupal, once);
