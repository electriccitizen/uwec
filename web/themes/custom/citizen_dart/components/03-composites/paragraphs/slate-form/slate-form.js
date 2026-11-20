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

    once('slateAnimations', '.slate-right', context).forEach(async (slateRight)=>{
      const slateForm = await waitForElement(slateRight, 'form');

      // remove the "Delivery Mode" field, if we should
      if(slateRight.dataset.showDeliveryMode != '1'){
        const deliveryModeField = slateForm.querySelector('.form_question_934bbcc8-0650-4aca-9b1f-9a84b8b011b6');
        if(deliveryModeField){
          deliveryModeField.remove();
        }
      }

      //find option selected so can change the color of slate select
      slateRight.querySelectorAll('select').forEach((select)=>{
        select.addEventListener('change', ()=>{
          if(select.value != ''){
            select.classList.add('selected');
          }else{
            select.classList.remove('selected');
          }
        });
      });
    });
  }
}

// wait for an element to exist and return it
function waitForElement(context, selector, timeout = 10000) {
  return new Promise((resolve, reject) => {
    // 1. Check if the element already exists
    const existingElement = context.querySelector(selector);
    if(existingElement){
      return resolve(existingElement);
    }

    // 2. Set up a timeout so it doesn't wait forever
    const timer = setTimeout(() => {
      observer.disconnect();
      reject(new Error(`Element "${selector}" not found within ${timeout}ms`));
    }, timeout);

    // 3. Watch for DOM changes
    const observer = new MutationObserver(() => {
      const element = document.querySelector(selector);
      if(element){
        clearTimeout(timer);
        observer.disconnect();
        resolve(element);
      }
    });

    observer.observe(context, {
      childList: true,
      subtree: true,
    });
  });
}

})(jQuery, Drupal, once);
