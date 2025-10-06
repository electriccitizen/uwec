// tweak the google search form's html in the header
// this uses the window "load" event so it fires after the google search form js
// because the google js creates these elements
window.addEventListener('load', ()=>{
	// add new label
	let form = document.querySelector('form.gsc-search-box');
	if(form){
		let label = document.createElement("label");
		label.htmlFor = 'gsc-i-id1';
		label.innerHTML = 'Search the UW-Eau Claire Website';
		form.prepend(label);
	}

	// set placeholder
	let input = document.querySelector('input.gsc-input');
	if(input){
		input.setAttribute('placeholder', 'What are you searching for?');
	}

	// add the word "Search" to the search button
	let searchButton = document.querySelector('button.gsc-search-button');
	if(searchButton){
		searchButton.append('Search');
	}
});

(function(Drupal) {
	function setHeaderSearchVisible(visible){
		let container = document.querySelector('.google-search-container');
		let tSearch = document.querySelector('.t-search');

		if(visible){
			container.classList.add('expanded');
			container.setAttribute('aria-hidden', 'false');
			tSearch.classList.add('close-search');
			tSearch.querySelector('span').innerHTML = 'Close search';

			// auto focus on the search input
			setTimeout(function(){
				document.querySelector('input.gsc-input').focus();
			}, 100);
		}else{
			container.classList.remove('expanded');
			container.setAttribute('aria-hidden', 'true');
			tSearch.classList.remove('close-search');
			tSearch.querySelector('span').innerHTML = 'Search';
		}
	}

	// desktop search toggle
	Drupal.behaviors.searchToggle = {
		attach: function (context, settings) {
			once('tSearch', '.block-citizen-google-search-block', context).forEach(function(block){
				block.querySelectorAll('.search-form-container').forEach((ele)=>{
					ele.addEventListener('focusout', (event)=>{
						if(!ele.contains(event.relatedTarget)){
							setHeaderSearchVisible(false);
						}
					});
				});

				// close the header search if they click out
				let searchContainer = block.querySelector('.google-search-container');
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

				// toggle nav on click on search icon (or close search icon)
				document.querySelectorAll('.t-search').forEach((tSearch)=>{
					tSearch.addEventListener('click', (e)=>{
						e.preventDefault();
						if(tSearch.classList.contains('close-search')){
							setHeaderSearchVisible(false);
						} else {
							setHeaderSearchVisible(true);
						}
					});
				});
			});
		}
	}

	// Scroll to the results when a user searches
	Drupal.behaviors.searchResults = {
		attach: function (context, settings) {
			once('isSearching', '#search-results-wrapper', context).forEach(function(searchResultsWrapper){
				//check if searched and scroll to if so
				let urlCurrent = window.location.href;
				if(urlCurrent.indexOf("?q") > -1){
					let scrollTarget = searchResultsWrapper.getBoundingClientRect().top;
					scrollTarget += window.scrollY; // ignore initial scroll
					scrollTarget -= 100; // a bit of margin
					window.scrollTo({
						top: scrollTarget,
						behavior: 'smooth',
					});
				}
			});
		}
	};

})(Drupal);
