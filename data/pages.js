/**
 * Javascript for influencing the layout and data handling
 *
 *
 */

function togglePasswordVisibility(event) {
	var buttn = document.getElementById(event.target.id);
	var inputID = buttn.getAttribute('data-for');
	var input = document.getElementById(inputID);
	if (input.getAttribute('type') == 'password') {
		input.setAttribute('type', 'text');
		buttn.textContent = 'hide password';
	} else {
		input.setAttribute('type', 'password');
		buttn.textContent = 'show password';
	}
}


function whenReady() {
	//console.info('DOM is ready');
	var formElem = document.querySelector('form');
	formElem.addEventListener('click', togglePasswordVisibility);
}

document.addEventListener('DOMContentLoaded', whenReady);

