// Get the "Domain Access" radio inputs.
var da_options = document.querySelectorAll('#edit-field-domain-access input');

// Get the "Domain Source" selector.
var ds_field = document.querySelector('select#edit-field-domain-source');

// Define the handler for the 'change' event.
function set_domain_source(event){
  // Find the option that matches the "Domain Access" value.
  for(var o = 1; o < ds_field.options.length; o++){
    if(ds_field.options[o].value === event.target.value){
      // Set the option's "selected" attribute.
      ds_field.options[o].setAttribute('selected', 'selected');
      // Set the field's "selectedIndex" value to the index.
      ds_field.options.selectedIndex = o;
      // Get the rendered select list.
      var rendered_select_list = ds_field.nextElementSibling;
      // Get the rendered selected option.
      var rendered_selected_option = rendered_select_list.querySelector('span[class$=__rendered]');
      // Set the innerHTML value of the rendered select option.
      rendered_selected_option.innerHTML = ds_field.options[o].innerHTML;
    }else{
      // Remove the option's "selected" attribute.
      ds_field.options[o].removeAttribute('selected');
    }
  }
}

if(da_options.length > 0){
  // Add the change event listener to each input.
  for(var i = 0; i < da_options.length; i++){
    da_options[i].addEventListener('change', set_domain_source);
  }
}
