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

        /* Estilos para la sección de mesas disponibles */
        .mesas-disponibles-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid var(--border-color);
        }

        .mesas-disponibles-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            cursor: pointer;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .mesas-disponibles-header:hover {
            background: var(--bg-light);
        }

        .mesas-disponibles-header h6 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
        }

        .mesas-disponibles-header h6 i {
            margin-right: 8px;
            color: var(--secondary-color);
            font-size: 18px;
        }

        .mesas-disponibles-toggle {
            background: none;
            border: none;
            color: var(--secondary-color);
            font-size: 14px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .mesas-disponibles-toggle.rotated {
            transform: rotate(180deg);
        }

        .mesas-disponibles-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .mesas-disponibles-content.expanded {
            max-height: 500px;
            overflow-y: auto;
        }

        .mesas-disponibles-content::-webkit-scrollbar {
            width: 4px;
        }

        .mesas-disponibles-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .mesas-disponibles-content::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .mesas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 10px;
            padding: 5px;
        }

        .mesa-disponible-card {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 2px solid #28a745;
            border-radius: 12px;
            padding: 12px 8px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: default;
            position: relative;
            overflow: hidden;
        }

        .mesa-disponible-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .mesa-disponible-card:hover::before {
            opacity: 1;
        }

        .mesa-disponible-card:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
            border-color: #218838;
        }

        .mesa-disponible-card .mesa-icon {
            font-size: 24px;
            color: #28a745;
            margin-bottom: 8px;
            display: block;
        }

        .mesa-disponible-card .mesa-numero {
            font-size: 16px;
            font-weight: 700;
            color: #155724;
            margin-bottom: 4px;
        }

        .mesa-disponible-card .mesa-capacidad {
            font-size: 11px;
            color: #6c757d;
            margin: 0;
        }

        .mesas-empty-state {
            text-align: center;
            padding: 30px 15px;
            color: #6c757d;
        }

        .mesas-empty-state i {
            font-size: 48px;
            margin-bottom: 10px;
            opacity: 0.5;
            color: #adb5bd;
        }

        .mesas-empty-state p {
            margin: 0;
            font-size: 14px;
        }

        .mesas-loading {
            text-align: center;
            padding: 20px;
        }

        .mesas-loading .spinner-border {
            width: 2rem;
            height: 2rem;
            border-width: 0.2em;
            color: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .mesas-grid {
                grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
                gap: 8px;
            }

            .mesa-disponible-card {
                padding: 10px 6px;
            }

            .mesa-disponible-card .mesa-icon {
                font-size: 20px;
                margin-bottom: 6px;
            }

            .mesa-disponible-card .mesa-numero {
                font-size: 14px;
            }

            .mesa-disponible-card .mesa-capacidad {
                font-size: 10px;
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
                        
                        <!-- Sección de Mesas Disponibles -->
                        <div class="mesas-disponibles-section">
                            <div class="mesas-disponibles-header" onclick="toggleMesasDisponibles()">
                                <h6>
                                    <i class="fas fa-table"></i>
                                    Mesas Disponibles
                                </h6>
                                <button type="button" class="mesas-disponibles-toggle" id="toggle-mesas-btn">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="mesas-disponibles-content" id="mesas-disponibles-content">
                                <div class="mesas-loading">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Cargando...</span>
                                    </div>
                                </div>
                            </div>
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
        var base_path = '{{ url("/") }}';
        var CSRF_TOKEN = '{{ csrf_token() }}';
    </script>
    <script src="{{ asset('assets/js/usuarioExterno/menu.js') }}"></script>
    <script>
        // Funciones para gestionar las mesas disponibles
        var mesasDisponiblesExpandido = false;

        function toggleMesasDisponibles() {
            const content = document.getElementById('mesas-disponibles-content');
            const toggleBtn = document.getElementById('toggle-mesas-btn');
            
            if (!content || !toggleBtn) return;
            
            mesasDisponiblesExpandido = !mesasDisponiblesExpandido;
            
            if (mesasDisponiblesExpandido) {
                content.classList.add('expanded');
                toggleBtn.classList.add('rotated');
                cargarMesasDisponibles();
            } else {
                content.classList.remove('expanded');
                toggleBtn.classList.remove('rotated');
            }
        }

        function cargarMesasDisponibles() {
            const contentDiv = document.getElementById('mesas-disponibles-content');
            if (!contentDiv) return;
            
            contentDiv.innerHTML = '<div class="mesas-loading"><div style="width: 2rem; height: 2rem; border: 0.2em solid #3498db; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; margin: 0 auto;"></div><p style="text-align: center; margin-top: 10px; color: #6c757d;">Cargando...</p></div>';

            if (typeof $ !== 'undefined' && $.ajax) {
                $.ajax({
                    url: base_path + '/usuarioExterno/menu/mesas-disponibles',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: CSRF_TOKEN
                    },
                    success: function(response) {
                        if (response && response.estado && response.datos) {
                            mostrarMesasDisponibles(response.datos);
                        } else {
                            contentDiv.innerHTML = '<div class="mesas-empty-state"><i class="fas fa-table"></i><p>No hay mesas disponibles en este momento</p></div>';
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        contentDiv.innerHTML = '<div class="mesas-empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error al cargar las mesas</p></div>';
                    }
                });
            } else {
                // Fallback usando fetch si jQuery no está disponible
                fetch(base_path + '/usuarioExterno/menu/mesas-disponibles', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.estado && data.datos) {
                        mostrarMesasDisponibles(data.datos);
                    } else {
                        contentDiv.innerHTML = '<div class="mesas-empty-state"><i class="fas fa-table"></i><p>No hay mesas disponibles en este momento</p></div>';
                    }
                })
                .catch(error => {
                    contentDiv.innerHTML = '<div class="mesas-empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error al cargar las mesas</p></div>';
                });
            }
        }

        function mostrarMesasDisponibles(mesas) {
            const contentDiv = document.getElementById('mesas-disponibles-content');
            if (!contentDiv) return;
            
            if (!mesas || mesas.length === 0) {
                contentDiv.innerHTML = '<div class="mesas-empty-state"><i class="fas fa-table"></i><p>No hay mesas disponibles en este momento</p></div>';
                return;
            }

            let html = '<div class="mesas-grid">';
            mesas.forEach(function(mesa) {
                const numeroMesa = mesa.numero_mesa || 'N/A';
                const capacidad = mesa.capacidad || 'N/A';
                html += '<div class="mesa-disponible-card" title="Mesa ' + numeroMesa + ' - Capacidad: ' + capacidad + ' personas">';
                html += '<i class="fas fa-table mesa-icon"></i>';
                html += '<div class="mesa-numero">' + numeroMesa + '</div>';
                html += '<p class="mesa-capacidad"><i class="fas fa-users"></i> ' + capacidad + '</p>';
                html += '</div>';
            });
            html += '</div>';
            
            contentDiv.innerHTML = html;
        }

        // Animación para el spinner
        if (document.styleSheets) {
            var style = document.createElement('style');
            style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
            document.head.appendChild(style);
        }
    </script>
@endsection
