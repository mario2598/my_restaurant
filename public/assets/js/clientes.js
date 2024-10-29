
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var cedulaActual; //donde se almacena el numero de ced actual para evitar probelmas
var usuarioEnUso = false;

function editar(id,nombre,ape1,ape2,tel,correo,ced,index){
 
  $('#index').val(index);
  cedulaActual = ced;
  $('#id').val(id);
  $('#nombre').val(nombre);
  $('#ape1').val(ape1);
  $('#ape2').val(ape2);
  $('#correo').val(correo);
  $('#telefono').val(tel);
  $('#cedula').val(ced);
  $('#cliente_modal').modal('show');

}

function cerrarModal(){
  $('#id').val('');
  $('#nombre').val('');
  $('#ape1').val('');
  $('#ape2').val('');
  $('#correo').val('');
  $('#telefono').val('');
  $('#cedula').val('');
  $('#cliente_modal').modal('hide');
  $('#modal_spinner').fadeOut(50);
  $('#edit_cliente_text').html('Clientes');
}


function guardar(){
  if($('#nombre').val() == ''){
    $('#nombre').focus();
  }else if($('#ape1').val() == ''){
    $('#ape1').focus();
  }else if($('#telefono').val() == ''){
    $('#telefono').focus();
  }else{
    if(usuarioEnUso){
      $("#spam_cedula").css("color", "red");
      $('#spam_cedula').html("<strong>* Cédula en uso</strong>").fadeIn(200); 
      $('#cedula').focus();

    }else{
      $('#modal_spinner').fadeIn(50);
      $('#edit_cliente_text').html('Guardando ...');
      guardarAjax();

     
    }
    
  }
}

function cerrarAlerta(){
  $('#alert-container').fadeOut(200);

}

function guardarAjax(){

      var ced = $('#cedula').val();
      var id = $('#id').val();
      var nombre = $('#nombre').val();
      var ape1 = $('#ape1').val();
      var ape2 = $('#ape2').val();
      var correo = $('#correo').val();
      var tel = $('#telefono').val();
      $.ajax({
          url: '/guardarCliente',
          type: 'post',
          data: {_token: CSRF_TOKEN,idCliente:id,cedula:ced,nom:nombre,a1:ape1,a2:ape2,corr:correo,telefono:tel}
      }).done(function( response ) {
        
      if(response == 1){
        var table = document.getElementById("table-1").rows[parseInt($('#index').val()) ];
        table.cells[0].innerHTML = $('#nombre').val();
        table.cells[1].innerHTML  = $('#ape1').val()+' '+$('#ape2').val();
        table.cells[2].innerHTML  = $('#cedula').val();
        table.cells[3].innerHTML  = $('#telefono').val();
        if($('#cedula').val() == ''){
          table.cells[2].innerHTML  = 'sin cédula';
        }else{
          table.cells[2].innerHTML  = $('#cedula').val();
        }
        if($('#correo').val() == ''){
          table.cells[4].innerHTML  = 'sin correo';
        }else{
          table.cells[4].innerHTML  = $('#correo').val();
        }
        

        $('#alert-container').fadeIn(200);


        }else{      
              
        }
        cerrarModal();
      
      }).fail(function (jqXHR, textStatus, errorThrown){
        cerrarModal();
         
          
      });     
  }

function verificarUsuario(value){

  if(value.length == 0 || value == cedulaActual){
    $('#spam_cedula').fadeOut(200);
    usuarioEnUso = false;
  }else{
      var ced = $('#cedula').val();
      $.ajax({
          url: '/verificarCed',
          type: 'post',
          data: {_token: CSRF_TOKEN,cedula:ced}
      }).done(function( existe ) {
      
      if(existe == 0){
        $("#spam_cedula").css("color", "hsl(137, 64%, 49%)");
          $('#spam_cedula').html("<strong>&#x2713 Disponible!</strong>").fadeIn(200);
          usuarioEnUso = false;
          
          }else{      
              usuarioEnUso = true;
              $("#spam_cedula").css("color", "red");
              $('#spam_cedula').html("<strong>* Cédula en uso</strong>").fadeIn(200); 
              $('#cedula').focus();
          }
      
      }).fail(function (jqXHR, textStatus, errorThrown){
          usuarioEnUso = false;
          $('#cedula').val('');
          
      });     
  }
  
    
}

function pdf(){
  $.ajax({
    url: '/pdf',
    type: 'post',
    data: {_token: CSRF_TOKEN,cedula:'2251515'}
}).done(function( response ) {

alert(response);

}).fail(function (jqXHR, textStatus, errorThrown){
   
    
}); 
}