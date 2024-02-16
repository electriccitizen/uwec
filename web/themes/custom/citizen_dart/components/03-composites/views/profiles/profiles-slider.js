
(function($, Drupal, once) {

	Drupal.behaviors.profileSliders = {
		attach: function (context, settings) {
      $(once('profileSlider', '.bios:not(.profile-list)', context)).each(function(){
				$(document).ready(function(){
					$('.bios:not(.profile-list) .view-content').slick({
						adaptiveHeight: false,
						autoplay: true,
						autoplaySpeed: 5000,
            slidesToShow: 3,
            slidesToScroll: 3,
            centerMode: false,
            responsive: [
              {
                breakpoint: 984,
                settings: {
                  slidesToShow: 3,
                  slidesToScroll: 3,
                  centerMode: true,
                  centerPadding: '40px',
                }
              },
              {
                breakpoint: 800,
                settings: {
                  slidesToShow: 2,
                  slidesToScroll: 2,
                  centerMode: true,
                  centerPadding: '40px',
                }
              },
              {
                breakpoint: 480,
                settings: {
                  slidesToShow: 1,
                  slidesToScroll: 1,
                  centerMode: true,
                  centerPadding: '40px',
                }
              }
            ]
					});
				});
			});//end profile slider
		}
	}

})(jQuery, Drupal, once);
