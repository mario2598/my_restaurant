window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


function initialice() {


}

function clickIngreso(id) {
  $('#idIngreso').val(id);
  $('#formIngreso').submit();
}
