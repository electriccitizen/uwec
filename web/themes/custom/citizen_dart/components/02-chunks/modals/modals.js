(function($, Drupal, once) {

	//Pop-up content functionality.
	Drupal.behaviors.modal = {
		attach: function (context, settings) {
			$(once('modalContent', '.modal', context)).each(function(){
				//set the variable needed to keep the modal controls specific
				var pid = $('.modal-lead', this).attr('data-attribute-id');
				$('.modal-wrapper', this).appendTo('.overflow-guard');
	
				//load the setup
				window.addEventListener('load', setup);
				const get = document.getElementById.bind(document);
		  		const query = document.querySelector.bind(document);
	
				function setup() {
					//set the controls for this specific modal
					let modalRoot = get('modal-outer-' + pid);
					let button = get('modal-trigger-' + pid);
					let modal = get('modal-inner-' + pid);
					let close = get('modal-close-' + pid);
					//set the click functions
					modalRoot.addEventListener('click', rootClick);
					button.addEventListener('click', openModal);
					modal.addEventListener('click', modalClick);
					close.addEventListener('click', modalClose);
					//close when the modal window is clicked out side of the content
					function rootClick() {
						modalRoot.classList.add('close-modal');
						setTimeout(function(){ modalRoot.classList.remove('close-modal','active-modal'); }, 1200);
					}
					//close when the close button is clicked
					function modalClose() {
						modalRoot.classList.add('close-modal');
						setTimeout(function(){ modalRoot.classList.remove('close-modal','active-modal'); }, 1200);

						//if there is a external video in an iframe or an HTML video, stop it when the modal closes
						if(modal.getElementsByTagName('iframe').length != 0) {
							modal.getElementsByTagName('iframe')[0].src = modal.getElementsByTagName('iframe')[0].src;
						} else if(modal.getElementsByTagName('video').length != 0) {
							modal.getElementsByTagName('video')[0].pause();
							modal.getElementsByTagName('video')[0].currentTime = 0;
						}
					}
					//open the modal
					function openModal() {
						modalRoot.classList.add('active-modal');
						if(modal.getElementsByTagName('form').length != 0) {
							modal.getElementsByTagName('form')[0].getElementsByTagName('input')[0].focus();
						} else {
							modal.focus();
						}
					}
					//prevent close when the modal inner content is clicked
					function modalClick(e) {
					//e.preventDefault();
					e.stopPropagation();
					e.stopImmediatePropagation();
					return false;
					}
					$(document).keydown(function(event) { 
						if (event.keyCode == 27) { 
						  modalClose();
						}
					  });
		  		}
			});
		}
	}
	
	})(jQuery, Drupal, once);