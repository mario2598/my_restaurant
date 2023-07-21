@foreach ($data['pedidos_pendientes'] as $p)
    @if (count($p->detalles) > 0)
        <div class="col-md-6 col-xs-12 col-sm-12 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h4>
                        @if ($p->tipo == 'CA' || $p->tipo == 'M')
                            Mesa No.{{ $p->numero_mesa }}
                        @else
                            Orden No.{{ $p->numero_orden }}
                        @endif
                    </h4>
                    <div class="card-header-action">
                        <a class="btn btn-icon btn-success" style="cursor: pointer"
                        onclick='terminarOrdenComida("{{ $p->id }}","{{ $p->fecha_creacion_detalle }}")' title="Terminar orden"><i
                                class="fas fa-check"></i></a>
                        <a data-collapse="#mycard-collapse{{ $p->id }}" title="Esconder"
                            class="btn btn-icon btn-info" href="#"><i class="fas fa-minus"></i></a>
                    </div>
                </div>
                <div class="collapse show" id="mycard-collapse{{ $p->id }}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <h6>Hora : {{ $p->fecha_inicio_hora_tiempo }}</h6><br>

                            </div>
                            @if ($p->tipo == 'CA' || $p->tipo == 'M')
                                <div class="col-12">
                                    <h6 style="cursor: pointer">
                                        Orden No.{{ $p->numero_orden }}</h6><br>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer">
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
                                        @if ($d->cantidad - $d->cantidad_preparada > 0)
                                            <tr>
                                                <td><i class="{{ $d->servicio_mesa == 'S' ? 'fas fa-utensils text-secondary' : 'fas fa-box text-secondary' }}"
                                                        aria-hidden="true"
                                                        style="{{ $d->servicio_mesa == 'S' ? 'color:grey !important;' : 'color:red !important;' }}"></i>
                                                    - {{ $d->nombre_producto ?? '' }}</td>
                                                <td>{{ $d->cantidad - $d->cantidad_preparada }} </td>
                                                <td>{{ $d->observacion ?? '' }}</td>
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
