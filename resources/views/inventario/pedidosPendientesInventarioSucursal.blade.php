@extends('layout.master')

@section('style')


@endsection


@section('content')

    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Pedidos Pendientes</h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" name="" id="buscar_pedido" class="form-control"
                                    placeholder="Buscar pedido">
                                <div class="input-group-btn">
                                    <a class="btn btn-primary btn-icon"><i class="fas fa-search"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="tablaPedidos">
                                <thead>
                                    <tr>

                                        <th class="text-center"># Pedido</th>
                                        <th class="text-center">
                                            Fecha
                                        </th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>

                                    </tr>
                                </thead>
                                <tbody id="tbody_generico">

                                    @foreach ($data['pedidos_pendientes'] as $g)
                                        <tr class="space_row_table" style="cursor: pointer;">

                                            <td class="text-center">
                                                {{ $g->id }}
                                            </td>
                                            <td class="text-center">
                                                {{ $g->fecha ?? '' }}
                                            </td>
                                            <!--  Estados P = Pendiente, C = Cancelado, T = Terminado , A = aceptado, E = eliminado-->
                                            <td class="text-center">

                                                @if ($g->estado == 'P')
                                                    <div class="badge badge-warning badge-shadow">
                                                        Pendiente Aprobar</div>
                                                @endif
                                                @if ($g->estado == 'A')
                                                    <div class="badge badge-success badge-shadow">
                                                        Aprobado</div>
                                                @endif
                                               
                                            </td>
                                            <td class="text-center">
                                                @if ($g->estado == 'P')
                                                    <button  class="btn btn-icon btn-danger" onclick='eliminarSucursalInventarioPedido("{{$g->id}}")'
                                                        style="color: blanchedalmond"><i class="fas fa-trash"></i></button>
                                                @endif
                                                <button  class="btn btn-icon btn-success" onclick='verSucursalInventarioPedido("{{$g->id}}")'
                                                    style="color: blanchedalmond"><i class="fas fa-eye"></i></button>
                                            </td>
                                        </tr>

                                    @endforeach


                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </section>

    </div>
    <form id="formEliminarPedido" action="{{URL::to('inventario/sucursal/pedidos/pendientes/eliminar')}}" style="display: none"  method="POST">
        {{csrf_field()}}
        <input type="hidden" name="idPedido" id="idPedido" value="-1">
      </form>

      <form id="formGoEditarPedido" action="{{URL::to('inventario/sucursal/pedidos/pedido')}}" style="display: none"  method="POST">
        {{csrf_field()}}
        <input type="hidden" name="idPedidoEditar" id="idPedidoEditar" value="-1">
      </form>

@endsection



@section('script')

    <script src="{{ asset('assets/js/inventario/sucursal/pedidos_pendientes.js') }}"></script>



@endsection
