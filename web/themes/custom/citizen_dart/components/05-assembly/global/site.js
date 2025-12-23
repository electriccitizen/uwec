(function($, Drupal, once) {

/* LAYOUT
------------------ */
Drupal.behaviors.removeEmptyRegions = {
  attach: function (context, settings) {
    $(once('removeEmpty', '.layout > .layout__region:not(.layout-builder__region)', context)).each(function(){
      if(!$(this).children().length){
        $(this).remove();
      }
      $(document).on('click', 'a#layout-content', function(e) {
        e.preventDefault();
    });
    });
    $(once('removeEmptyContact', '.node-bios #node-section-2,.node-department #node-section-4,.node-college #node-section-4', context)).each(function(){
      if(!$('.field',this).length){
        $(this).remove();
      }
    });
  }
}

/* BACK TO TOP
------------------ */
Drupal.behaviors.backToTop = {
  attach: function (context, settings) {
  	$(once('backTop', 'html.js', context)).each(function(){
      //scroll to toc
      $('.back-anchor a').click(function(e){
        e.preventDefault();
        $('html,body').animate({
          scrollTop: $('.layout-container').offset().top - 10
        });
      });
    });
  }
}

/* SMOOTH SCROLL TO ANCHOR LINKS
------------------ */
Drupal.behaviors.smoothAnchorator = {
  attach: function (context, settings) {
  	$(once('smoothAnchors', '#block-citizen-dart-content', context)).each(function(){
      //find links in fields on the page
      const links = document.querySelectorAll('.field a');

      links.forEach(function(link) {
        if (link.getAttribute('href').match(/^#[a-zA-Z]/)) {
          link.addEventListener('click', function(event) {
          event.preventDefault();

          const targetId = link.getAttribute('href').substring(1);
          const targetElement = document.getElementById(targetId);

          let scrollPosition = 'center';
          if(targetId == 'scroll-anchor'){
            scrollPosition = 'start';
          }

          if (targetElement) {
            targetElement.scrollIntoView({
              behavior: 'smooth',
              block: scrollPosition,
            });
          }
        });
        }
      });
    });
  }
}

// GPA Calculator and "Raise your GPA"
Drupal.behaviors.gpaCalculator = {
  attach: function (context, settings) {

	// GPA Estimator
	$('#gpa-estimator input').keyup(function(e) {

		// Ignore arrows
		switch (e.which) {
			case 37: case 38: case 39: case 40:
			return;
		}

		// These will contain our accumulating values
		var totalCredits = 0, totalPoints = 0;

		// Get all values
		$('#gpa-estimator tbody tr').each(function() {

			// Grab the grade value
			var gradeVal = parseFloat($(this).find('td:nth-child(2)').text());

			// Grab and update the credits value
			var input = $(this).find('input');
			var creditVal = parseInt(input.val());
			if (!isNaN(creditVal)) {

				// Put parsed value back (and make sure it's positive)
				if (creditVal < 0) creditVal = 0;
				input.val(creditVal);

				// Accumulate credits
				totalCredits += creditVal;

				// Accumulate points
				totalPoints += creditVal * gradeVal;
			}
		});

		// Calculate the final GPA
		var totalGPA = (totalCredits > 0)? (totalPoints / totalCredits).toFixed(2) : 'x.xx';
		var totalPoints = totalPoints.toFixed(2);

		// Display stuff
		$('#gpa-probable').html(totalGPA);
		$('#gpa-points').html(totalPoints);
	});

	// Raise Your GPA
	$('#gpa-raise input').keyup(function(e) {

		// Ignore arrows
		switch (e.which) {
			case 37: case 38: case 39: case 40:
			return;
		}

		// Clear messages / classes
		$('#gpa-needed,#gpa-current').removeClass('unattainable');
		$('#gpa-error').html('');

		// Get all values
		var vals = {};
		$('#gpa-raise tbody input').each(function() {
			var input = $(this);

			// Get and validate this input value
			var val = input.val();
			if (val != '') {
				val = parseFloat(val);
				if (isNaN(val) || val < 0) val = 0;
				if (val != input.val()) input.val(val); // Send back to form
			}

			// Save it for later
			vals[input.attr('name')] = (val=='')? 0 : val;
		});

		// Check desired GPA value
		if (vals.wantedGPA > 4) {
			vals.wantedGPA = 4;
			$('input[name="wantedGPA"]').val('4.00');
			$('#gpa-error').append('Your desired GPA cannot be higher than 4.00.<br />');
		}

		// Calculate current GPA
		if (vals.totalCredits > 0) {
			var currentGPA = vals.totalPoints / vals.totalCredits;
			if (currentGPA < 0 || currentGPA > 4) {
				$('#gpa-current').addClass('unattainable');
				$('#gpa-error').append('Your current GPA is not actually possible.  Please double-check your credits and grade points.<br />');
			}
			$('#gpa-current').html(currentGPA.toFixed(2));
		} else {
			$('#gpa-current').html('x.xx');
		}

		// Only continue with calcualtions if there are credits to be taken yet
		if (vals.futureCredits > 0) {

			// Calculate the required grade point average
			var wantedPoints = (vals.totalCredits + vals.futureCredits - vals.repeatCredits) * vals.wantedGPA;
			var neededGPA = ((wantedPoints - vals.totalPoints + vals.repeatPoints) / vals.futureCredits).toFixed(2);

			// Make sure the needed GPA is reasonable
			if (neededGPA <= 0) {
				neededGPA = '0.00';
				$('#gpa-error').append('The GPA you would need is attainable with the credits you will be taking.');
			} else if (neededGPA > 4) {
				$('#gpa-needed').addClass('unattainable');
				$('#gpa-error').append('The GPA you would need is not attainable with the given credits, so please contact your advisor to discuss your options.');
			}

			// Display stuff
			$('#gpa-needed').html(neededGPA);

		} else {
			$('#gpa-needed').html('x.xx');
			if ($('input[name="futureCredits"]').val()!='') {
				$('#gpa-error').append('Please enter the number of credits you will be taking.  You can\'t raise your GPA without credits!');
			}
		}
	});
  }
}

// "Pause Animations" button
Drupal.behaviors.pauseAnimations = {
  attach: function (context, settings) {
    const pause_lang = Drupal.t("Pause Animation");
    const play_lang = Drupal.t("Play Animation");
    const animationStorageKey = "uwecAnimationsPlay";

    // All animations are controlled via classes in the body tag.
    // Generally, .animations-paused is checked by the various JS files that add
    // some sort of "visible" class on scroll. If animations are paused, that
    // class is added immediately instead of waiting.
    // On the other hand, .animations-playing is checked by the CSS to see if
    // they should play various fade-in animations, or if they should just be
    // opaque by default.

    $(once('animationsState', 'html', context)).each(function() {
      let animationState = localStorage.getItem(animationStorageKey) || 'playing';
      const contentWrapper = $("#block-footer-ancillary-menu > ul", this);

      const animationsButton = $(`<li><a href="#" class="animations-button"><span>${pickButtonLanguage(animationState)}</span></a></li>`);
      $(this).addClass("animations-" + animationState);

      animationsButton.on("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        // Simply toggle the state on click. Everything else is keyed from this one variable.
        animationState = animationState == "playing" ? "paused" : "playing";
        localStorage.setItem(animationStorageKey, animationState);
        if(animationState == 'playing'){
          $(this).addClass('animations-playing');
          $(this).removeClass('animations-paused');
        }else{
          $(this).addClass('animations-paused');
          $(this).removeClass('animations-playing');
        }

        // Don't forget to update the ARIA language.
        animationsButton.find('span').text(pickButtonLanguage(animationState));
      });
      contentWrapper.append(animationsButton);
    });

    // Return the pause/play language based off of the current state.
    // Expects the state to be "playing" if the video is currently playing, or
    // any other state if it's paused.
    function pickButtonLanguage(state) {
      return state === "playing" ? pause_lang : play_lang;
    }
  }
}

/* COOKIE COMPLIANCE TAB INDEX
------------------ */
Drupal.behaviors.cookieTabs = {
  attach: function (context, settings) {
  	$(once('cookieIndexWatcher', 'body', context)).each(function(){
      var baseTabIndex = 9994;

      function applyTabOrder($banner) {
        if (!$banner.length || $banner.data('cookie-tabbed')) {
          return;
        }

        var $focusables = $banner.find('a, button, input, [role="button"], [tabindex]').filter(function() {
          var $el = $(this);
          var isHidden = !$el.is(':visible') || $el.attr('aria-hidden') === 'true';
          var isDisabled = this.disabled;
          return !isHidden && !isDisabled;
        });

        $focusables.each(function(index) {
          $(this).attr('tabindex', baseTabIndex + index);
        });

        $banner.data('cookie-tabbed', true);
      }

      // If the banner is already present, apply immediately.
      applyTabOrder($('.eu-cookie-compliance-banner', context));

      // Watch for the banner to be injected later.
      var observer = new MutationObserver(function(mutations, obs) {
        mutations.forEach(function(mutation) {
          $(mutation.addedNodes).each(function() {
            var $node = $(this);
            if ($node.hasClass('eu-cookie-compliance-banner')) {
              applyTabOrder($node);
              obs.disconnect();
            } else {
              var $banner = $node.find('.eu-cookie-compliance-banner');
              if ($banner.length) {
                applyTabOrder($banner);
                obs.disconnect();
              }
            }
          });
        });
      });

      observer.observe(document.body, { childList: true, subtree: true });
    });
  }
}

})(jQuery, Drupal, once);
