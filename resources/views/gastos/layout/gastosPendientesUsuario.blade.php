@foreach ($data['gastosSinAprobar'] as $g)
                <div class="col-12 col-md-6 col-lg-6">

                  <div class="card card-primary">
                    <div class="card-header">
                      <h4>CRC {{number_format($g->monto,2,".",",")}}  <small>- {{$g->nombre ?? ''}} </small></h4>
                      <div class="card-header-action">
                        @if ($g->caja_cerrada == 'N')
                          <a  onclick="editarGastoUsuario('{{$g->id}}')" style="cursor: pointer; color:white;" class="btn btn-primary">Editar</a>
                          <a  onclick="eliminarGastoUsuario('{{$g->id}}')" class="btn btn-primary">Eliminar</a>
                        @endif
                       
                      </div>
                    </div>
                    <div class="card-body">
                      <p><strong> {{$g->fecha ?? ''}}</strong> <br> 
                        <small>{{($g->descripcion ?? '')}} </small><br>
                        @if ($g->observacion != null && $g->observacion != "")
                          <small><strong>Observación : </strong> {{$g->observacion ?? ''}} </small><br>
                        @endif
                      </p> 
                      
                    </div>
                    
                  </div>
                </div>
              @endforeach