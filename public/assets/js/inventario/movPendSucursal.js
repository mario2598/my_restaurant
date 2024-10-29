window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var time = 15;
var tiempoSeteado = false;

function initialice() {
    actualizarMovimiento();
 
}

function actualizar(seg){
    
    if(!tiempoSeteado){
        time = seg * 1000;
        tiempoSeteado = true;
    }
    
    if($('#actualizarAutomatico').is(':checked')) 
    { 
        actualizarMovimiento();
    }
    setTimeout(function () { this.actualizar(); }, time);
}

function actualizarMovimiento(){
    
    $.ajax({
        url: '/actualizarMoviminetosInvPendSucursal',
        type: 'post',
        data: {_token: CSRF_TOKEN}
    }).done(function( tbody ) {
        if(tbody == '-1'){
            window.location.href = `${base_path}/`; //En base path esta el path principal
        }else{
            $('#tbodyMovimientos').html(tbody);
        }

    }).fail(function (jqXHR, textStatus, errorThrown){
    });  
}

function goMovimientoPendiente(id){
  $("#idMov").val(id);
  $("#formMovPend").submit();
}

function aceptarMovimientoSucursal(id){
    $("#idMovSuc").val(id);
    $("#detMovSuc").val($("#detalle_movimiento_generado").val());
    $("#formAceptarMovSuc").submit();
  }