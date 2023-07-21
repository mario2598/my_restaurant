'use strict';

var str_b64;
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var usuarioEnUso = false;


$(function () {
    //Horizontal form basic
    $('#wizard_horizontal').steps({
        headerTag: 'h2',
        bodyTag: 'section',
        transitionEffect: 'slideLeft',
        onInit: function (event, currentIndex) {
            setButtonWavesEffect(event);
        },
        onStepChanged: function (event, currentIndex, priorIndex) {
            setButtonWavesEffect(event);
        }
    });

    //Vertical form basic
    $('#wizard_vertical').steps({
        headerTag: 'h2',
        bodyTag: 'section',
        transitionEffect: 'slideLeft',
        stepsOrientation: 'vertical',
        onInit: function (event, currentIndex) {
            setButtonWavesEffect(event);
        },
        onStepChanged: function (event, currentIndex, priorIndex) {
            setButtonWavesEffect(event);
        }
    });

    //Advanced form with validation
    var form = $('#wizard_with_validation').show();
    form.steps({
        headerTag: 'h3',
        bodyTag: 'fieldset',
        transitionEffect: 'slideLeft',
        onInit: function (event, currentIndex) {

            //Set tab width
            var $tab = $(event.currentTarget).find('ul[role="tablist"] li');
            var tabCount = $tab.length;
            $tab.css('width', (100 / tabCount) + '%');

            //set button waves effect
            setButtonWavesEffect(event);
        },
        onStepChanging: function (event, currentIndex, newIndex) {
            if (usuarioEnUso){
                $("#spam_cedula").css("color", "red");
                $('#spam_cedula').html("<strong>* Cedula en uso</strong>").fadeIn(200); 
                $('#cedula').focus();

            }else{
                if (currentIndex > newIndex) { return true; }

                    if (currentIndex < newIndex) {
                        form.find('.body:eq(' + newIndex + ') label.error').remove();
                        form.find('.body:eq(' + newIndex + ') .error').removeClass('error');
                    }

                    form.validate().settings.ignore = ':disabled,:hidden';
                    return form.valid();
            }
            
        },
        onStepChanged: function (event, currentIndex, priorIndex) {
            setButtonWavesEffect(event);
        },
        onFinishing: function (event, currentIndex) {
            form.validate().settings.ignore = ':disabled';
            return form.valid();
        },
        onFinished: function (event, currentIndex) {
            
            $('#modal_spinner').fadeIn(50);
            $('#tituloForm').html("<strong>Guardando ..</strong>").fadeIn(200);

            //el canvas en imagen base 64
            var canvas = document.getElementById('canvas');
            var dataURL = canvas.toDataURL('image/png');

            // se obtiene la imagen en caso de no traer imagen se devuelve null
            var img_adicional = document.getElementById('img_adicional');
                 
            img_adicional = img_adicional.files[0];
        
            if (img_adicional != undefined){
               
                img_adicional = $('#file_foto').val();
                 
            }else{
                img_adicional = null;
            }
            

            if($('#cedula').val() != null){
                var cedula = $('#cedula').val();
            }else{ var cedula = 0;}

            var nombre = $('#nombre').val();
            var ape1 = $('#ape1').val();

            // se obtiene apellido 2
            
            if( $('#ape2').val() != null){
                var ape2 = $('#ape2').val();
            }else
            { var ape2 = '';}

            if( $('#ape2').val() != null){
                var ape2 = $('#ape2').val();
            }else
            { var ape2 = '';}

            if( $('#imei').val() != null){
                var imei = $('#imei').val();
            }else
            { var imei = '';}

            if( $('#dispositivo').val() != null){
                var dispositivo = $('#dispositivo').val();
            }else
            { var dispositivo = '';}

            var telefono = $('#telefono').val();

            // se obtiene correo 

            if( $('#correo').val() != null){
                var correo = $('#correo').val();
            }else
            { var correo = '';}

            var tapa,bateria,tornillos,bandeja_sim,tarjeta_sd,rayas,quebrado,golpes;

            $("#rb_tapa").prop("checked") ?  tapa ='s' : tapa ='n';
            $("#rb_bateria").prop("checked") ?  bateria ='s' : bateria ='n';
            $("#rb_tornillos").prop("checked") ?  tornillos ='s' : tornillos ='n';
            $("#rb_bandeja_sim").prop("checked") ?  bandeja_sim ='s' : bandeja_sim ='n';
            $("#rb_tarjeta_sd").prop("checked") ?  tarjeta_sd ='s' : tarjeta_sd ='n';
            $("#rb_rayas").prop("checked") ?  rayas ='s' : rayas ='n';
            $("#rb_quebrado").prop("checked") ?  quebrado ='s' : quebrado ='n';
            $("#rb_golpes").prop("checked") ?  golpes ='s' : golpes ='n';

            var id_usuario = $('#id').val();
            var observacion = $('#dp_observacion').val()
            var detalle_falla =  $('#dp_detalle_falla').val();

            var marca = $('#marca').val();
            var modelo = $('#modelo').val()
            var numSerie =  $('#numSerie').val();
            
            var codigo =  $('#codigo').val();

           // alert(img_adicional);

           
            $.ajax({
                url: '/nuevoTrabajo',
                type: 'post',
                data: {_token: CSRF_TOKEN,disp:dispositivo,ns : numSerie, mar : marca,mod : modelo, cod : codigo, im : imei, id_usu: id_usuario , canvas : dataURL, img_adi : img_adicional, ced : cedula, 
                nom : nombre , a1 : ape1 , a2: ape2, tel : telefono , mail : correo , tap : tapa, bat : bateria, tor : tornillos, sim : bandeja_sim,
                sd : tarjeta_sd, ray : rayas, queb : quebrado, gol : golpes, obs : observacion , det_falla : detalle_falla }
              }).done(function( response ) {
                $('#modal_spinner').fadeOut(50);
                $('#btn-header').fadeIn(100);
               if(response != '0'){
                
                $("#tituloForm").css("color", "hsl(137, 64%, 49%)");
                $('#tituloForm').html("<strong>&#x2713 Se guardo el trabajo correctamente!</strong>").fadeIn(200);
                var urlAux = $('#btn-imprimir-tickete').prop("href");
                urlAux = urlAux+'/'+response;
               
                $('#btn-imprimir-tickete').prop("href",urlAux);
                
                $('#idTrabajoNuevo').val(response);
                $('#btn-imprimir-tickete').fadeIn(50);
               }else{
                $("#tituloForm").css("color", "red");
                $('#tituloForm').html("<strong>* Algo salío mal, reintentalo!</strong>").fadeIn(200);

               }
                
              }).fail(function (jqXHR, textStatus, errorThrown){
                
                $('#modal_spinner').fadeOut(50);
                $('#btn-header').fadeIn(100);
                $("#tituloForm").css("color", "red");
                $('#tituloForm').html("<strong>* Algo salío mal en el servidor, reintentalo!</strong>").fadeIn(200);
              });
         
          //  alert($("#rb_tapa").val());
          //  alert($("#rb_bateria").val());

            
            form.hide();
        }
    });

    form.validate({
        highlight: function (input) {
            $(input).parents('.form-line').addClass('error');
        },
        unhighlight: function (input) {
            $(input).parents('.form-line').removeClass('error');
        },
        errorPlacement: function (error, element) {
            $(element).parents('.form-group').append(error);
        },
        rules: {
            'confirm': {
                equalTo: '#password'
            }
        }
    });


});


function setButtonWavesEffect(event) {
    $(event.currentTarget).find('[role="menu"] li a').removeClass('waves-effect');
    $(event.currentTarget).find('[role="menu"] li:not(.disabled) a').addClass('waves-effect');
}

  
  //funcion que carga imagen luego de seleccionarla en el input
function cargarB64_input() {
    var oFReader = new FileReader();
    oFReader.readAsDataURL(document.getElementById("img_adicional").files[0]);
  
    oFReader.onload = function (oFREvent) {
      
        document.getElementById("file_foto").setAttribute("value", oFREvent.target.result);
       // $('#file_foto').val(oFREvent.target.result);
     
       // $('#imagen_foto').prop('src', oFREvent.target.result);
    };
  };

 

  function verificarUsuario(value){

    if(value.length == 0){
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