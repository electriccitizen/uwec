(function($, Drupal, once) {

/* VIDEO ANIMATIONS
------------------ */
Drupal.behaviors.videoAnimate = {
  attach: function (context, settings) {
  	$(once('videoAnimations', '.paragraph--type--video', context)).each(function(){
      // Function to handle the intersection observer callback
      function handleIntersection(entries, observer) {
        entries.forEach(entry => {
          if ($("html").hasClass("animations-paused")) {
            entry.target.classList.add('video-visible');
            observer.unobserve(entry.target);
          }
          else if (entry.isIntersecting) {
            //add class when target is visible
            entry.target.classList.add('video-visible');
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

// Remote video audio description toggle
Drupal.behaviors.remoteVideoAudioDescriptionToggle = {
  attach: function(context, settings){
    const localStorageKey = 'audioDescriptions'

    // updates the iframe src to use the audio description version if desired
    // the given "ele" can be any element within the video paragraph
    // updates all video embeds on the page
    function updateVideoEmbeds(){
      let useAudioDescriptions = localStorage.getItem(localStorageKey) !== null
      document.querySelectorAll('.paragraph--type--video').forEach(container=>{
        let audioDescriptionEle = container.querySelector('.remote-video-audio-description')
        if(audioDescriptionEle == null) return

        // determine which video URL to display
        let video_url = '';
        if(useAudioDescriptions){
          video_url = audioDescriptionEle.dataset.secondarySrc
        }else{
          video_url = audioDescriptionEle.dataset.primarySrc
        }
        let video_id = getVideoID(video_url)

        let iframe = container.querySelector('iframe')
        iframe.src = 'https://www.youtube.com/embed/'+video_id

        let checkbox = container.querySelector('input[type=checkbox]')
        checkbox.checked = useAudioDescriptions
      })
    }

    // given a youtube video URL, this returns just the video id.
    // returns false if there is no valid video id.
    function getVideoID(url){
      if(typeof(url)=='undefined') return false;
      let regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
      let match = url.match(regExp);
      return (match&&match[7].length==11)? match[7] : false;
    }

    once('remoteVideoAudioDescriptionToggle', '.remote-video-audio-description', context).forEach(ele=>{
      let checkbox = ele.querySelector('input[type=checkbox]')
      // update audio description toggle in local storage when the check box changes
      checkbox.addEventListener('change', (event)=>{
        if(event.target.checked){
          localStorage.setItem(localStorageKey, '1')
        }else{
          localStorage.removeItem(localStorageKey)
        }
        updateVideoEmbeds()
      })

      // check the check box if it's already toggled on
      if(localStorage.getItem(localStorageKey) !== null){
        checkbox.checked = true
        updateVideoEmbeds()
      }
    })
  }
}

})(jQuery, Drupal, once);
