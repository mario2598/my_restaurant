@foreach ($data['inventario'] as $i)
<tr style="cursor: pointer;" onclick="seleccionarProductoInventario('{{$i->codigo_barra}}','{{$i->nombre}}','{{$i->cantidad}}')">
    <td scope="row" class="text-center">
        {{ strtoupper($i->codigo_barra ?? '') }}
    </td>
    <td class="text-center">
        {{ $i->nombre ?? '' }}
    </td>
    <td class="text-center">
        {{ $i->cantidad ?? '' }}
    </td>
</tr>
@endforeach