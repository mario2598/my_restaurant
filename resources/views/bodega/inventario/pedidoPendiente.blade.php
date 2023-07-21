@extends('layout.master')

@section('content')

    @include('layout.sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">


                    <div class="col-sm-12 col-md-3">
                        <div class="form-group">
                            <label>Sucursal Solicitud</label>
                            <input type="text" class="form-control" value="{{ $data['pedido']->sucursalNombre ?? '' }}"
                                readonly>
                        </div>
                    </div>
                    <div class="col-12 col-lg-3">
                        <div class="form-group mb-0">
                            <label>Bodega Despacho</label>
                            <input type="text" class="form-control" value="{{ $data['pedido']->bodegaNombre ?? '' }}"
                                readonly>
                        </div>
                    </div>
                    @if ($data['pedido']->usuarioEmisor ?? null != null)
                        <div class="col-12 col-lg-3">
                            <div class="form-group mb-0">
                                <label>Solicitante</label>
                                <input type="text" class="form-control" value="{{ $data['pedido']->usuarioEmisor ?? '' }}"
                                    readonly>
                            </div>
                        </div>
                    @endif
                    <div class="col-12 col-lg-3">
                        <div class="form-group mb-0">
                            <label>Detalle de pedido</label>
                            <textarea class="form-control" readonly
                                maxlength="150">{{ $data['pedido']->detalle ?? '' }}</textarea>
                        </div>
                    </div>
                    @if ($data['pedido'] ?? null == null)

                        @if ($data['pedido']->estado == 'P')
                            <div class="col-12 col-md-4 col-lg-3">
                                <div class="form-group mb-0">
                                    <label>Procesar Pedido</label><br>
                                    <a onclick='procesarSucursalBodegaPedido("{{ $data['pedido']->id ?? -1 }}")'
                                        style="cursor: pointer; color:white;" class="btn btn-warning">Procesar</a>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="col-12 col-md-4 col-lg-3"></div>
                    @endif

                    <div class="col-12 col-md-12 col-lg-12" style="margin-top:30px;">

                        <div class="col-12 col-sm-12 col-lg-12">

                            <h5 style="font-size: 14px;">Detalle de pedido</h5>

                            <div class="table-responsive">
                                <table class="table table-striped" id="tablaDetalle">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-center"># Código</th>
                                            <th scope="col" class="text-center">Categoría</th>
                                            <th scope="col" class="text-center">Producto</th>
                                            <th scope="col" class="text-center">Cantidad</th>

                                        </tr>
                                    </thead>
                                    <tbody id="tbody_inventario">
                                        @foreach ($data['detalles_pedido'] ?? [] as $i)
                                            <tr>
                                                <td class="text-center">
                                                    {{ $i->codigo_barra }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->nombre_categoria }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $i->nombre }}
                                                </td>

                                                <td class="text-center">
                                                    {{ $i->cantidad }}
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


    <form id="formProcesarPedidoBodega" action="{{ URL::to('bodega/inventario/pedido/procesar') }}" style="display: none"
        method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="idPedidoBodega" id="idPedidoBodega" value="{{ $data['pedido']->id ?? -1 }}">
    </form>

@endsection
@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script>
        function procesarSucursalBodegaPedido(id) {
            if (id != "") {
                $('#idPedidoBodega').val(id);
                $('#formProcesarPedidoBodega').submit();
            }
        }

    </script>

<script>
    window.addEventListener("load", initialice, false);
    function initialice() {
      
      var sucursalNombre= "{{$data['pedido']->sucursalNombre ?? 'sin sucursal.'}}";
      var solicitante= "{{ $data['pedido']->usuarioEmisor ?? '' }}"
      var fecha= "{{ $data['pedido']->fecha ?? 'sin fecha' }}";

      var topMesage = 'Reporte de Pedido de '+sucursalNombre;
      var bottomMesage = 'Reporte de Pedido de '+sucursalNombre+". Solicitud realizada por "+solicitante+" el "+fecha;
    
      bottomMesage += '. Desarrollado por Space Software CR. ';
     
     
      $('#tablaDetalle').DataTable({
        dom: 'Bfrtip',
        "searching": false,
        "paging": false,
        'fixedHeader': {
    'header': true,
    'footer': true
  },
        buttons: [
          {
            extend: 'excel',
            footer: true,
            title: 'SPACE REST',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_pedido_el_amanecer'
          }, {
            extend: 'pdf',
            footer: true,
            title: 'SPACE REST',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_pedido_el_amanecer'
          }, {
            extend: 'print',
            footer: true,
            title: 'SPACE REST',
            messageTop:topMesage,
            messageBottom:bottomMesage,
            filename: 'reporte_pedido_el_amanecer'
          }
        ]
      });

    }
    </script>

@endsection
