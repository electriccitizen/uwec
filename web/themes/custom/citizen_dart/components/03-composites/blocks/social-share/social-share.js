(function($, Drupal, once) {

Drupal.behaviors.accordion = {
	attach: function (context, settings) {
		once('social-share-link', '.social-share .link-share', context).forEach(function(link){
			link.addEventListener('click', function(){
				// make sure window.navigator.clipboard exists.
				// it only exists on secure connections
				if(!window.navigator.clipboard){
					showCopyToast(link, "Not copied");
					return;
				}

				let url = window.location.href;
				try {
					window.navigator.clipboard.writeText(url).then(function(){
						showCopyToast(link, "Copied");
					});
				} catch (error) {
					showCopyToast(link, "Not copied");
					console.error(error.message);
				}
			});
		});

		// displays a tiny toast message right above the given link element
		// it's the tiny popup that says "Copied" when you click the link
		function showCopyToast(link, message){
			let e = document.createElement('div');
			e.classList.add('toast');
			e.innerText = message;

			// show it, but only briefly
			link.appendChild(e);
			setTimeout(function(){
				e.classList.add('fade');
			}, 1000);
			setTimeout(function(){
				e.remove();
			}, 1500);
		}
	}
}

})(jQuery, Drupal, once);
