window.addEventListener("load", initialice, false);
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


function initialice() {


}

/**
 * Cierra el modal 
 */
function calcularCaja(gastos){
  let efectivo = $('#monto_efectivo').val();
  let tarjeta = $('#monto_tarjeta').val();
  let sinpe = $('#monto_sinpe').val();
  
  if(efectivo == ""){
    efectivo = 0;
  }
  if(tarjeta == ''){
    tarjeta = 0;
    
  }
  if(sinpe == ''){
    sinpe = 0;
  }

  let subtotal = parseFloat(efectivo) + parseFloat(tarjeta) + parseFloat(sinpe);
  
  let total = parseFloat(subtotal) - parseFloat(gastos) ;
  $('#totalCaja').val(subtotal);
  total = parseFloat(total).toFixed(2);
  subtotal = parseFloat(subtotal).toFixed(2);
  gastos = parseFloat(gastos).toFixed(2);
  efectivo = parseFloat(efectivo).toFixed(2);
  tarjeta = parseFloat(tarjeta).toFixed(2);
  sinpe = parseFloat(sinpe).toFixed(2);

  if(parseFloat(total) >= 0){
    $('#monto_total_lbl').html("CRC <strong>"+total.replace(/\d(?=(\d{3})+\.)/g, '$&,' + "</strong>"));
  }
  if(parseFloat(efectivo) >= 0){
    $('#monto_efectivo_lbl').html("CRC <strong>"+efectivo.replace(/\d(?=(\d{3})+\.)/g, '$&,' + "</strong>"));
  }
  if(parseFloat(tarjeta) >= 0){
    $('#monto_tarjetas_lbl').html("CRC <strong>"+tarjeta.replace(/\d(?=(\d{3})+\.)/g, '$&,' + "</strong>"));
  }
  if(parseFloat(sinpe) >= 0){
    $('#monto_sinpe_lbl').html("CRC <strong>"+sinpe.replace(/\d(?=(\d{3})+\.)/g, '$&,' + "</strong>"));
  }
  if(parseFloat(subtotal) >= 0){
    $('#monto_subtotal_lbl').html("CRC <strong>"+subtotal.replace(/\d(?=(\d{3})+\.)/g, '$&,' + "</strong>"));
  }
  
  
}

/** modales  */
/**
 * Abre el modal y carga los datos correspondientes
 * @param {id} id 
 * @param {nombre proveedor} id 
 * @param {descripcion  del proveedor} desc 
 */
function editarGenerico(id,banco,porcentaje) {
  $('#mdl_generico_ipt_nombre').val(banco);
  $('#mdl_generico_ipt_porcentaje').val(porcentaje);
  $('#mdl_generico_ipt_id').val(id);
  $('#mdl_generico').modal('show');
}

/**
 * Cierra el modal 
 */
function cerrarModalGenerico(){
  $('#mdl_generico').modal('hide');
}

/**
 * Abre el modal de sucursales y limpia los valores
 */
function nuevoGenerico(){
  $('#mdl_generico_ipt_nombre').val("");
  $('#mdl_generico_ipt_porcentaje').val("0");
  $('#mdl_generico_ipt_id').val('-1');
  $('#mdl_generico').modal('show');
}

function eliminarGenerico(id){
  swal({
    title: 'Seguro de inactivar el banco?',
    text: 'No podra deshacer esta acción!',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  })
    .then((willDelete) => {
      if (willDelete) {
        swal.close();
        $('#idGenericoEliminar').val(id);
        $('#frmEliminarGenerico').submit();
        
      } else {
        swal.close();
      }
    });
 
  
}

function calcularMontoEfectivo(){
  let efectivo_mon_5 = $('#efectivo_mon_5').val();
  let efectivo_mon_10 = $('#efectivo_mon_10').val();
  let efectivo_mon_25 = $('#efectivo_mon_25').val();
  let efectivo_mon_50 = $('#efectivo_mon_50').val();
  let efectivo_mon_100 = $('#efectivo_mon_100').val();
  let efectivo_mon_500 = $('#efectivo_mon_500').val();

  let efectivo_bill_1000 = $('#efectivo_bill_1000').val();
  let efectivo_bill_2000 = $('#efectivo_bill_2000').val();
  let efectivo_bill_5000 = $('#efectivo_bill_5000').val();
  let efectivo_bill_10000 = $('#efectivo_bill_10000').val();
  let efectivo_bill_20000 = $('#efectivo_bill_20000').val();
  let efectivo_bill_50000 = $('#efectivo_bill_50000').val();

  let monto_5 =  parseFloat(efectivo_mon_5) * parseFloat(5);
  let monto_10 = parseFloat(efectivo_mon_10) * parseFloat(10);
  let monto_25 = parseFloat(efectivo_mon_25) * parseFloat(25);
  let monto_50 =parseFloat(efectivo_mon_50) * parseFloat(50);
  let monto_100 = parseFloat(efectivo_mon_100) * parseFloat(100);
  let monto_500 = parseFloat(efectivo_mon_500) * parseFloat(500);

  let monto_1000 = parseFloat(efectivo_bill_1000) * parseFloat(1000);
  let monto_2000 = parseFloat(efectivo_bill_2000) * parseFloat(2000);
  let monto_5000 = parseFloat(efectivo_bill_5000) * parseFloat(5000);
  let monto_10000 = parseFloat(efectivo_bill_10000) * parseFloat(10000);
  let monto_20000 = parseFloat(efectivo_bill_20000) * parseFloat(20000);
  let monto_50000 = parseFloat(efectivo_bill_50000) * parseFloat(50000);

  let montoTotal = parseFloat(monto_5) + parseFloat(monto_10) + parseFloat(monto_25)
   + parseFloat(monto_50) + parseFloat(monto_100) + parseFloat(monto_500) 
  + parseFloat(monto_1000) + parseFloat(monto_2000) + parseFloat(monto_5000) + parseFloat(monto_10000)
  + parseFloat(monto_20000) + parseFloat(monto_50000);
  $('#monto_efectivo').val(montoTotal);

  calcularCaja(gastosCaja);
}