@extends('layout.master')
@section('style')
@endsection


@section('content')
    @include('layout.sidebar')
    <script>
        var idComanda = "{{ $data['idComanda'] ?? '' }}";
    </script>

    <style>
        .table td,
        .table th {
            height: 32px !important;
        }
    </style>

    <div class="main-content">
        <section class="section">
            <div class="row" id="contenedor_comandas">
            </div>
        </section>
        <a href="" target='_blank' class="btn btn-primary" id='btn-pdf' style="display:none"></a>
    </div>
    <!-- Modal para mostrar receta -->
    <!-- Modal para mostrar receta mejorado -->
    <div class="modal fade" id="mdl_mostrar_receta" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
        data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nombreProductoAux"></h5>
                    <button type="button" class="close" onclick="ocultarReceta()" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="recetaTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="receta-tab" data-toggle="tab" href="#recetaContent"
                                role="tab" aria-controls="recetaContent" aria-selected="true">Receta</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="composicion-tab" data-toggle="tab" href="#composicionContent"
                                role="tab" aria-controls="composicionContent" aria-selected="false">Composición</a>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="recetaTabContent">
                        <!-- Contenido de la pestaña Receta -->
                        <div class="tab-pane fade show active" id="recetaContent" role="tabpanel"
                            aria-labelledby="receta-tab">
                            <ul id="listaReceta" class="list-group"></ul>
                        </div>
                        <!-- Contenido de la pestaña Composición -->
                        <div class="tab-pane fade" id="composicionContent" role="tabpanel"
                            aria-labelledby="composicion-tab">
                            <ul id="listaComposicion" class="list-group"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="ocultarReceta()">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection



@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/comandas/preparacion/comandasGen.js') }}"></script>
@endsection
