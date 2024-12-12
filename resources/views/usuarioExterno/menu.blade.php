@extends('layout.master-usuarioE')

@section('content')
    <style>
        /* Estilos generales */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f9f9f9;
        }

        .main-content {
            padding: 20px;
        }

        /* Estilos de tarjetas */
        .card-mario {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            color: #333;
            font-size: 16px;
            cursor: pointer;
            overflow: hidden;
        }

        .card-mario:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        .imagen-cuadrada {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid #ddd;
        }

        .card-mario p {
            margin: 10px 0;
            text-align: center;
            font-weight: 500;
            font-size: 14px;
            color: #555;
        }

        .categories ul {
            list-style: none;
            padding: 0;
        }

        .categories ul li a {
            display: block;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .categories ul li a:hover,
        .categories ul li.active a {
            color: #fff;
            background: #007bff;
        }

        /* Diseño adaptable */
        @media screen and (max-width: 768px) {
            .imagen-cuadrada {
                height: 150px;
            }
        }

        @media screen and (min-width: 1024px) {
            .card-mario {
                margin: 15px;
            }
        }
    </style>

    <div class="main-content">
        <section class="section">
            <div class="row">
                <!-- Columna lateral -->
                <div class="col-md-2">
                    <a>
                        <img title="Nombre empresa" alt="Nombre empresa"
                            src="{{ asset('assets/images/default-logo.png') }}"
                            class="img-thumbnail" style="background-color: transparent; border: none;">
                    </a>
                    <div class="categories mt-3">
                        <ul id="categoriasNav">
                            <li class="active"><a href="#">Categoría 1</a></li>
                            <li><a href="#">Categoría 2</a></li>
                            <li><a href="#">Categoría 3</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Columna principal -->
                <div class="col-md-10">
                    <div class="section-body">
                        <div class="row clearfix">
                            @foreach ($data['categorias'] as $index => $cat)
                                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                    <div class="card-mario" onclick="seleccionarTipo({{ $index }})">
                                        <img class="imagen-cuadrada" src="{{ $cat->url_imagen }}"
                                            alt="{{ $cat->categoria }}">
                                        <p>{{ $cat->categoria }}</p>
                                    </div>
                                </div>
                            @endforeach
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
