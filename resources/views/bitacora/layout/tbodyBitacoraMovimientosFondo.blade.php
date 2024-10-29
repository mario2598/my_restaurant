
@foreach ($data['movimientos'] as $i)
<tr class="space_row_table" style="cursor: pointer;" 
  @if ($i->tabla == 'ingreso')
    onclick='clickIngreso("{{$i->id_entidad}}")'
  @endif
  @if ($i->tabla == 'gasto')
    onclick='clickGasto("{{$i->id_entidad}}")'
  @endif
>
  <td class="text-center">
    {{strtoupper($i->tabla ?? '')}}
  </td>
  <td class="text-center">
    {{strtoupper($i->usuario ?? '')}}
  </td>
  <td class="text-center">
    {{$i->fecha ?? ''}}
  </td>
  <td class="text-center">
    {{$i->tipo ?? ''}}
  </td>
  <td class="text-center">
    CRC {{number_format($i->total ?? '0.00',2,".",",")}}
  </td>
  <td class="text-center">
    {{$i->sucDes ?? ''}}
  </td>
</tr>
@endforeach
                  