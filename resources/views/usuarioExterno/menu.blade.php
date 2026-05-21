@extends('layout.master-usuarioE')

@section('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Fraunces:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/menu-digital.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/menu-digital-responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/mesa-plano-visual.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/menu-plano-publico.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body.menu-digital-page .loader,
        body.menu-digital-page .main-footer { display: none !important; }
        body.menu-digital-page .main-wrapper { padding: 0 !important; min-height: 100vh; }
        body.menu-digital-page #app { min-height: 100vh; }
        body.menu-digital-page .bg-fill { display: none; }
        body.menu-digital-page {
            background: linear-gradient(165deg, #5fa688 0%, #3d8f72 45%, #247d6f 100%);
        }
        body.menu-digital-page .brand-center__rose {
            background-image: none;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(238, 230, 200, 0.22) 0%, transparent 72%);
        }
    </style>
@endsection

@section('content')
    <script>document.body.classList.add('menu-digital-page');</script>

    @if(!empty($mostrarSelectorSucursal))
    <div id="menu-sucursal-overlay" class="menu-sucursal-overlay{{ empty($idSucursalMenu) ? ' is-visible' : '' }}" aria-hidden="{{ empty($idSucursalMenu) ? 'false' : 'true' }}">
        <div class="menu-sucursal-picker" role="dialog" aria-labelledby="menu-sucursal-title">
            <p class="menu-sucursal-picker__eyebrow">{{ $nombreNegocio }}</p>
            <h2 id="menu-sucursal-title">¿Desde qué sucursal nos visitás?</h2>
            <p class="menu-sucursal-picker__hint">Elegí la ubicación para ver el menú y precios correctos.</p>
            <ul class="menu-sucursal-list" id="menu-sucursal-list">
                @foreach($sucursalesMenu as $s)
                <li>
                    <button type="button" class="menu-sucursal-btn" data-id="{{ $s['id'] }}">
                        <span class="menu-sucursal-btn__icon"><i class="fas fa-store"></i></span>
                        <span class="menu-sucursal-btn__text">{{ $s['nombre'] }}</span>
                        <span class="menu-sucursal-btn__arrow"><i class="fas fa-chevron-right"></i></span>
                    </button>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="brand-bg" aria-hidden="true">
        <div class="brand-center">
            <div class="brand-center__rose"></div>
            <div class="brand-center__name">{{ $nombreNegocio }}</div>
        </div>
    </div>

    <div class="menu-shell{{ empty($idSucursalMenu) && !empty($mostrarSelectorSucursal) ? ' menu-shell--pending' : '' }}" id="menu-shell">
    <div class="app">
        <section id="home-view">
            <header class="header">
                <div class="header__brand">
                    <img id="menu-logo" src="{{ $logoUrl }}" alt="{{ $nombreNegocio }}">
                    <div class="header__brand-text">
                        <strong>{{ $nombreNegocio }}</strong>
                        <span id="menu-sucursal-label">{{ $nombreSucursalActiva ?: ($sloganNegocio ?: 'Menú digital') }}</span>
                    </div>
                </div>
                @if(!empty($mostrarSelectorSucursal))
                <button type="button" class="menu-cambiar-sucursal" id="menu-cambiar-sucursal" title="Cambiar sucursal"{{ empty($idSucursalMenu) ? ' hidden' : '' }}>
                    <i class="fas fa-map-marker-alt"></i>
                    <span class="d-none d-sm-inline">Sucursal</span>
                </button>
                @endif
                <a class="icon-btn order-btn" id="order-link" target="_blank" rel="noopener" aria-label="Ordenar por WhatsApp">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413"/></svg>
                    <span class="order-btn__label">Ordenar</span>
                </a>
                <a class="icon-btn" id="maps-link" href="https://maps.google.com/" target="_blank" rel="noopener" aria-label="Ubicación">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </a>
            </header>

            <div class="search">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input id="search" type="search" placeholder="Buscar plato, bebida, postre…" autocomplete="off">
            </div>

            <nav class="tabs" id="tabs" hidden></nav>

            <div class="menu-body">
                <div class="menu-body__main">
                    <div class="section-head menu-section-head">
                        <h2 id="featured-head">Destacados</h2>
                    </div>
                    <div class="featured" id="featured"></div>

                    <div class="section-head menu-section-head">
                        <h2>Categorías</h2>
                        <span class="menu-section-hint d-none d-md-inline">Elija una sección</span>
                    </div>
                    <div class="category-list" id="category-list"></div>

                    <div id="search-results-block" class="search-results-block" hidden>
                        <div class="section-head menu-section-head">
                            <h2>Resultados</h2>
                            <span id="search-results-count"></span>
                        </div>
                        <div class="search-results-list" id="search-results"></div>
                    </div>

                    <div class="menu-mesas-panel" id="menu-mesas-panel">
                        <div class="menu-mesas-panel__head" onclick="toggleMesasMenuPanel()">
                            <span><i class="fas fa-map"></i> Mapa del local</span>
                            <span class="menu-mesas-panel__badge" id="menu-mesas-badge" hidden></span>
                            <i class="fas fa-chevron-down" id="menu-mesas-chevron"></i>
                        </div>
                        <div class="menu-mesas-panel__content" id="mesas-disponibles-content">
                            <p class="menu-plano-hint">Plano de la sucursal — mesas libres resaltadas</p>
                            <div class="menu-plano-leyenda" aria-hidden="true">
                                <span><i class="leyenda-dot disponible"></i> Libre</span>
                                <span><i class="leyenda-dot ocupada"></i> Ocupada</span>
                            </div>
                            <div class="menu-plano-stage" id="menu-plano-stage" hidden>
                                <div class="menu-plano-canvas" id="menu-plano-canvas">
                                    <div id="menu-plano-zonas"></div>
                                    <div id="menu-plano-mesas"></div>
                                </div>
                                <p class="menu-plano-ref" id="menu-plano-ref"></p>
                            </div>
                            <div id="menu-plano-loading" class="menu-plano-loading" hidden>
                                <i class="fas fa-spinner fa-spin"></i> Cargando mapa…
                            </div>
                            <div id="menu-plano-empty" class="menu-plano-empty" hidden>
                                <p class="small mb-0">No hay mesas configuradas para esta sucursal.</p>
                            </div>
                        </div>
                    </div>

                    <div class="empty" id="search-empty" hidden>
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <p>No encontramos resultados</p>
                    </div>

                    <div class="footer">
                        <p>{{ $nombreNegocio }}</p>
                        <p style="margin-top: 0.3rem;"><a href="{{ $siteUrl }}">← Volver al sitio</a></p>
                    </div>
                </div>

                <aside class="menu-body__aside" id="menu-sidebar" aria-label="Navegación por categorías">
                    <p class="menu-body__aside-title">Categorías</p>
                    <ul class="menu-nav-cats" id="menu-nav-cats"></ul>
                </aside>
            </div>
        </section>

        <section id="cat-view" class="cat-view">
            <div class="back-bar">
                <button type="button" class="icon-btn" id="back-btn" aria-label="Volver">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <div>
                    <h1 id="cat-name"></h1>
                    <div class="back-bar__sub" id="cat-desc"></div>
                </div>
            </div>
            <div class="item-list" id="item-list"></div>
        </section>
    </div>
    </div>

    <div class="detail" id="detail" aria-hidden="true">
        <button type="button" class="detail__close" id="detail-close" aria-label="Cerrar">
            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <div class="detail__inner">
            <div class="detail__hero" id="detail-hero"></div>
            <div class="detail__card">
                <span class="detail__category" id="detail-cat"></span>
                <h1 class="detail__name" id="detail-name"></h1>
                <div class="detail__price" id="detail-price"></div>
                <p class="detail__desc" id="detail-desc"></p>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        var DEFAULT_IMAGE_URL = '{{ asset('assets/images/default-logo.png') }}';
        var base_path = '{{ url('/') }}';
        var CSRF_TOKEN = '{{ csrf_token() }}';
        var MENU_WHATSAPP = '{{ $whatsappPhone }}';
        window.MENU_SUCURSALES = @json($sucursalesMenu ?? []);
        window.MENU_ID_SUCURSAL = {{ $idSucursalMenu ? (int) $idSucursalMenu : 'null' }};
        window.MENU_REQUIERE_SUCURSAL = {{ !empty($mostrarSelectorSucursal) ? 'true' : 'false' }};
        window.MENU_TIPOS_INICIAL = @json($menuTipos ?? []);
        window.MENU_STORAGE_KEY = 'menu_id_sucursal';
    </script>
    <script src="{{ asset('assets/js/mobiliario/mesa-plano-utils.js') }}"></script>
    <script src="{{ asset('assets/js/usuarioExterno/menu-plano-publico.js') }}"></script>
    <script src="{{ asset('assets/js/usuarioExterno/menu.js') }}"></script>
@endsection
