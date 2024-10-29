window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var time = 15;
var tiempoSeteado = false;

function initialice() {
    document.getElementById('desdeFecha').value = moment().format('YYYY-MM-DD');
    document.getElementById('hastaFecha').value = moment().format('YYYY-MM-DD');
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
    let despacho = $('#select_despacho').val();
    let destino = $('#select_destino').val();
    let tipo_movimiento = $('#tipo_movimiento').val();
    let estado = $('#select_estado').val();
    let has =$('#hastaFecha').val();
    let des =$('#desdeFecha').val();
    $.ajax({
        url: '/actualizarBitacoraMoviminetosInv',
        type: 'post',
        data: {_token: CSRF_TOKEN,desde:des,hasta:has,desp:despacho,dest:destino,tip_mov:tipo_movimiento,est:estado}
    }).done(function( tbody ) {
        if(tbody == '-1'){
            window.location.href = `${base_path}/`; //En base path esta el path principal
        }else{
            $('#tbodyBitacoraMovimientos').html(tbody);
        }

    }).fail(function (jqXHR, textStatus, errorThrown){
    });  
}

