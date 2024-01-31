
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
						openSpeed: 300
					});
				});
			});
			//Slider
			$(once('slider', '.gallery-style.slider', context)).each(function(){
				$(document).ready(function(){
					$('.field-gallery-items', this).slick({
						adaptiveHeight: false,
						autoplay: true,
						autoplaySpeed: 5000
					});
				});
			});
			// Masonry
			$(once('masonry', '.gallery-style.masonry', context)).each(function(){
				$(this).masonry({
					// options
					itemSelector: '.masonry-item',
					columnWidth: 300,
					gutter: 16
				});
			});
		}
	}

})(jQuery, Drupal, once);
