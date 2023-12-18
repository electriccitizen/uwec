function toggleProgramDetails() {
  var body = document.getElementById("programBody");
  var chevron = document.getElementById("chevronIcon");
  body.classList.toggle("hidden");
  chevron.classList.toggle("chevron-flip");
}


function toggleProgramDetails(button) {
  // Find the closest parent container with the class 'academic-program-teaser'
  var container = button.closest('.academic-program-teaser');

  // Within this container, toggle the 'hidden' class for the program body and link
  var body = container.querySelector('.program-body');
  var chevron = button.querySelector('.chevron-down');
  
  body.classList.toggle("hidden");
  chevron.classList.toggle("chevron-flip");
}
