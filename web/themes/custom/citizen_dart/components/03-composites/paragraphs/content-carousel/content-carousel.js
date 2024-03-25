
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
            centerMode: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            responsive: [
              {
                breakpoint: 984,
                settings: {
                  adaptiveHeight: false,
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

})(jQuery, Drupal, once);
