(function($, Drupal, once) {

Drupal.behaviors.pageStructuredSectionAnimate = {
  attach: function (context, settings) {
    $(once('academicProgramRelated', '.block-structured-section .field-formatted-headline, .block-structured-section .field-cta-image', context)).each(function(){
      // Function to handle the intersection observer callback
      function handleIntersection(entries, observer) {
        entries.forEach(entry => {
          if ($("html").hasClass("animations-paused")) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
          else if (entry.isIntersecting) {
            //add class when target is visible
            entry.target.classList.add('visible');
            observer.unobserve(entry.target); // Stop observing once the class is added.
          }
        });
      }
      // Create an intersection observer
      const observer = new IntersectionObserver(handleIntersection, { threshold: 0.25 });
      // Select the target element
      // Start observing the target element
      observer.observe(this);
    });
  }
}

})(jQuery, Drupal, once);
