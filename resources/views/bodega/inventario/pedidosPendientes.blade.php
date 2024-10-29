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
                                        <th class="text-center">Solicitante</th>
                                        <th class="text-center">Sucursal</th>
                                        <th class="text-center">Fecha</th>

                                    </tr>
                                </thead>
                                <tbody id="tbody_generico">
                                    @foreach ($data['pedidos_pendientes'] as $g)
                                        <tr class="space_row_table" style="cursor: pointer;"
                                            onclick='verSucursalBodegaPedido("{{ $g->id }}")'>

                                            <td class="text-center">
                                                {{ $g->id }}
                                            </td>
                                            <td class="text-center">
                                                {{ $g->emisorNombre ?? '' }}
                                            </td>
                                            <td class="text-center">
                                                {{ $g->sucursalNombre ?? '' }}
                                            </td>
                                            <td class="text-center">
                                                {{ $g->fecha ?? '' }}
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

    <form id="formGoPedidoBodega" action="{{ URL::to('bodega/inventario/pedido') }}" style="display: none" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="idPedidoBodega" id="idPedidoBodega" value="-1">
    </form>

@endsection


@section('script')

    <script>
        function verSucursalBodegaPedido(id) {
            if (id != "") {
                $('#idPedidoBodega').val(id);
                $('#formGoPedidoBodega').submit();
            }
        }

    </script>



@endsection
