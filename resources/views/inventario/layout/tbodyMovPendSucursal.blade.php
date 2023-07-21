@foreach ($data['movimientos'] as $i)
    <tr class="space_row_table" style="cursor: pointer;" onclick='goMovimientoPendiente("{{ $i->id }}")'>
        <td class="text-center" title="{{ $i->codigo_movimiento ?? '' }}">
            {{ strtoupper($i->descripcion_movimiento ?? '') }}
        </td>
        <td class="text-center">
            {{ $i->fecha ?? '' }}
        </td>
        <td class="text-center">
            {{ $i->despacho ?? '' }}
        </td>
       
        <td class="text-center">
            {{ $i->nombre_usuario ?? '' }}
        </td>

    </tr>
@endforeach
