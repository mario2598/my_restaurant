@foreach ($data['gastosSinAprobar'] as $g)
  <div class="col-12 col-md-6 col-lg-6">

    <div class="card card-primary">
      <div class="card-header">
        <h4>CRC {{number_format($g->monto,2,".",",")}}  <small>- {{$g->nombre ?? ''}} </small></h4>
        <div class="card-header-action">
          @if ($g->caja_cerrada == 'N')
              <small>* Caja sin cerrar</small>
          @endif
          @if ($g->caja_cerrada == 'S')
            <a  onclick='confirmarGasto("{{$g->id}}",this,"{{number_format($g->monto,2,".",",")}}")' style="cursor: pointer; color:white;" class="btn btn-primary">Aceptar</a>
            <a href="#" class="btn btn-primary">Rechazar</a>
          @endif
        </div>
      </div>
      <div class="card-body">
        <p><strong>{{strtoupper($g->nombreUsuario ?? '')}} - {{$g->fecha ?? ''}}</strong> <br> 
          <small>{{($g->descripcion ?? '')}} </small><br>
          @if ($g->observacion != null && $g->observacion != "")
            <small><strong>Observación : </strong> {{$g->observacion ?? ''}} </small><br>
          @endif
        </p> 
        
      </div>
      
    </div>
  </div>
@endforeach
           