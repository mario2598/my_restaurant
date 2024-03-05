@extends('layout.master')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
@endsection


@section('content')
    @include('layout.sidebar')



    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Ordenes con Entrega </h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" id="input_buscar_generico" class="form-control"
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
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Estado</label>
                                    <select class="form-control" id="select_estado" name="select_estado">
                                        <option value="T" selected>Todos</option>
                                        @foreach ($data['estadosOrden'] as $i)
                                            <option value="{{ $i->id ?? '' }}" title="{{ $i->nombre ?? '' }}">
                                                {{ $i->nombre ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <input type="date" class="form-control" id="desde" value="" />

                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <input type="date" class="form-control" id="hasta" value="" />
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-2">
                                <div class="form-group">
                                    <label>Buscar</label>
                                    <button onclick="filtrar()" class="btn btn-primary btn-icon form-control"
                                        style="cursor: pointer;"><i class="fas fa-search"></i></button>
                                </div>
                            </div>

                        </div>
                        <div id="contenedor_gastos" class="row">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tbl-ordenes">
                                    <thead class="thead-light">
                                        <tr>
                                            <th scope="col" style="text-align: center">No.Orden</th>
                                            <th scope="col" style="text-align: center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-ordenes">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <a href="" target='_blank' class="btn btn-primary" id='btn-pdf' style="display:none"></a>
    </div>

    <div class="modal fade bd-example-modal-lg" id='mdl-entrega' tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered  modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="width: 100%">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="width: 100%">
                    <div class="row">
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label for="nOrden">Número Orden :</label>
                                    <output id="nOrden" >
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <output id="nEstado">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Cliente :</label>
                           
                                        <output 
                                        id="ocliente" name="ocliente">
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Contacto de entrega :</label>
                                    
                                    <output
                                        id="ipt_contacto_entrega" name="mdl_contacto_entrega">
                                       
                                </div>
                               <a id="msjWhatsapp"  target="_blank">
                                 <i class="fab fa-whatsapp" aria-hidden="true"> </i>Contactar por whatsapp
                                </a>
                            </div>
                        </div>

                        <div class="col-xl-12 col-sm-12">
                            <div class="form-group form-float">
                                <div class="form-line">
                                    <label class="form-label">Descripción Lugar Entrega :</label>
                                    <output  
                                    id="ipt_lugar_entrega" name="mdl_lugar_entrega">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-group" id="botonesEntregaContainer">

                      
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('script')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/entregas/ordenEntrega.js') }}"></script>
@endsection
