@foreach ($data['pagos'] as $p)
    <tr class="space_row_table" style="cursor:pointer;" 
        onclick='ticketePagoParcial("{{$p->id}}")'> 
        <td class="text-center">
            {{$p->fecha ?? ""}}
        </td>
        <td class="text-center">
            CRC {{number_format($p->totalPagado ?? '0.00',2,".",",")}} 
        </td>      
        <td class="text-center">
            {{$p->cobrador ?? ""}}
        </td>
    </tr>
@endforeach
