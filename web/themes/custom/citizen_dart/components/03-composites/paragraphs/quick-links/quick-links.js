(function(Drupal, once) {

Drupal.behaviors.quickLinksAnimate = {
  attach: function (context, settings) {
  	once('quickLinksAnimations', '.paragraph--type--quick-links', context).forEach(function(element){
      // Function to handle the intersection observer callback
      function handleIntersection(entries, observer) {
        entries.forEach(entry => {
          if ($("body").hasClass("animations-paused")) {
            entry.target.classList.add('quick-links-visible');
            observer.unobserve(entry.target);
          }
          else if (entry.isIntersecting) {
            //add class when target is visible
            entry.target.classList.add('quick-links-visible');
            observer.unobserve(entry.target); // Stop observing once the class is added
          }
        });
      }
      const observer = new IntersectionObserver(handleIntersection, { threshold: 0.25 });
      observer.observe(element);
    });
  }
}

})(Drupal, once);
