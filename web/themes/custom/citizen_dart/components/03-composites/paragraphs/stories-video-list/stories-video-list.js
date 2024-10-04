(function($, Drupal, once) {

/* STORY VIDEO ANIMATIONS
------------------ */
Drupal.behaviors.storyVideoAnimate = {
  attach: function (context, settings) {
  	$(once('storyVideoAnimations', '.paragraph--type--stories-video-list .node-teaser', context)).each(function(){
      // Function to handle the intersection observer callback
      function handleIntersection(entries, observer) {
        entries.forEach(entry => {
          if ($("body").hasClass("animations-paused")) {
            entry.target.classList.add('story-video-visible');
            observer.unobserve(entry.target);
          }
          else if (entry.isIntersecting) {
            //add class when target is visible
            entry.target.classList.add('story-video-visible');
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
