@extends('layout.master')

@section('content')  

@include('layout.sidebar')

<!-- Main Content -->
<div class="main-content">
  <section class="section">
    <div class="section-body">
      <div class="row">
        <div class="col-12 col-md-6 col-sm-12">
          <div class="card">
            <div class="card-header">
              <h4>Detalle de Lote</h4>
            </div>
            <div class="card-body">
              <div class="empty-state" data-height="350">
                <div class="empty-state-icon">
                  <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h2>Registrar un gasto</h2>
                <p class="lead">
                  Código del Lote Generado. <br> {{$data['lote']->codigo ?? 'Algo salio mal'}}
                </p>
                <a href="{{url('gastos/nuevo')}}" class="btn btn-primary mt-4">Registrar gasto</a>
              
              </div>
            </div>
          </div>
        </div>
        
    </div>
  </section>

  @endsection