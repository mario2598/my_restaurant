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
    </style>

    <div class="main-content" style="padding-left: 30px;">
        <section class="section">
            <div class="section-body">
                <div class="row clearfix">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Light Gallery</h4>
                            </div>
                            <div class="card-body">
                                <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                                    @foreach ($data['prodcutosMenu'] as $p)
                                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12" style="padding: 10px;">
                                            <div class="card-mario" style="padding: 10px;">
                                                <a href="{{ asset('storage/'.$p->url_imagen) }}"
                                                    data-sub-html="{{$p->nombre . ' | CRC ' . number_format($p->precio, 2, ".", ",") . ' | '.$p->descripcion }} ">
                                                    <img class="img-responsive thumbnail imagen-cuadrada"
                                                        src="{{ asset('storage/'.$p->url_imagen) }}"
                                                        alt="{{$p->descripcion}}">
                                                </a>
                                                <p> <small>{{$p->nombre}}</small> <br>
                                                    <strong>{{'CRC ' . number_format($p->precio, 2, ".", ",") }}</strong>
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
        </section>
    </div>
@endsection
