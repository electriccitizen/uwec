(function($, Drupal) {
  Drupal.behaviors.googleCustomSearch = {
    attach: function (context, settings) {
    	$(once('hasSearch', '.google-search', context)).each(function(){
	      document.onreadystatechange = function () {
	        if (document.readyState === 'complete') {

	          // The full host in the URL.
	          var host = this.location.host;
	          // The pattern to check for.
	          var site_pattern = /(\w+)\./;
	          // Isolate the domain/subdomain.
	          var site = host.match(site_pattern);
	          // The name of the site determined below.
	          var site_name;

	          // Determine which site is being viewed.
	          switch (site[1]) {
	            case 'example':
	              site_name = 'Fancy Example';
	              break;
	            default:
	              site_name = 'UW-Eau Claire';
	              break;
	          }
	         
	          var form = document.querySelector('form.gsc-search-box');
	          var input = document.querySelector('input.gsc-input');
	          var placeholder = 'What are you searching for?';
						var label = document.createElement("Label");

	          label.htmlFor = 'gsc-i-id1';
	          label.innerHTML = 'Search the ' + site_name + ' Website';
	          
	          form.prepend(label);
	          input.setAttribute('placeholder', placeholder);
	          input.style.backgroundImage = 'none';
	          document.querySelector('button.gsc-search-button svg').remove();
	          document.querySelector('button.gsc-search-button').append('Search');
	        }
	      };
	    });
    }
  };

	//search toggle
	Drupal.behaviors.searchToggle = {
		attach: function (context, settings) {
		 	$(once('tSearch', '.block-citizen-google-search-block', context)).each(function(){
		 		//find the top position of the site header and position the fixed search box to that if we're on desktop 
		 		if($('window').outerWidth() > 983){
		 			var headerPosition = $('header.site-header').offset().top();
		 			$('#header-search').css('top',headerPosition);
		 		}
		 		
		 		$('.t-search', this).click(function(e){
		 			e.preventDefault();
		 			$('.google-search-container').slideDown(400).attr('aria-hidden', 'false');
		 		});
		 		$('.close-search', this).click(function(e){
		 			e.preventDefault();
		 			$('.google-search-container').slideUp(400).attr('aria-hidden', 'true');
		 		});	
			});
		}
	}

	/* Scroll to the results when a use searches
	------------------------------------ */
	Drupal.behaviors.searchResults = {
	  attach: function (context, settings) {
	    $(once('isSearching', '#search-results-wrapper', context)).each(function(){
	      //check if searched and scroll to if so
	      var urlCurrent = window.location.href;
	      if(urlCurrent.indexOf("?s") > -1){
					$(document).ready(function(){
						setTimeout(function() {
			   			$('.dialog-off-canvas-main-canvas').animate({
							scrollTop: $('#search-results-wrapper').offset().top - 100
			   			});
		   			}, 300);
			 		});
	      }
	    });
	  }
	};

})(jQuery, Drupal);
