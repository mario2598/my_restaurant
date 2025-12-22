@extends('layout.master-usuarioE')

@section('content')
    <style>
        /* Variables de colores */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --text-color: #333;
            --bg-light: #f8f9fa;
            --border-color: #e0e0e0;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 8px rgba(0,0,0,0.15);
            --shadow-lg: 0 8px 16px rgba(0,0,0,0.2);
        }

        /* Contenedor principal */
        .menu-externo-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 15px;
        }

        /* Logo y header */
        .menu-header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
        }

        .menu-header img {
            max-width: 150px;
            height: auto;
            border-radius: 8px;
        }

        /* Sidebar mejorado */
        .menu-sidebar {
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: var(--shadow-md);
            margin-bottom: 20px;
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }

        .menu-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .menu-sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .menu-sidebar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .menu-sidebar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Navegación de categorías */
        .categories {
            margin-top: 15px;
        }

        .categories ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .categories ul li {
            margin-bottom: 8px;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .categories ul li a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .categories ul li a i {
            margin-right: 10px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .categories ul li:hover a {
            background: var(--bg-light);
            color: var(--primary-color);
            border-left-color: var(--secondary-color);
            transform: translateX(5px);
        }

        .categories ul li.active a {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-left-color: var(--accent-color);
            box-shadow: var(--shadow-sm);
        }

        /* Tarjetas de productos/categorías */
        .card-mario {
            border: none;
            border-radius: 16px;
            background-color: #fff;
            box-shadow: var(--shadow-sm);
            margin-bottom: 15px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .card-mario:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-lg);
        }

        .card-mario:active {
            transform: translateY(-4px) scale(1.01);
        }

        .card-mario .card-image-wrapper {
            position: relative;
            width: 100%;
            padding-top: 100%; /* Aspect ratio 1:1 */
            overflow: hidden;
            background: var(--bg-light);
        }

        .imagen-cuadrada {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .card-mario:hover .imagen-cuadrada {
            transform: scale(1.1);
        }

        .card-mario .card-content {
            padding: 15px;
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .card-mario .card-content p {
            margin: 0;
            color: var(--text-color);
        }

        .card-mario .card-content small {
            font-size: 14px;
            font-weight: 600;
            color: var(--primary-color);
            display: block;
            margin-bottom: 5px;
        }

        .card-mario .card-content strong {
            font-size: 18px;
            color: var(--accent-color);
            font-weight: 700;
        }

        /* Contenedor de productos */
        .menu-content {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow-md);
            min-height: 400px;
        }

        #aniimated-thumbnials {
            margin: 0;
        }

        /* LightGallery personalizado */
        .lg-outer .lg-thumb-item {
            border: 0px solid #FFF !important;
        }

        /* Responsive para móviles */
        @media (max-width: 768px) {
            .menu-externo-container {
                padding: 10px;
            }

            .menu-sidebar {
                position: relative;
                top: 0;
                max-height: none;
                margin-bottom: 15px;
            }

            .menu-header {
                padding: 10px;
                margin-bottom: 15px;
            }

            .menu-header img {
                max-width: 120px;
            }

            .menu-content {
                padding: 15px;
            }

            .card-mario .card-content {
                padding: 12px;
            }

            .card-mario .card-content small {
                font-size: 12px;
            }

            .card-mario .card-content strong {
                font-size: 16px;
            }

            .categories ul li a {
                padding: 10px 12px;
                font-size: 14px;
            }
        }

        /* Tablet optimizations */
        @media (min-width: 769px) and (max-width: 1024px) {
            .card-mario .card-content small {
                font-size: 13px;
            }

            .card-mario .card-content strong {
                font-size: 17px;
            }
        }

        /* Animación de carga */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-mario {
            animation: fadeIn 0.5s ease-out;
        }

        /* Mejora para touch devices */
        @media (hover: none) and (pointer: coarse) {
            .card-mario:hover {
                transform: none;
            }

            .card-mario:active {
                transform: scale(0.98);
                box-shadow: var(--shadow-sm);
            }
        }

        /* Grid responsive mejorado */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        @media (max-width: 576px) {
            .menu-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 10px;
            }
        }
    </style>

    <div class="menu-externo-container">
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar de categorías -->
                <div class="col-lg-3 col-md-4 col-12">
                    <div class="menu-header d-md-none">
                        <img title="Nombre empresa" alt="Nombre empresa"
                             src="{{ asset('assets/images/default-logo.png') }}"
                             class="img-fluid" />
                    </div>
                    <div class="menu-sidebar">
                        <div class="d-none d-md-block mb-3">
                            <img title="Nombre empresa" alt="Nombre empresa"
                                 src="{{ asset('assets/images/default-logo.png') }}"
                                 class="img-fluid" style="max-width: 100%; border-radius: 8px;" />
                        </div>
                        <div class="categories">
                            <ul id="categoriasNav"></ul>
                        </div>
                    </div>
                </div>

                <!-- Contenido principal -->
                <div class="col-lg-9 col-md-8 col-12">
                    <div class="menu-content">
                        <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                            @foreach ($data['categorias'] as $index => $cat)
                                <div class="col-lg-3 col-md-4 col-sm-6 col-6"
                                     onclick="seleccionarTipo({{ $index }})">
                                    <div class="card-mario">
                                        <div class="card-image-wrapper">
                                            <img class="img-responsive thumbnail imagen-cuadrada"
                                                 src="{{ $cat->url_imagen }}" 
                                                 alt="{{ $cat->categoria }}"
                                                 loading="lazy">
                                        </div>
                                        <div class="card-content">
                                            <p>
                                                <small>{{ $cat->categoria }}</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Variable global para la imagen por defecto
        var DEFAULT_IMAGE_URL = '{{ asset("assets/images/default-logo.png") }}';
    </script>
    <script src="{{ asset('assets/js/usuarioExterno/menu.js') }}"></script>
@endsection
