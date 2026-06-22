@extends('layout.master')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/bundles/pretty-checkbox/pretty-checkbox.min.css') }}">
@endsection


@section('content')
@include('layout.sidebar')

<div class="main-content">
    <section class="section">
        <div class="section-body">
            <div class="card card-warning">
                <div class="card-header">
                    <h4>Sucursales</h4>
                    <form class="card-header-form">
                        <div class="input-group">
                            <input type="text" name="" id="input_buscar_sucursal" class="form-control"
                                placeholder="Buscar..">
                            <div class="input-group-btn">
                                <a class="btn btn-primary btn-icon" style="cursor: pointer;"
                                    onclick="$('#input_buscar_generico').trigger('change');"><i
                                        class="fas fa-search"></i></a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body">

                    <div class="row" style="width: 100%">
                        <div class="col-sm-12 col-md-2">
                            <div class="form-group">
                                <a class="btn btn-primary" title="Agregar Sucursal" style="color:white;"
                                    onclick="nuevaSucursal()">+ Agregar</a>
                            </div>
                        </div>


                    </div>
                    <div id="contenedor_gastos" class="row">
                        <div class="table-responsive">
                            <table class="table table-striped" id="">
                                <thead>
                                    <tr>
                                        <th class="space-align-center">Código</th>
                                        <th class="space-align-center">Sucursal</th>
                                        <th class="space-align-center">Correo Factura</th>
                                        <th class="space-align-center">Estado</th>
                                        <th class="space-align-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_sucursal">
                                    @foreach ($data['sucursales'] as $s)
                                    <tr>
                                        <td class="space-align-center">{{ $s->id }}</td>
                                        <td class="space-align-center">{{ $s->descripcion }}</td>
                                        <td class="space-align-center">{{ $s->correo_factura ?? 'Sin Asignar'}}</td>

                                        <!-- Estado como texto: Activa o Inactiva según el valor de 'estado' -->
                                        <td class="space-align-center">
                                            @if ($s->estado === 'A')
                                            <span class="badge badge-success">Activa</span>
                                            @else
                                            <span class="badge badge-danger">Inactiva</span>
                                            @endif
                                        </td>

                                        <!-- Acciones de Editar y Eliminar -->
                                        <td class="space-align-center">
                                            <a onclick='editarSucursal("{{ $s->id }}","{{ $s->descripcion }}")'
                                                title="Editar" class="btn btn-primary" style="color:white"><i
                                                    class="fas fa-cog"></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

</div>


