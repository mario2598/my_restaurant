@extends('layout.master-usuarioE')

@section('content')
    <style>
        .card-mario {
            border: 1px solid #ccc;
            /* Borde delgado de color gris */
            border-radius: 8px;
            /* Bordes poco redondeados */
            background-color: #fff;
            /* Color de fondo blanco */
            box-shadow: none;
            margin-right: 5px;
            /* Sin sombra */
            transition: transform 0.2s;
            /* Efecto de transformación suave al hacer hover */

            /* Estilo del texto */
            color: #333;
            font-size: 16px;
        }

        /* Cambia el estilo al hacer hover */
        .card-mario:hover {
            transform: scale(1.05);
            /* Escala ligeramente al hacer hover */
        }

        .imagen-cuadrada {
            width: 200px;
            /* Establece el ancho fijo para la imagen */
            height: 200px;
            /* Establece la altura fija para la imagen (mismo valor que el ancho) */
            object-fit: cover;
            /* Ajusta la imagen para que cubra completamente el cuadrado sin distorsión */
        }

        .lg-outer .lg-thumb-item {
            border: 0px solid #FFF !important;
        }


        .categories ul li a:hover {
            color: white;
            background: black;
        }

        .categories ul li.active a {
            color: white;
            background: black;
        }

        .categories ul {
            text-align: left;
        }
    </style>

    <div class="main-content" style="padding-left: 10px;padding-top: 10px;">
        <section class="section">
            <div class="row">
                <div class="col-md-2">
                    <a> <img title="Nombre empresa" alt="Nombre empresa"
                            src="{{ asset('assets/images/default-logo.png') }}"
                            style="background-color: transparent;border-color: transparent;" class="img-thumbnail" /> 
                    </a>
                    <div class="categories">
                        <ul id="categoriasNav" style="margin-bottom: 0px;margin-top: 5px;">
                        </ul>
                    </div>
                </div>
                <div class="col-md-10">
                    <div class="section-body" style="margin-top: 5px;">
                        <div class="row clearfix">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 col-12">
                                <div class="card">

                                    <div class="card-body">
                                        <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                                            @foreach ($data['categorias'] as $index => $cat)
                                                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"
                                                    onclick="seleccionarTipo({{ $index }})" style="padding: 10px;">
                                                    <div class="card-mario" style="padding: 10px;">
                                                        <img class="img-responsive thumbnail imagen-cuadrada"
                                                            src="{{ $cat->url_imagen }}" alt="{{ $cat->categoria }}">

                                                        <p style="text-align: center;"> <small>{{ $cat->categoria }}</small>
                                                            <br>

                                                        </p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </section>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/js/usuarioExterno/menu.js') }}"></script>
@endsection
