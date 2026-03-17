(function($, Drupal, once) {
	Drupal.behaviors.contentCarousel = {
		attach: function (context, settings) {
			$(once('contentCarousel', '.paragraph--type--content-carousel', context)).each(function(){
				// remove the whole paragraph if there are zero widgets to show
				if(this.querySelector('.widget-wrapper').childElementCount <= 0){
					this.closest('.block-field-content-carousel').remove();
					return;
				}

				// do slick slider
				$('.widget-wrapper', this).slick({
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
		}
	}
})(jQuery, Drupal, once);
