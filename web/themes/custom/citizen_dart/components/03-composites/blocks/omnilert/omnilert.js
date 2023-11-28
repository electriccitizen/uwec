// call this function from your browser's console to test it
function omnilertTest(){
	let container = document.querySelector('.omnilert');
	container.innerHTML = '<table class="SmartBoard_Table" id="SmartBoard_46e61838b833b89f7600"><tr class="SmartBoard_Row"><td class="SmartBoard_Subject">Emergency test</td><td class="SmartBoard_DateTime">2015-12-10 10:18:28</td></tr><tr class="SmartBoard_Row"><td class="SmartBoard_Message" colspan="2">This ends the emergency notification system test. In an actual emergency, you would receive continued updates.</t</tr></table><table class="SmartBoard_Table" id="SmartBoard_2c35efe7b11477b74bf3"><tr class="SmartBoard_Row"><td class="SmartBoard_Subject"></td><td class="SmartBoard_DateTime">2015-12-10 10:00:22</td></tr><tr class="SmartBoard_Row"><td class="SmartBoard_Message" colspan="2">This is a TEST of the U W Eau Claire Emergency Communications System. This is only a TEST.</td></tr></table>';
};

document.addEventListener('DOMContentLoaded', function(){
	let container = document.querySelector('.omnilert');
	if(container){
		fetch('/ajax/omnilert')
		.then(response => response.text())
		.then(html => {
			let alertString = '';

			// try to parse out the 'document.write()' wrapper in the response
			let rx = /document\.write\('(.*)'\)/.exec(html);
			if(rx != null) alertString = rx[1];

			container.innerHTML = alertString;
		});
	}
});
