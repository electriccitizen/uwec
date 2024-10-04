(function($, Drupal, once) {

/* CTA ANIMATIONS
------------------ */
Drupal.behaviors.ctaAnimate = {
  attach: function (context, settings) {
  	$(once('ctaAnimations', '.paragraph--type--ctas', context)).each(function(){
      // Function to handle the intersection observer callback
      function handleIntersection(entries, observer) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
              //add class when target is visible
              entry.target.classList.add('cta-visible');
              observer.unobserve(entry.target); // Stop observing once the class is added
            }
        });
      }
      // Create an intersection observer
      const observer = new IntersectionObserver(handleIntersection, { threshold: 0.15 });
      // Select the target element
      // Start observing the target element
      observer.observe(this);
    });
  }
}

})(jQuery, Drupal, once);
