window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


function initialice() {


}

function fileValidation(){

  var fileInput = document.getElementById('foto_comprobante');
  var filePath = fileInput.value;
  var allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;
  if(!allowedExtensions.exec(filePath)){
    iziToast.error({
      title: 'Formato invalido',
      message: 'El formato no corresponde al formato correcto',
      position: 'topRight'
    });
  }else{
    //Image preview
    if (fileInput.files && fileInput.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
          var file = document.getElementById('foto_comprobante');
          file = file.files[0];
          document.getElementById("foto_comprobante_b64").setAttribute("value", e.target.result);
          $('#foto_comprobante_b64').val(e.target.result);
        
      };
      reader.readAsDataURL(fileInput.files[0]);
    }
  }
}