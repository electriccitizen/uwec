(function($, Drupal, once) {

/* WAYFINDING ANIMATIONS
------------------ */
Drupal.behaviors.wayfindingAnimate = {
  attach: function (context, settings) {
  	$(once('wayfindingAnimations', '.paragraph--type--wayfinding .wayfinding', context)).each(function(){
      // Function to handle the intersection observer callback
      function handleIntersection(entries, observer) {
        entries.forEach(entry => {
          if ($("body").hasClass("animations-paused")) {
            entry.target.classList.add('wayfinding-visible');
            observer.unobserve(entry.target);
          }
          else if (entry.isIntersecting) {
            //add class when target is visible
            entry.target.classList.add('wayfinding-visible');
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
