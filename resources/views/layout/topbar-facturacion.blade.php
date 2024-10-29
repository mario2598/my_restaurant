<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar sticky space-navbar" style="left:0 !important">
    <div class="form-inline mr-auto">
        <ul class="navbar-nav mr-3 border-end border-dark">
            <li><a class="nav-link nav-link-lg" style="color: #555556 !important"
                    href="http://127.0.0.1:8000/facturacion/facturar">Nueva <i class="fas fa-file-invoice"></i></a></li>
        </ul>
        <ul class="navbar-nav mr-3">
            <li><a class="nav-link nav-link-lg" style="color: #555556 !important"
                    href="http://127.0.0.1:8000/cocina/cocina/comandas">Cocina <i class="fas fa-hamburger"></i></a></li>
            <li><a class="nav-link nav-link-lg" style="color: #555556 !important"
                    href="http://127.0.0.1:8000/cocina/bebidas/comandas">Bebidas <i class="fas fa-wine-glass"></i></a></li>
            <li><a class="nav-link nav-link-lg" style="color: #555556 !important"
                    href="http://127.0.0.1:8000/cocina/ordenesListas/comanda">Listas <i class="fas fa-check-square"></i></a>
            </li>
            <li><a class="nav-link nav-link-lg" style="color: #555556 !important"
                    href="http://127.0.0.1:8000/cocina/facturar/ordenes">Facturar <i class="fas fa-file-invoice-dollar"></i></a></li>

        </ul>
    </div>
    <ul class="navbar-nav navbar-right">

        <li><a href="#" class="nav-link nav-link-lg fullscreen-btn mt-1">
                <i data-feather="maximize"></i>
            </a></li>
        <li class="dropdown"><a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg"><i
                    class="fas fa-user-cog" style="color:#555556"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right pullDown">
                <div class="dropdown-title">Bienvenido {{ session('usuario')['nombre'] ?? 'Usuario' }}</div>

                <div class="dropdown-divider"></div>
                <a href="{{ url('login') }}" class="dropdown-item has-icon text-danger"> <i
                        class="fas fa-sign-out-alt"></i>
                    Cerrar Sesión
                </a>
            </div>
        </li>
    </ul>
</nav>
