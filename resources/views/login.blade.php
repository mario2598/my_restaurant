@extends('layout.master-login')

@section('content')
    <section class="section">
        <div class="container mt-3">
            <div class="row">
                <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-8 offset-lg-2 col-xl-8 offset-xl-2">
                    <div class="card card-primary">
                        <div class="card-header" style="display: block;"> 
                            <div class="account-logo" style="text-align: center;">
                                <a href="#">
                                    <img src="{{ asset('assets/images/default-logo.png') }}"
                                        style="background-color: transparent;border-color: transparent; max-height: 180px;"
                                        class="img-thumbnail" title="{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}"
                                        alt="{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}">
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ URL::to('login') }}" class="needs-validation" autocomplete="off">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label for="email">Usuario</label>
                                    <input type="text" class="form-control" name="user" tabindex="1" required autofocus
                                        maxlength="25">
                                    <div class="invalid-feedback">
                                        * Ingresa un usuario valido
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="d-block">
                                        <label for="password" class="control-label">Contraseña</label>
                                    </div>
                                    <input id="password" type="password" class="form-control" name="password" tabindex="2"
                                        required minlength="4" maxlength="25">
                                    <div class="invalid-feedback">
                                        * Ingresa la contraseña
                                    </div>
                                </div>
    
                                <div class="form-group">
                                    <input type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4"
                                        value="Ingresar">
                                </div>

                                <div class="mt-5 text-muted text-center">
                                    © {{ date('Y') }} | {{ config('app.name', 'SPACE SOFTWARE CR') }}
                                </div>
                            </form>
                        </div>
           
                    </div>
                </div>
            </div>
        </div>
    </section>

    </section>
   
@endsection