<!-- modal modal de agregar sucursal -->
<div class="modal fade bs-example-modal-center" id='mdl_sucursal' tabindex="-1" role="dialog"
    aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ URL::to('guardarsucursal') }}" autocomplete="off" method="POST">
                {{ csrf_field() }}
                <div class="modal-header">

                    <div class="spinner-border" id='modal_spinner' style='margin-right:3%;display:none;' role="status">
                    </div>
                    <h5 class="modal-title mt-0" id="edit_cliente_text"><i class="fas fa-cog"></i> Sucursal</h5>
                    <button type="button" class="close" aria-hidden="true" onclick="cerrarModalSucursal()">x</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Descripción (Lugar)</label>
                                    <input type="text" class="form-control space_input_modal" required maxlength="50"
                                        id="mdl_sucursal_ipt_descripcion" name="mdl_sucursal_ipt_descripcion">
                                    <input type="hidden" id="mdl_sucursal_ipt_id" name="mdl_sucursal_ipt_id"
                                        value="-1">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nuevos campos agregados -->
                    <div class="row">
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Nombre Factura</label>
                                    <input type="text" class="form-control" maxlength="500"
                                        id="mdl_sucursal_ipt_nombre_factura" name="mdl_sucursal_ipt_nombre_factura"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-xl-6">
                            <div class="form-group">
                                <label>Tipo de Identificación del Emisor</label>
                                <select class="form-control" id="tipo_identificacion_emisor" name="tipo_identificacion_emisor">
                                    <option value="">Seleccione tipo de identificación</option>
                                    <option value="01">Cédula Física</option>
                                    <option value="02">Cédula Jurídica</option>
                                   
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Cédula Factura</label>
                                    <input type="text" class="form-control" maxlength="50"
                                        id="mdl_sucursal_ipt_cedula_factura" name="mdl_sucursal_ipt_cedula_factura"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Correo Factura</label>
                                    <input type="email" class="form-control" maxlength="500"
                                        id="mdl_sucursal_ipt_correo_factura" name="mdl_sucursal_ipt_correo_factura"
                                        required>
                                </div>
                            </div>
                        </div>

                        <!-- Indicador de si está activa o no -->
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <label class="form-label">Sucursal Activa</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="mdl_sucursal_chk_activa"
                                        name="mdl_sucursal_chk_activa">
                                    <label class="form-check-label" for="mdl_sucursal_chk_activa">
                                        Activa
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- ── Config impresión ── -->
                        <div class="col-12 mt-2">
                            <hr style="margin:4px 0 10px;">
                            <h6 class="font-weight-bold"><i class="fas fa-print"></i> Configuración de Impresión</h6>
                        </div>

                        <!-- Modo impresión -->
                        <div class="col-12 mb-2">
                            <label class="font-weight-bold d-block mb-2">Modo de impresión al cobrar</label>
                            <input type="hidden" id="mdl_sucursal_ticket_modo" name="mdl_sucursal_ticket_modo" value="html">
                            <div class="row" id="ticket-modo-cards">
                                <div class="col-6">
                                    <div class="ticket-modo-card" data-modo="html"
                                         onclick="seleccionarTicketModo('html')"
                                         style="border:2px solid #dee2e6; border-radius:10px;
                                                padding:14px 10px; text-align:center; cursor:pointer;
                                                transition:all .18s; background:#fff; user-select:none;">
                                        <div style="font-size:1.8rem; margin-bottom:4px;">&#128424;</div>
                                        <div class="font-weight-bold" style="font-size:.85rem;">HTML</div>
                                        <div class="text-muted" style="font-size:.75rem;">Ventana emergente (auto-imprime)</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="ticket-modo-card" data-modo="qz"
                                         onclick="seleccionarTicketModo('qz')"
                                         style="border:2px solid #dee2e6; border-radius:10px;
                                                padding:14px 10px; text-align:center; cursor:pointer;
                                                transition:all .18s; background:#fff; user-select:none;">
                                        <div style="font-size:1.8rem; margin-bottom:4px;">&#9889;</div>
                                        <div class="font-weight-bold" style="font-size:.85rem;">QZ Tray</div>
                                        <div class="text-muted" style="font-size:.75rem;">Impresion directa silenciosa</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group">
                                <label>Ancho del papel <small class="text-muted">(solo modo HTML)</small></label>
                                <select class="form-control" id="mdl_sucursal_ancho_mm" name="mdl_sucursal_ancho_mm">
                                    <option value="80">80 mm (estándar)</option>
                                    <option value="58">58 mm (compacto)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="mdl_sucursal_chk_auto_imprimir"
                                        name="mdl_sucursal_chk_auto_imprimir" checked>
                                    <label class="form-check-label" for="mdl_sucursal_chk_auto_imprimir">
                                        Abrir diálogo de impresión automáticamente al cobrar
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group">
                                <label>Mensaje pie de tiquete (opcional)</label>
                                <input type="text" class="form-control" maxlength="200"
                                    id="mdl_sucursal_nota_pie" name="mdl_sucursal_nota_pie"
                                    placeholder="Ej: ¡Gracias por su preferencia!">
                            </div>
                        </div>

                        <!-- Impresora QZ Tray -->
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-print text-primary mr-1"></i>
                                    Nombre de impresora (impresión directa)
                                </label>
                                <input type="text" class="form-control" maxlength="200"
                                    id="mdl_sucursal_impresora" name="mdl_sucursal_impresora"
                                    placeholder="Ej: EPSON TM-T20III">
                                <small class="text-muted d-block mb-2">
                                    Deje en blanco para usar ventana emergente.
                                    Con impresora configurada, imprime silenciosamente vía <strong>QZ Tray</strong>.
                                </small>

                                <!-- Instrucciones QZ Tray -->
                                <div class="card border-0 mt-2" style="background:#f0f4ff; border-radius:10px; overflow:hidden;">
                                    <div class="card-header p-0" style="background:transparent; border:none;">
                                        <button class="btn btn-link btn-sm font-weight-bold px-3 py-2 w-100 text-left"
                                                type="button"
                                                data-toggle="collapse" data-target="#instruccionesQZ"
                                                style="color:#4e73df; text-decoration:none;">
                                            <i class="fas fa-question-circle mr-1"></i>
                                            ¿Cómo configurar la impresión directa?
                                            <i class="fas fa-chevron-down ml-1" style="font-size:.72rem;"></i>
                                        </button>
                                    </div>
                                    <div id="instruccionesQZ" class="collapse">
                                        <div class="card-body pt-0 pb-3 px-3" style="font-size:.84rem;">

                                            <p class="mb-3 text-muted border-top pt-2">
                                                Haga esto <strong>una sola vez</strong> en la computadora de caja:
                                            </p>

                                            <!-- Paso 1 -->
                                            <div class="d-flex mb-3">
                                                <span class="badge badge-primary mr-2 mt-1" style="height:20px;min-width:20px;line-height:14px;">1</span>
                                                <div>
                                                    <strong>Instalar QZ Tray</strong><br>
                                                    <span class="text-muted">
                                                        Descargue e instale desde <code style="font-size:.82rem;">qz.io/download</code>
                                                        (disponible para Windows, Mac y Linux).<br>
                                                        Ábralo — aparece como ícono
                                                        <i class="fas fa-circle text-success" style="font-size:.6rem;"></i>
                                                        en la barra del sistema (esquina inferior derecha en Windows).
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Paso 2 -->
                                            <div class="d-flex mb-3">
                                                <span class="badge badge-primary mr-2 mt-1" style="height:20px;min-width:20px;line-height:14px;">2</span>
                                                <div>
                                                    <strong>Obtener el nombre exacto de la impresora</strong><br>
                                                    <span class="text-muted">
                                                        <strong>Windows:</strong>
                                                        Panel de control → Dispositivos e impresoras → clic derecho en la impresora
                                                        → <em>Propiedades de impresora</em> → copie el nombre del título.<br>
                                                        <strong>Opción rápida:</strong>
                                                        Bloc de notas → Archivo → Imprimir → el nombre aparece en el desplegable.
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Paso 3 -->
                                            <div class="d-flex mb-3">
                                                <span class="badge badge-primary mr-2 mt-1" style="height:20px;min-width:20px;line-height:14px;">3</span>
                                                <div>
                                                    <strong>Pegue el nombre en el campo de arriba y guarde</strong><br>
                                                    <span class="text-muted">
                                                        El nombre debe ser <strong>idéntico</strong> al de Windows
                                                        (respeta mayúsculas, espacios y acentos).
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Paso 4 -->
                                            <div class="d-flex mb-3">
                                                <span class="badge badge-primary mr-2 mt-1" style="height:20px;min-width:20px;line-height:14px;">4</span>
                                                <div>
                                                    <strong>Primera impresión — autorizar el sitio</strong><br>
                                                    <span class="text-muted">
                                                        Al cobrar por primera vez, QZ Tray mostrará una ventana de seguridad.<br>
                                                        Seleccione <strong>"Allow"</strong> y marque
                                                        <em>"Remember this decision"</em>.<br>
                                                        A partir de ese momento todas las impresiones son silenciosas y automáticas.
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Nota importante -->
                                            <div class="alert alert-warning py-2 mb-0 d-flex align-items-start" style="font-size:.8rem; border-radius:8px;">
                                                <i class="fas fa-exclamation-triangle mr-2 mt-1 text-warning"></i>
                                                <span>
                                                    <strong>QZ Tray debe estar corriendo</strong> en la computadora de caja
                                                    cada vez que se use el sistema. Si no está activo, la impresión
                                                    vuelve al modo ventana emergente sin perder datos.
                                                </span>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div id='footerContiner' class="modal-footer" style="margin-top:-5%;">
                    <a href="#" class="btn btn-secondary" onclick="cerrarModalSucursal()">Volver</a>
                    <input type="submit" class="btn btn-primary" value="Guardar" />
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -- fin modal de agregar sucursal -->
@endsection



@section('script')
<script src="{{ asset('assets/bundles/sweetalert/sweetalert.min.js') }}"></script>

<script src="{{ asset('assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/bundles/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('assets/js/page/datatables.js') }}"></script>
<script src="{{ asset('assets/js/mant_sucursales.js') }}"></script>
@endsection