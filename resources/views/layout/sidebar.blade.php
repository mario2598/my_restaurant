<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="row">
            <div class="col-12">
                <div class="sidebar-brand  align-items-center justify-content-center pt-2">
                    <a href="{{ url('/') }}" class="d-inline-block text-center">
                        <img id="imgLogoSidebar" title="Nombre empresa" alt="Logo empresa"
                            src="{{ session('usuario')['url_logo_sistema'] ?? '' }}"
                            class="img-thumbnail"
                            style="background-color: transparent; border-color: transparent;" />
                    </a>
                </div>
            </div>
            <div class="col-12">
                <ul class="sidebar-menu mt-2">
                    <li class="menu-header">Menú</li>
                    <?php $menusSide = \App\Traits\SpaceUtil::cargarMenus(); ?>
                    @foreach ($menusSide ?? [] as $m)
                    <li class="dropdown">
                        <a href="#" class="menu-toggle nav-link has-dropdown">
                            <i class="{{ $m->icon ?? '' }}" style="font-size:24px; margin-left:-1px;"></i>
                            <span>{{ $m->titulo }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            @foreach ($m->submenus as $sm)
                            <li class="menu-item" title="{{ $sm->titulo }}">
                                <a href="{{ url($sm->ruta) }}" class="truncate-text">{{ $sm->titulo }}</a>
                            </li>
                            @endforeach
                        </ul>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </aside>
</div>

<style>
    .main-sidebar .sidebar-brand {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        text-align: center;
    }

    .main-sidebar .sidebar-brand img#imgLogoSidebar {
        max-width: 100%;
        height: auto;
        display: inline-block;
    }

    .main-sidebar .sidebar-menu {
        padding-top: 0.25rem;
    }

    .sidebar-menu .menu-item a.truncate-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }

    /* Tooltip styling */
    .menu-item[title] {
        position: relative;
    }

    .menu-item[title]::before {
        content: attr(title);
        position: absolute;
        left: 0;
        top: 100%;
        background: rgba(0, 0, 0, 0.7);
        color: #fff;
        padding: 5px;
        font-size: 12px;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
        z-index: 1;
    }

    .menu-item[title]:hover::before {
        opacity: 1;
    }
</style>