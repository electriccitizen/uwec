(function($, Drupal, once) {

/* LAYOUT 
------------------ */
Drupal.behaviors.removeEmptyRegions = {
  attach: function (context, settings) {
    $(once('removeEmpty', '.layout > .layout__region:not(.layout-builder__region)', context)).each(function(){
      if(!$(this).children().length){
        $(this).remove();
      }
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
            if (entry.isIntersecting) {
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
        $('.overflow-guard .dialog-off-canvas-main-canvas').animate({
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
      
          if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
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
	if (wwidth < 984) {
		//if not in desktop or higher, clone the search and secondary menu and move them to the mobile menu
		if(!$('.sf-accordion > li.mobile-search-container').length){
			var $searchContainer = $('.site-header .search-box-wrapper');

			// Continue with cloning and moving
			$searchContainer.clone(true)
				.prependTo('.sf-accordion')
				.wrap('<li class="mobile-search-container"></li>')
				.addClass('mobile-search');

			$('.mobile-search-container form.gsc-search-box label').remove();
			if(!$('.sf-accordion > li.mobile-search-container label').length){
	      $('.mobile-search-container form.gsc-search-box').prepend('<label for="gsc-i-id1">Search</label>');
	    }else{
	    	$('.sf-accordion > li.mobile-search-container label').text('Search');
	    }
      $('.mobile-search-container input.gsc-input').attr('placeholder', 'What are you searching for?');

      var $secondaryMenu = $('.site-header #block-secondary-menu > ul');

      // Continue with cloning and moving
			$secondaryMenu.clone(true)
				.appendTo('.sf-accordion')
				.wrap('<li class="mobile-secondary-container"></li>')
				.addClass('mobile-secondary');
		}
	}
}

})(jQuery, Drupal, once);
