
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
				let grids = [...document.querySelectorAll('.grid--masonry .gallery-wrapper')];

				if(grids.length) {
					grids = grids.map(grid => ({
						_el: grid, 
						gap: parseFloat(getComputedStyle(grid).gridRowGap), 
						items: [...grid.childNodes].filter(c => c.nodeType === 1), 
						ncol: 0
					}));

					function layout() {
						grids.forEach(grid => {
							/* get the post relayout number of columns */
							let ncol = getComputedStyle(grid._el).gridTemplateColumns.split(' ').length;

							/* if the number of columns has changed */
							if(grid.ncol !== ncol) {
								/* update number of columns */
								grid.ncol = ncol;

								/* revert to initial positioning, no margin */
								grid.items.forEach(c => c.style.removeProperty('margin-top'));

								/* if we have more than one column */
								if(grid.ncol > 1) {
									grid.items.slice(ncol).forEach((c, i) => {
										let prev_fin = grid.items[i].getBoundingClientRect().bottom /* bottom edge of item above */, 
												curr_ini = c.getBoundingClientRect().top /* top edge of current item */;
										
										c.style.marginTop = `${prev_fin + grid.gap - curr_ini}px`
									})
								}
							}
						})
					}

					addEventListener('load', e => {
						layout(); /* initial load */
						addEventListener('resize', layout, false) /* on resize */
					}, false);
				}
			});
		}
	}

})(jQuery, Drupal, once);
