
window.addEventListener("load", initialice, false);
"use strict";
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


function initialice() {
  var t=  document.getElementById('ape2');
    t.addEventListener('input',function(){ // limitar el tamaño a maximo 8
      if (this.value.length > 20) 
         this.value = this.value.slice(0,20); 
  });

  var t=  document.getElementById('ape1');
    t.addEventListener('input',function(){ // limitar el tamaño a maximo 8
      if (this.value.length > 20) 
         this.value = this.value.slice(0,20); 
  });

  var t=  document.getElementById('nombre');
    t.addEventListener('input',function(){ // limitar el tamaño a maximo 8
      if (this.value.length > 20) 
         this.value = this.value.slice(0,20); 
  });

  var t=  document.getElementById('telefono');
    t.addEventListener('input',function(){ // limitar el tamaño a maximo 8
      if (this.value.length > 8) 
         this.value = this.value.slice(0,8); 
  });

  var t=  document.getElementById('correo');
    t.addEventListener('input',function(){ // limitar el tamaño a maximo 8
      if (this.value.length > 120) 
         this.value = this.value.slice(0,120); 
  });

  var t=  document.getElementById('modelo');
    t.addEventListener('input',function(){ // limitar el tamaño a maximo 8
      if (this.value.length > 25) 
         this.value = this.value.slice(0,25); 
  });

  var t=  document.getElementById('marca');
    t.addEventListener('input',function(){ // 
      if (this.value.length > 25) 
         this.value = this.value.slice(0,25); 
  });

  var t=  document.getElementById('imei');
    t.addEventListener('input',function(){ //
      if (this.value.length > 30) 
         this.value = this.value.slice(0,30); 
  });
  
  var t=  document.getElementById('codigo');
    t.addEventListener('input',function(){ // 
      if (this.value.length > 20) 
         this.value = this.value.slice(0,20); 
  });

}

$(document).ready(function () {

  $("#buscar_cliente_tf").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#clientes_table tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});

  $(document).ready(function () {
     lienzo = document.getElementById('canvas');
     ctx = lienzo.getContext('2d');
 

     $('#dibujando_txt').val('false');

//Dejamos todo preparado para escuchar los eventos
    lienzo.addEventListener('mousedown',pulsaRaton,false);
    lienzo.addEventListener('mousemove',mueveRaton,false);
    lienzo.addEventListener('mouseup',levantaRaton,false);

   
  });

  function abrirModClientes(){
    $('#cliente_modal').modal('show');
  }


  function pulsaRaton(capturo){
    
    $('#dibujando_txt').val('true');
    //Indico que vamos a dibujar
    ctx.beginPath();
    //Averiguo las coordenadas X e Y por dónde va pasando el ratón

    lienzo = document.getElementById('canvas');

    var mousePos = getMousePos(lienzo,capturo);

    var rect = lienzo.getBoundingClientRect();
    var x = mousePos.x;
    var y = mousePos.y;

    //var x = capturo.clientX  - getOffset( lienzo ).left; 
   // var y = capturo.clientY - getOffset( lienzo ).top; 

  
  
    ctx.moveTo(x,y);
}

function mueveRaton(capturo){
 
  if($('#dibujando_txt').val() == 'true'){

    lienzo = document.getElementById('canvas');

    var mousePos = getMousePos(lienzo,capturo);

    var x = mousePos.x;
    var y = mousePos.y;

        
    drawCoordinates(x,y);
  }
}

function  getMousePos(canvas, evt) {
  var rect = canvas.getBoundingClientRect(), // abs. size of element
      scaleX = canvas.width / rect.width,    // relationship bitmap vs. element for X
      scaleY = canvas.height / rect.height;  // relationship bitmap vs. element for Y

  return {
    x: (evt.clientX - rect.left) * scaleX,   // scale mouse coordinates after they have
    y: (evt.clientY - rect.top) * scaleY     // been adjusted to be relative to element
  }
}
/*
function getMousePos(canvas, evt) {
  var rect = canvas.getBoundingClientRect();
  return {
    x: evt.clientX - rect.left,
    y: evt.clientY - rect.top
  };
}*/


function levantaRaton(capturo){
  //Indico que termino el dibujo
       ctx.closePath();
       $('#dibujando_txt').val('false');

  }


  function getOffset( el ) {
    var _x = 0;
    var _y = 0;
    while( el && !isNaN( el.offsetLeft ) && !isNaN( el.offsetTop ) ) {
        _x += el.offsetLeft - el.scrollLeft;
        _y += el.offsetTop - el.scrollTop;
        el = el.offsetParent;
    }
    return { top: _y, left: _x };
    }

    function drawCoordinates(x,y){
      var pointSize = 3; // Cambia el tamaño del punto
      var ctx = document.getElementById("canvas").getContext("2d");
  
  
      ctx.fillStyle = "#000"; // Color rojo
  
      ctx.beginPath(); // Iniciar trazo
      ctx.arc(x, y, pointSize, 0, Math.PI * 2, true); // Dibujar un punto usando la funcion arc
      ctx.fill(); // Terminar trazo
  }

  function borrar(){
    lienzo = document.getElementById('canvas');
    lienzo.width = lienzo.width;
  }

  function cargarCliente(id,ced,nombre,ape1,ape2,correo,telefono){
    $('#nombre').val(nombre);
    $('#ape1').val(ape1);
    $('#ape2').val(ape2);
    $('#telefono').val(telefono);
    $('#correo').val(correo);
    $('#cedula').val(ced);
    $('#id').val(id);
    $('#cliente_modal').modal('hide');
    $("#buscar_cliente_tf").val('').trigger('keyup');
    
  }

  function cerrarModal(){
    $('#cliente_modal').modal('hide');
    $("#buscar_cliente_tf").val('').trigger('keyup');
    
  }

  
  function cambiarPatron(){
    borrar();
    $("#titulo_patron_container").css("color", "black");
    $('#titulo_patron_container').html("<strong>Nuevo patrón</strong>").fadeIn(50);
    $('#aniimated-thumbnials').fadeOut(50);
    $("#canvas_patron_container").fadeIn(200);
    
  }

  function guardarPatron(){
    $('#modal_spinner').fadeIn(50);
    $("#canvas_patron_container").fadeOut(50);
    $('#titulo_patron_container').html('Guardando ...');

    var canvas = document.getElementById('canvas');
    var dataURL = canvas.toDataURL('image/png');
    var trabId = $('#id').val();


    $.ajax({
      url: '/nuevoPatron',
      type: 'post',
      data: {_token: CSRF_TOKEN, canvas : dataURL, id : trabId}
    }).done(function( response ) {
      $('#modal_spinner').fadeOut(50);
      $('#aniimated-thumbnials').fadeIn(50);
     

     if(response == '1'){
      $("#titulo_patron_container").css("color", "hsl(137, 64%, 49%)");

      var image = document.getElementById('img_patron');
      image.src = canvas.toDataURL();

      $('#titulo_patron_container').html("<strong>&#x2713 Se guardo correctamente!</strong>").fadeIn(50);
     }else{
      $("#titulo_patron_container").css("color", "red");
      $('#titulo_patron_container').html("<strong>* Algo salío mal, reintentalo!</strong>").fadeIn(50);
      $('#aniimated-thumbnials').fadeOut(50);

     }
      
    }).fail(function (jqXHR, textStatus, errorThrown){
      $("#titulo_patron_container").css("color", "red");
      $('#titulo_patron_container').html("<strong>* Algo salío mal, reintentalo!</strong>").fadeIn(50);
      $('#aniimated-thumbnials').fadeOut(50);
     
    });
  }

