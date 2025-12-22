@extends('layout.master')

@section('style')
<link rel="stylesheet" href="{{asset("assets/bundles/datatables/datatables.min.css")}}">
<link rel="stylesheet" href="{{asset("assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css")}}">
<link rel="stylesheet" href="{{asset("assets/bundles/izitoast/css/iziToast.min.css")}}">

<style>
  /* Estilos personalizados para DataTables */
  .dataTables_wrapper .dataTables_length,
  .dataTables_wrapper .dataTables_filter,
  .dataTables_wrapper .dataTables_info,
  .dataTables_wrapper .dataTables_processing,
  .dataTables_wrapper .dataTables_paginate {
    margin-bottom: 15px;
  }

  /* Mejorar el estilo de los controles de DataTables */
  .dataTables_wrapper .dataTables_length select {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px 10px;
    margin: 0 5px;
    background-color: #fff;
  }

  .dataTables_wrapper .dataTables_filter input {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 8px 12px;
    margin-left: 10px;
    width: 250px;
    background-color: #fff;
    transition: border-color 0.3s ease;
  }

  .dataTables_wrapper .dataTables_filter input:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
  }

  /* Estilo para los botones de paginación */
  .dataTables_wrapper .dataTables_paginate .paginate_button {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 8px 12px;
    margin: 0 2px;
    background-color: #fff;
    color: #333;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background-color: #007bff;
    color: #fff;
    border-color: #007bff;
  }

  .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background-color: #007bff;
    color: #fff;
    border-color: #007bff;
  }

  .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
    background-color: #f8f9fa;
    color: #6c757d;
    border-color: #dee2e6;
    cursor: not-allowed;
  }

  /* Contenedor principal de controles */
  .dataTables_wrapper .dataTables_length,
  .dataTables_wrapper .dataTables_filter {
    display: inline-block;
    vertical-align: top;
  }

  .dataTables_wrapper .dataTables_length {
    float: left;
  }

  .dataTables_wrapper .dataTables_filter {
    float: right;
  }

  /* Información de la tabla */
  .dataTables_wrapper .dataTables_info {
    clear: both;
    padding-top: 10px;
    color: #666;
    font-size: 14px;
  }

  /* Spinner de carga */
  .dataTables_processing {
    background-color: rgba(255, 255, 255, 0.9);
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    font-weight: bold;
    color: #333;
  }

  /* Responsive para móviles */
  @media (max-width: 768px) {
    .dataTables_wrapper .dataTables_filter input {
      width: 200px;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
      display: block;
      float: none;
      margin-bottom: 10px;
    }

    .dataTables_wrapper .dataTables_filter {
      text-align: left;
    }
  }

  /* Estilo para el botón de agregar cliente */
  .btn-agregar-cliente {
    background: linear-gradient(45deg, #28a745, #20c997);
    border: none;
    border-radius: 6px;
    padding: 10px 20px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
    transition: all 0.3s ease;
  }

  .btn-agregar-cliente:hover {
    background: linear-gradient(45deg, #218838, #1ea085);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4);
    color: white;
  }

  .btn-agregar-cliente:focus {
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.5);
  }

  /* Estilos para los botones de acción en la tabla */
  .table .btn {
    margin: 2px;
    border-radius: 4px;
    transition: all 0.3s ease;
    font-size: 12px;
    padding: 6px 10px;
  }

  .table .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  }

  .table .btn-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border: none;
  }

  .table .btn-primary:hover {
    background: linear-gradient(45deg, #0056b3, #004085);
  }

  .table .btn-info {
    background: linear-gradient(45deg, #17a2b8, #138496);
    border: none;
  }

  .table .btn-info:hover {
    background: linear-gradient(45deg, #138496, #117a8b);
  }

  .table .btn-danger {
    background: linear-gradient(45deg, #dc3545, #c82333);
    border: none;
  }

  .table .btn-danger:hover {
    background: linear-gradient(45deg, #c82333, #bd2130);
  }

  /* Mejorar el estilo de los badges */
  .badge {
    font-size: 11px;
    padding: 6px 10px;
    border-radius: 12px;
    font-weight: 600;
  }

  .badge-success {
    background: linear-gradient(45deg, #28a745, #20c997);
  }

  .badge-warning {
    background: linear-gradient(45deg, #ffc107, #e0a800);
    color: #212529;
  }

  /* Estilo para la tabla */
  .table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 123, 255, 0.05);
  }

  .table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.5px;
  }

  .table td {
    vertical-align: middle;
    border-top: 1px solid #dee2e6;
  }

  /* Animación para la carga de la tabla */
  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(20px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .table {
    animation: fadeIn 0.5s ease-in-out;
  }
</style>

@endsection


@section('content')

@include('layout.sidebar')

<div class="main-content">
  <section class="section">
    <div class="section-body">
      <div class="card card-warning">
        <div class="card-header">
          <h4>Clientes</h4>
          <form class="card-header-form">
            <div class="input-group">
              <input type="text" name="" id="input_buscar_generico" class="form-control" placeholder="Buscar..">
              <div class="input-group-btn">
                <a class="btn btn-primary btn-icon" style="cursor: pointer;" onclick="$('#input_buscar_generico').trigger('change');"><i class="fas fa-search"></i></a>
              </div>
            </div>
          </form>
        </div>
        <div class="card-body">

          <div class="row" style="width: 100%">
            <div class="col-sm-12 col-md-3">
              <div class="form-group">
                <a class="btn btn-primary btn-agregar-cliente" title="Agregar Cliente"
                  style="color:white;" onclick="abrirModalNuevoCliente()">
                  <i class="fas fa-plus"></i> Agregar Cliente
                </a>
              </div>
            </div>
          </div>
          <div id="contenedor_gastos" class="row">
            <div class="table-responsive">
              <table class="table table-striped" id="tabla_clientes">
                <thead>
                  <tr>
                    <th class="space-align-center">Nombre</th>
                    <th class="space-align-center">Apellidos</th>
                    <th class="space-align-center">Télefono</th>
                    <th class="space-align-center">Correo</th>
                    <th class="space-align-center">Ubicación</th>
                    <th class="space-align-center">FE Configurado</th>
                    <th class="space-align-center">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Los datos se cargarán por AJAX -->
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>

    </div>
  </section>

</div>


<!-- modal modal de agregar proveedor -->
<div class="modal fade bs-example-modal-center" id='mdl_generico' tabindex="-1" role="dialog"
  aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status"></div>
        <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Cliente</h5>
        <button type="button" id='btnSalirFact' class="close" aria-hidden="true" onclick="cerrarModalGenerico()">x</button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xl-12 col-sm-12">
            <div class="form-group form-float">
              <div class="form-line">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control space_input_modal" id="mdl_generico_ipt_nombre" name="mdl_generico_ipt_nombre" required maxlength="50">
              </div>
            </div>
          </div>
          <div class="col-xl-12 col-sm-12">
            <div class="form-group form-float">
              <div class="form-line">
                <label class="form-label">Apellidos</label>
                <input type="text" class="form-control space_input_modal" id="mdl_generico_ipt_apellidos" name="mdl_generico_ipt_apellidos" required maxlength="50">
              </div>
            </div>
          </div>
          <div class="col-xl-12 col-sm-12">
            <div class="form-group form-float">
              <div class="form-line">
                <label class="form-label">Teléfono (+506)</label>
                <input type="number" class="form-control space_input_modal" id="mdl_generico_ipt_tel" name="mdl_generico_ipt_tel" maxlength="8">
              </div>
            </div>
          </div>
          <div class="col-xl-12 col-sm-12">
            <div class="form-group form-float">
              <div class="form-line">
                <label class="form-label">Correo</label>
                <input type="email" class="form-control space_input_modal" id="mdl_generico_ipt_correo" name="mdl_generico_ipt_correo" maxlength="100">
              </div>
            </div>
          </div>

          <div class="col-xl-12 col-sm-12">
            <div class="form-group form-float" style="display: block;">
              <label for="mdl_generico_ipt_ubicacion" class="form-label">Ubicación</label>
              <textarea id="mdl_generico_ipt_ubicacion" class="space_input_modal" name="mdl_generico_ipt_ubicacion" style="width:100%;" maxlength="300"></textarea>
            </div>
          </div>
        </div>
      </div>
      <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
        <a href="#" class="btn btn-secondary" onclick="cerrarModalGenerico()">Volver</a>
        <input type="button" class="btn btn-primary" value="Guardar" onclick="guardarCliente()" />
        <input type="reset" class="btn btn-primary">
      </div>

    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -- fin modal de agregar sucursal-->

<!-- Modal para Configuración de Facturación Electrónica de Clientes -->
<div class="modal fade bs-example-modal-center" id='mdl-config-fe-cliente' tabindex="-1" role="dialog"
  aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="width: 100%">
        <div class="row" style="width: 100%">
          <div class="col-sm-12 col-md-12 col-xl-12">
            <h5 class="modal-title">Configuración de Facturación Electrónica - Cliente</h5>
          </div>
        </div>
      </div>
      <div class="modal-body">
        <!-- Campo oculto para el ID del cliente -->
        <input type="hidden" id="id_cliente_fe" value="">


        <div class="card-body">
          <div class="row">
            <div class="col-sm-12 col-md-6 col-xl-6">
              <div class="form-group">
                <label>Código de Actividad Económica</label>
                <input type="text" class="form-control" id="codigo_actividad_cliente"
                  placeholder="722003" maxlength="10" value="722003">
                <small class="form-text text-muted">Código según catálogo del MEIC</small>
              </div>
            </div>

            <div class="col-sm-12 col-md-6 col-xl-6">
              <div class="form-group">
                <label>Tipo de Identificación</label>
                <select class="form-control" id="tipo_identificacion_cliente">
                  <option value="01">01 - Cédula Física</option>
                  <option value="02">02 - Cédula Jurídica</option>
                </select>
                <small class="form-text text-muted">Tipo de identificación del cliente</small>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-12 col-md-6 col-xl-6">
              <div class="form-group">
                <label>Número de Cédula</label>
                <input type="text" class="form-control" id="cedula_cliente"
                  placeholder="Ej: 123456789" maxlength="20">
                <small class="form-text text-muted">Número de cédula del cliente</small>
              </div>
            </div>

            <div class="col-sm-12 col-md-6 col-xl-6">
              <div class="form-group">
                <label>Teléfono</label>
                <input type="text" class="form-control" id="telefono_cliente"
                  placeholder="Ej: 88888888" maxlength="20">
                <small class="form-text text-muted">Teléfono del cliente</small>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-12 col-md-12 col-xl-12">
              <div class="form-group">
                <label>Nombre Comercial</label>
                <input type="text" class="form-control" id="nombre_comercial_cliente"
                  placeholder="Nombre comercial del cliente" maxlength="200">
                <small class="form-text text-muted">Nombre comercial que aparecerá en la factura</small>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-12 col-md-12 col-xl-12">
              <div class="form-group">
                <label>Dirección Completa</label>
                <textarea class="form-control" id="direccion_cliente" rows="3"
                  placeholder="Dirección completa del cliente" maxlength="500"></textarea>
                <small class="form-text text-muted">Dirección que aparecerá en la factura</small>
              </div>
            </div>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <div class="row" style="width: 100%">
          <div class="col-sm-12 col-md-12 col-xl-12 text-right">
            <a class="btn btn-primary btn-icon" title="Guardar" onclick='guardarConfigFECliente()' style="color:white; cursor: pointer;">
              <i class="fas fa-save"></i> Guardar Configuración
            </a>
            <a class="btn btn-secondary btn-icon" title="Cerrar" onclick='cerrarConfigFECliente()'
              style="cursor: pointer;">
              <i class="fas fa-times"></i> Cerrar
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection



@section('script')
<script src="{{asset("assets/bundles/sweetalert/sweetalert.min.js")}}"></script>

<script src="{{asset("assets/bundles/datatables/datatables.min.js")}}"></script>
<script src="{{asset("assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js")}}"></script>
<script src="{{asset("assets/bundles/jquery-ui/jquery-ui.min.js")}}"></script>
<script src="{{asset("assets/js/page/datatables.js")}}"></script>
<script src="{{asset("assets/js/mant_clientes.js")}}"></script>




@endsection