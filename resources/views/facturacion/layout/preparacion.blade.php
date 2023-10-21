@foreach ($data['ordenes'] as $p)
    @if (count($p->detalles) > 0)
    <div class="col-md-6 col-xs-12 col-sm-12 col-xl-4">
        <div class="card">
            <div class="card-header" style="padding: 5px !important;">
                <h4>
                    {{ $p->numero_orden }} : {{ $p->nombre_cliente }} 

                </h4>
                <div class="card-header-action">
                    <a class="btn btn-icon btn-success" style="cursor: pointer"
                        onclick='terminarPreparacion({{ "$p->id" }})' title="Teminar preparación orden"><i
                            class="fas fa-check"></i></a>
                    <a data-collapse="#mycard-collapse{{ $p->id }}" title="Esconder"
                        class="btn btn-icon btn-info" href="#"><i class="fas fa-minus"></i></a>
                </div>
            </div>
            <div class="collapse show" id="mycard-collapse{{ $p->id }}">
                <div class="card-body" style="padding: 5px !important;">
                    <div class="row">
                        <div class="col-12">
                           
                                <h6 style="cursor: pointer">
                                    Estado : {{ $p->descEstado ?? '' }} </h6>
                        </div>
                    </div>
                </div>
                <div class="card-footer" style="padding: 5px !important;">
                    <div class="table-responsive">
                        <h4>Detalle de orden</h4>
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Observación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($p->detalles as $d)
                                    <tr style="border-top:1px solid black;cursor: pointer;"
                                    onclick="mostrarReceta(`{{$d->receta}}`)">
                                        <td> {{ $d->nombre_producto ?? '' }}</td>
                                        <td>{{ $d->cantidad ?? '0' }} </td>
                                        <td>{{ $d->observacion ?? '' }}</td>
                                    </tr>
                                    @if($d->tieneExtras)
                                    <tr>
                                        <td>
                                        <table class="table table-hover mb-0" style="width: 100%" >
                                            <thead>
                                                <tr>
                                                    <th>Extras </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($d->extras as $e)
                                                    <tr>
                                                        <td> {{ $e->descripcion_extra ?? '' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table></td>
                                    </tr>

                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach
