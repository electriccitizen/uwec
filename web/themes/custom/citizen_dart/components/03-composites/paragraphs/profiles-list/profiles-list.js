
(function($, Drupal, once) {

	Drupal.behaviors.profileSliders = {
		attach: function (context, settings) {
			$(once('fauxfileSlider', '.paragraph--type--fauxfile', context)).each(function(){
				$(document).ready(function(){
					$('.field-fauxfile-items').slick({
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
			});//end fauxfile context
      $(once('profilesAutoListSlider', '.profile-list-auto', context)).each(function(){
				$(document).ready(function(){
					$('.bios.profiles-list .view-content').slick({
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
			});//end profile list auto context
      $(once('profilesManualListSlider', '.profile-list-manual', context)).each(function(){
				$(document).ready(function(){
					$('.profile-list-manual .field-profiles').slick({
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
			});//end profiles list manual context
      $(once('departmentFacultyListSlider', '.bios.department-faculty', context)).each(function(){
				$(document).ready(function(){
					$('.bios.department-faculty .view-content').slick({
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
			});//end department faculty slider
      $(once('programFacultyListSlider', '.bios.program-faculty', context)).each(function(){
				$(document).ready(function(){
					$('.bios.program-faculty .view-content').slick({
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
			});//end program faculty slider
		}
	}

})(jQuery, Drupal, once);
