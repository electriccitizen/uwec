document.addEventListener('DOMContentLoaded', function(){
	let containers = document.querySelectorAll('.block-announcements');
	if(containers.length){
		// get path and sanitize
		let path = window.location.pathname;
		path += path.startsWith('/') ? '' : '/';
		path += path.endsWith('/') ? '' : '/';

		// send request
		fetch('/ajax/announcements?path='+encodeURIComponent(path))
		.then(response => response.text())
		.then(html => {
			// put response in containers
			containers.forEach(function(container){
				container.innerHTML = html;
			});
		});
	}
});