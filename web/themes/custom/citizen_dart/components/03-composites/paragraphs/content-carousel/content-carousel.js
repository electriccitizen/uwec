
(function($, Drupal, once) {

	//Swipebox script for lightbox images.
	Drupal.behaviors.contentCarousel = {
		attach: function (context, settings) {
			//Slider
			$(once('contentCarousel', '.paragraph--type--content-carousel', context)).each(function(){
        if(!$('.views-row',this).length){
          $(this).closest('.block-field-content-carousel').remove();
        }
				$(document).ready(function(){
					$('.paragraph--type--content-carousel .widget-wrapper', this).slick({
						adaptiveHeight: false,
						autoplay: false,
            centerMode: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            dots: true,
            responsive: [
              {
                breakpoint: 984,
                settings: {
                  adaptiveHeight: false,
                  centerMode: true,
                  centerPadding: '20px',
                  dots: false,
                }
              }
            ]
					});
				});
			});
		}
	}

})(jQuery, Drupal, once);
