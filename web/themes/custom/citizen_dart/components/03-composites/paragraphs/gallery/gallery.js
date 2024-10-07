
(function($, Drupal, once) {

	//Swipebox script for lightbox images.
	Drupal.behaviors.gallery = {
		attach: function (context, settings) {
			//Featherlight
			$(once('lightboxes', '.gallery-style.tiles', context)).each(function(){
				$(document).ready(function(){
					$('.featherlight-gal', this).featherlightGallery({
						previousIcon: 'Prev',
						nextIcon: 'Next',
						galleryFadeIn: 300,
						openSpeed: 300,
						closeIcon: 'Close'
					});
				});
			});
			//Slider
			$(once('slider', '.gallery-style.slider', context)).each(function(){
				$(document).ready(function(){
					$('.field-gallery-items', this).slick({
						adaptiveHeight: false,
						autoplay: true,
						autoplaySpeed: 5000,
						centerMode: false,
						slidesToShow: 1,
						slidesToScroll: 1,
						responsive: [
							{
								breakpoint: 984,
								settings: {
									centerMode: true,
									centerPadding: '20px',
								}
							}
						]
					});
				});
			});
		}
	}

	/* GALLERY ANIMATIONS
	------------------ */
	Drupal.behaviors.galleryAnimate = {
		attach: function (context, settings) {
			$(once('galleryAnimations', '.paragraph--type--gallery', context)).each(function(){
				// Function to handle the intersection observer callback
				function handleIntersection(entries, observer) {
					entries.forEach(entry => {
						if ($("html").hasClass("animations-paused")) {
              entry.target.classList.add('gallery-visible');
              observer.unobserve(entry.target);
            }
            else if (entry.isIntersecting) {
							//add class when target is visible
							entry.target.classList.add('gallery-visible');
							observer.unobserve(entry.target); // Stop observing once the class is added
						}
					});
				}
				// Create an intersection observer
				const observer = new IntersectionObserver(handleIntersection, { threshold: 0.25 });
				// Select the target element
				// Start observing the target element
				observer.observe(this);
			});
		}
	}

})(jQuery, Drupal, once);
