(function(Drupal, once) {

Drupal.behaviors.externalArticlesAnimate = {
	attach: function (context, settings) {
		once('externalArticlesAnimations', '.paragraph--type--external-articles .field-external-articles-items>div', context).forEach(element => {
			function handleIntersection(entries, observer) {
				entries.forEach(entry => {
          if ($("html").hasClass("animations-paused")) {
            entry.target.classList.add('external-articles-visible');
            observer.unobserve(entry.target);
          }
					else if (entry.isIntersecting) {
						entry.target.classList.add('external-articles-visible');
						observer.unobserve(entry.target);
					}
				});
			}
			const observer = new IntersectionObserver(handleIntersection, { threshold: 0.15 });
			observer.observe(element);
		});
	}
}

})(Drupal, once);
