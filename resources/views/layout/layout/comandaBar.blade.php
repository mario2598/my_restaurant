@foreach ($data['ordenes'] as $o)
    <div class="p-15 border-bottom">
        <div class="col-12">
            <div class="card">
                <div class="card-body card-type-3">
                    <div class="row">
                        <div class="col">

                            <span class="font-weight-bold mb-0">Mesa No.{{ $o->numero_mesa ?? '##' }}</span><br>
                            <span class="font-weight-bold mb-0">ORD-{{ $o->numero_orden ?? '##' }}</span><br>
                            <span class="text-nowrap">{{ $o->nombre_cliente ?? 'Cliente' }}</span><br>
                            <span class="text-nowrap">
                                @switch($o->estado)
                                    @case("LF")
                                    Listo para facturar
                                    @break
                                    @case("EP")
                                    En preparación
                                    @break
                                    @case("PT")
                                    En espera de entregar
                                    @break
                                    @default

                                @endswitch</span><br>
                            <span class="font-weight-bold mb-0">CRC {{ number_format($o->total ?? 0, 2, '.', ',') }}</span>
                        </div>
                    </div>
                        <p class="mt-3 mb-0 text-muted text-sm">
                            <a style="cursor: pointer" onclick='goFacturaOrden("{{ $o->id }}")' class="btn btn-icon icon-left btn-restore-theme">
                                <i class="fas fa-payment"></i> Detalles..
                            </a>
                        </p>
                </div>
            </div>
        </div>
    </div>
@endforeach
<div class="p-15 border-bottom">
    <div class="col-12">
        <div class="card">
        </div>
    </div>
</div>
