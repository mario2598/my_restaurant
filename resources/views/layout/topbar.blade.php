<style>
.nav-home-label {
    font-size: .72rem;
    font-weight: 700;
    color: #4e73df;
    margin-left: 4px;
    vertical-align: middle;
    letter-spacing: .02em;
}
nav .nav-link:has(.nav-home-label) {
    background: rgba(78,115,223,.08);
    border-radius: 8px;
    margin: 4px 2px;
    padding: 6px 10px !important;
    transition: background .18s;
}
nav .nav-link:has(.nav-home-label):hover {
    background: rgba(78,115,223,.18);
}
</style>
<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar sticky space-navbar" style="">
    <div class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg
									collapse-btn"> <i
                        data-feather="align-justify"></i></a></li>
            <li><a href="#" class="nav-link nav-link-lg fullscreen-btn">
                    <i data-feather="maximize"></i>
                </a></li>
            <li>
                <a href="{{ url('/') }}" class="nav-link nav-link-lg"
                   title="Ir al inicio"
                   style="position:relative;">
                    <i data-feather="home"></i>
                    <span class="nav-home-label">Inicio</span>
                </a>
            </li>

        </ul>
    </div>
    <ul class="navbar-nav navbar-right">

        <li class="dropdown"><a href="#" data-toggle="dropdown"
                class="nav-link notification-toggle nav-link-lg"><i class="fas fa-user-cog" style="color:#555556"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right pullDown">
                <div class="dropdown-title">Bienvenido {{ session('usuario')['nombre'] ?? 'Usuario' }}</div>
                <div class="dropdown-divider"></div>
                <a href="{{ URL::to('perfil/usuario') }}" class="dropdown-item has-icon text-danger"> <i
                        class="fas fa-cogs"></i>
                    Mi Perfil
                </a>
                <div class="dropdown-divider"></div>
                <a href="{{ url('logOut') }}" class="dropdown-item has-icon text-danger"> <i
                        class="fas fa-sign-out-alt"></i>
                    Cerrar Sesión
                </a>

            </div>
        </li>
    </ul>
</nav>
