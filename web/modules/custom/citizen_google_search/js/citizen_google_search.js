(function($, Drupal) {
	Drupal.behaviors.googleCustomSearch = {
		attach: function (context, settings) {
			$(once('hasSearch', '.google-search', context)).each(function(){
				document.onreadystatechange = function () {
					if (document.readyState === 'complete') {
						let form = document.querySelector('form.gsc-search-box');
						let input = document.querySelector('input.gsc-input');

						let label = document.createElement("label");
						label.htmlFor = 'gsc-i-id1';
						label.innerHTML = 'Search the UW-Eau Claire Website';
						
						form.prepend(label);
						input.setAttribute('placeholder', 'What are you searching for?');
						input.style.backgroundImage = 'none';
						document.querySelector('button.gsc-search-button svg').remove();
						document.querySelector('button.gsc-search-button').append('Search');
					}
				};
			});
		}
	};

	function setHeaderSearchVisible(visible){
		let $container = $('.google-search-container');
		let $tSearch = $('.t-search');

		if(visible){
			$container.addClass('expanded');
			$container.attr('aria-hidden', 'false');
			$tSearch.addClass('close-search');
			$tSearch.find('span').text('Close search');

			// auto focus on the search input
			setTimeout(function(){
				$('.gsc-input').focus();
			}, 100);
		}else{
			$container.removeClass('expanded');
			$container.attr('aria-hidden', 'true');
			$tSearch.removeClass('close-search');
			$tSearch.find('span').text('Search');
		}
	}

	//search toggle
	Drupal.behaviors.searchToggle = {
		attach: function (context, settings) {
			$(once('tSearch', '.block-citizen-google-search-block', context)).each(function(){
				$('.search-form-container').on('focusout', function(e){
					// if focus goes outside the container, hide the container
					// this is so the newly focused element is not obscured after tabbing out of the container.
					if(!this.contains(e.relatedTarget)){
						setHeaderSearchVisible(false);
					}
				});

				// close the header search if they click out
				let searchContainer = document.querySelector('.google-search-container');
				document.addEventListener('click', function(e){
					// ignore this if it's already closed
					if(!searchContainer.classList.contains('expanded')) return;

					// ignore this if we are clicking the open/close button
					if(e.target.classList.contains('t-search')) return;

					// if this click is outside the search container, close
					if(!searchContainer.contains(e.target)){
						setHeaderSearchVisible(false);
					}
				});

				// find the top position of the site header and position the fixed search box to that if we're on desktop 
				if($('window').outerWidth() > 1199){
					var headerPosition = $('header.site-header').offset().top();
					$('#header-search').css('top',headerPosition);
				}

				// toggle nav on click on search icon (or close search icon)
				$('.t-search', this).click(function(e){
					e.preventDefault();
					if($(this).hasClass('close-search')) {
						setHeaderSearchVisible(false);
					} else {
						setHeaderSearchVisible(true);
					}
				});
			});
		}
	}

	// Scroll to the results when a user searches
	Drupal.behaviors.searchResults = {
		attach: function (context, settings) {
			$(once('isSearching', '#search-results-wrapper', context)).each(function(){
				//check if searched and scroll to if so
				var urlCurrent = window.location.href;
				if((urlCurrent.indexOf("?q") > -1) || (urlCurrent.indexOf("#gsc.tab=0&gsc.q=") > -1)){
					$(document).ready(function(){
						setTimeout(function() {
							$('html,body').animate({
								scrollTop: $('#search-results-wrapper').offset().top - 100
							});
						}, 300);
					});
				}
			});
		}
	};

})(jQuery, Drupal);
