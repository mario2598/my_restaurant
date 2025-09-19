window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


function initialice() {


}

$(document).ready(function () {
  $("#btn_buscar_ingreso").on("keyup", function () {
      var value = $(this).val().toLowerCase();
      $("#tbody_generico tr").filter(function () {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
      });
  });
});


function clickIngreso(id) {
  $('#idIngreso').val(id);
  $('#formIngreso').submit();
}
