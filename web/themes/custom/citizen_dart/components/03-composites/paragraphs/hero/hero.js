(function($, Drupal, once, cookies) {

/* HERO VIDEO CONTROLS
------------------ */
Drupal.behaviors.heroVideo = {

  // Create a Play/Pause button that starts or stops the hero videos across the
  // site, and stores the current setting to a non-expiring cookie.
  attach: function (context, settings) {
    const pause_lang = Drupal.t("Pause video");
    const play_lang = Drupal.t("Play video");

    $(once('heroVideo', '.page-hero-video .video-hero-mask', context)).each(function() {
      const video = $("video", this).get(0);
      const videoWrapper = $(this);
      const videoCookie = cookies.get('heroVideoPlay');
      let defaultState = videoCookie ? videoCookie : "playing";

      if (video) {
        const videoButton = $(`<a href="#" class="video-button" title="${pickButtonLanguage(defaultState)}" aria-label="${pickButtonLanguage(defaultState)}"></a>`);

        // Set the initial state of the video (and the wrapper class) based off
        // of if we had a cookie telling us to pause.
        videoWrapper.addClass(defaultState);
        if (defaultState === "paused") {
          video.pause();
        }

        videoButton.on("click", (e) => {
          e.preventDefault();
          e.stopPropagation();
          if (videoWrapper.hasClass("playing")) {
            video.pause();
            defaultState = "paused";
          }
          else if (videoWrapper.hasClass("paused")) {
            video.play();
            defaultState = "playing";
          }
          cookies.set("heroVideoPlay", defaultState);
          videoWrapper.toggleClass("paused");
          videoWrapper.toggleClass("playing");

          // Don't forget to update the ARIA language.
          videoButton.attr("aria-label", pickButtonLanguage(defaultState));
          videoButton.attr("title", pickButtonLanguage(defaultState));
        });
        $(this).append(videoButton);
      }
    });

    // Return the pause/play language based off of the current state.
    // Expects the state to be "playing" if the video is currently playing, or
    // any other state if it's paused.
    function pickButtonLanguage(state) {
      return state === "playing" ? pause_lang : play_lang;
    }
  }
}

})(jQuery, Drupal, once, window.Cookies);
