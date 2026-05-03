@extends('layout.master')

@section('style')
@endsection


@section('content')

<style>


  .trIngreso :hover {
     
      font-weight: bold;
  }
</style>
    @include('layout.sidebar')

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card card-warning">
                    <div class="card-header">
                        <h4>Ingresos Pendientes de aprobar</h4>

                    </div>
                    <div class="card-body">
                        <div id="contenedor_ingresos_sin_aprobar" class="row">
                            <table class="table" id="tbl-ordenes" style="max-height: 100%;">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col" style="text-align: center">Tipo Ingreso</th>
                                        <th scope="col" style="text-align: center">Usuario</th>
                                        <th scope="col" style="text-align: center">Fecha</th>
                                        <th scope="col" style="text-align: center">Descripción</th>
                                        <th scope="col" style="text-align: center">Pagos / Monedas</th>
                                        <th scope="col" style="text-align: center">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-ordenes" class="trIngreso">


                                    @foreach ($data['ingresosSinAprobar'] as $g)
                                        <tr onclick='clickIngreso("{{ $g->id }}")' style="cursor: pointer" >
                                            <td>
                                                {{ $g->tipoIngreso ?? '' }}
                                            </td>
                                            <td>
                                                {{ $g->nombreUsuario ?? '' }}
                                            </td>
                                            <td>
                                                {{ $g->fecha ?? '' }}
                                            </td>
                                            <td>
                                                {{ $g->descripcion ?? '' }}
                                            </td>
                                            <td style="min-width: 290px;">
                                                @if (!empty($g->tiene_detalle_multimoneda))
                                                    <span class="badge badge-info mb-1">Multimoneda</span>
                                                    @if (!empty($g->detalle_pagos_resumen))
                                                        @foreach ($g->detalle_pagos_resumen as $lineaPago)
                                                            <div style="font-size: 12px; line-height: 1.3;">{{ $lineaPago }}</div>
                                                        @endforeach
                                                    @endif
                                                @else
                                                    <span class="text-muted">Sin desglose por moneda (modo clásico)</span>
                                                @endif
                                            </td>
                                            <td>
                                                CRC {{ number_format($g->total ?? '0.00', 2, '.', ',') }}
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
@endsection



@section('script')
    <script src="{{ asset('assets/js/ingresos_pendientes.js') }}"></script>
@endsection
