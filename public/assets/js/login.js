window.addEventListener("load", initialice, false);
"use strict";
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


function initialice() {
 
}

function sendForm(id){
    $('#id').val(id);
    $('#form_usuario').submit();
  
  }