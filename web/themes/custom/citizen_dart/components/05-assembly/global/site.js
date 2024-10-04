(function($, Drupal, once, cookies) {

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

/* FOOTER ANIMATIONS
------------------ */
Drupal.behaviors.footerAnimate = {
  attach: function (context, settings) {
  	$(once('footerAnimations', '.prefooter-wrapper', context)).each(function(){
      // Function to handle the intersection observer callback
      function handleIntersection(entries, observer) {
        entries.forEach(entry => {
          if ($("body").hasClass("animations-paused")) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
          else if (entry.isIntersecting) {
            //add class when target is visible
            entry.target.classList.add('visible');
            observer.unobserve(entry.target); // Stop observing once the class is added
          }
        });
      }
      // Create an intersection observer
      const observer = new IntersectionObserver(handleIntersection, { threshold: 0.5 });
      // Select the target element
      const targetElement = document.querySelector('.prefooter-wrapper');
      // Start observing the target element
      observer.observe(targetElement);
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

Drupal.behaviors.mobileMenu = {
		attach: function (context, settings) {
			$(once('insertHeaderElements', '.block-superfishmain', context)).each(function() {
				$(document).ready(function() {
					$(window).on('resize', debounce(mobileMenuInsert, 10)).trigger('resize');
				});
			});
		}
	};

//function to move blocks to mobile menu
function mobileMenuInsert() {
	var wwidth = $(window).outerWidth();
	if (wwidth < 1200) {
		//if not in desktop or higher, clone the search and secondary menu and move them to the mobile menu
		if(!$('.sf-accordion > li.mobile-search-container').length){
			var $searchContainer = $('.site-header .search-box-wrapper');

			// Continue with cloning and moving
			$searchContainer.clone(true)
				.prependTo('.sf-accordion')
				.wrap('<li class="mobile-search-container"></li>')
				.addClass('mobile-search');

      setTimeout(function() {
        $('.mobile-search-container form.gsc-search-box label').remove();
        if(!$('.sf-accordion > li.mobile-search-container label').length){
          $('.mobile-search-container form.gsc-search-box').prepend('<label for="gsc-i-id1">Search</label>');
        }else{
          $('.sf-accordion > li.mobile-search-container label').text('Search');
        }
        $('.mobile-search-container input.gsc-input').attr('placeholder', 'What are you searching for?');
      }, 510);

      var $secondaryMenu = $('.site-header #block-secondary-menu > ul');

      // Continue with cloning and moving
			$secondaryMenu.clone(true)
				.appendTo('.sf-accordion')
				.wrap('<li class="mobile-secondary-container"></li>')
				.addClass('mobile-secondary');
		}
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
				$('#gpa-error').append('With the credits you will be taking, you are guaranteed to attain your desired GPA.  Breathe easy!');
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
    const pause_lang = Drupal.t("Pause all animations");
    const play_lang = Drupal.t("Play all animations");

    // All animations are controlled via classes in the body tag.
    // Generally, .animations-paused is checked by the various JS files that add
    // some sort of "visible" class on scroll. If animations are paused, that
    // class is added immediately insteead of waiting.
    // On the other hand, .animations-playing is checked by the CSS to see if
    // they should play various fade-in animations, or if they should just be
    // opaque by default.

    $(once('animationsState', 'body', context)).each(function() {
      const animationCookie = cookies.get('uwecAnimationsPlay');
      const contentWrapper = $(".main-page-content", this);
      let defaultState = animationCookie ? animationCookie : "playing";

      const animationsButton = $(`<a href="#" class="animations-button" title="${pickButtonLanguage(defaultState)}" aria-label="${pickButtonLanguage(defaultState)}"></a>`);

      $(this).addClass("animations-" + defaultState);

      animationsButton.on("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        // Simply toggle the state onclick. Everything else is keyed from this
        // one thing.
        defaultState = defaultState === "playing" ? "paused" : "playing";
        $(this).toggleClass("animations-paused");
        $(this).toggleClass("animations-playing");

        // Don't forget to update the ARIA language.
        animationsButton.attr("aria-label", pickButtonLanguage(defaultState));
        animationsButton.attr("title", pickButtonLanguage(defaultState));
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

})(jQuery, Drupal, once, window.Cookies);
