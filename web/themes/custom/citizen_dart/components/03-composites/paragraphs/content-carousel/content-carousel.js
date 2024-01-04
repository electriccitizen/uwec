
(function($, Drupal, once) {

	//Swipebox script for lightbox images.
	Drupal.behaviors.contentCarousel = {
		attach: function (context, settings) {
			//Slider
			$(once('contentCarousel', '.paragraph--type--content-carousel', context)).each(function(){
				$(document).ready(function(){
					$('.paragraph--type--content-carousel .widget-wrapper', this).slick({
						adaptiveHeight: false,
						autoplay: false,
						autoplaySpeed: 5000
					});
				});
			});
		}
	}

})(jQuery, Drupal, once);
