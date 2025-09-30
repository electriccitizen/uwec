Drupal.behaviors.main_navigation = {
	attach: function(context, settings){
		once('main-navigation', '#block-main-menu', context).forEach(function(nav){
			// click arrow to toggle subnav
			nav.querySelectorAll('.menu-item-expand').forEach(function(expander){
				expander.addEventListener('click', function(e){
					e.preventDefault();
					let clickedLi = this.closest('li');
					// only allow one open at a time
					nav.querySelectorAll('.menu-main-navigation>li').forEach(function(li){
						if(li != clickedLi){
							li.classList.remove('open');
						}
					});
					clickedLi.classList.toggle('open');
				});
			});

			// hover top level link to open subnav
			nav.querySelectorAll(':scope > ul > li').forEach(function(topLi){
				topLi.addEventListener('mouseover', function(){
					// do nothing for fingers
					if(window.matchMedia('(pointer: coarse)').matches) return;

					// close any other one that may be open
					this.closest('ul').querySelectorAll('li').forEach(function(li){
						li.classList.remove('open');
					});

					this.classList.add('open');
				});
				topLi.addEventListener('mouseout', function(){
					// do nothing for fingers
					if(window.matchMedia('(pointer: coarse)').matches) return;

					this.classList.remove('open');
				});
			});

			// click the toggle
			nav.querySelector('.toggle-button').addEventListener('click', function(e){
				e.preventDefault();
				let siteHeader = nav.closest('.site-header');
				siteHeader.classList.toggle('open');
			});
		});
	}
};
