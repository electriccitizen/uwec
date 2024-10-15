(function($, Drupal, once) {

/* SLATE ANIMATIONS
------------------ */
Drupal.behaviors.slateAnimate = {
  attach: function (context, settings) {
    $(once('slateAnimations', '.slate-left', context)).each(function(){
      // Function to handle the intersection observer callback
      function handleIntersection(entries, observer) {
        entries.forEach(entry => {
          if ($("html").hasClass("animations-paused")) {
            entry.target.classList.add('slate-visible');
            observer.unobserve(entry.target);
          }
          else if (entry.isIntersecting) {
            //add class when target is visible
            entry.target.classList.add('slate-visible');
            observer.unobserve(entry.target); // Stop observing once the class is added
          }
        });
      }
      // Create an intersection observer
      const observer = new IntersectionObserver(handleIntersection, { threshold: 0.5 });
      // Select the target element
      // Start observing the target element
      observer.observe(this);
    });

    $(once('slateAnimations', '.slate-right', context)).each(function(){
      //find option selected so can change the color of slate select and fix broken aria attr
      $(document).ready(function(){
        setTimeout(function() {
          $('.form_question input[type="tel"]').removeAttr('aria-invalid');
          $('.slate-right select').change(function () {
            const selected = $(this).find('option:selected').val();
            if(selected != ''){
              $(this).addClass('selected');
            }else{
              $(this).removeClass('selected');
            }
          });

          // remove the "Delivery Mode" field, if we should
          let slateRight = document.querySelector('.slate-right');
          if(slateRight.dataset.showDeliveryMode != '1'){
            let deliveryModeField = document.querySelector('.form_question_934bbcc8-0650-4aca-9b1f-9a84b8b011b6');
            if(deliveryModeField){
              deliveryModeField.remove();
            }
          }
        }, 500);
      });
    });
  }
}

})(jQuery, Drupal, once);
