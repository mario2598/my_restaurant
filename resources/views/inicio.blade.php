@extends('layout.master')

@section('content')
@include('layout.sidebar')

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Inicio</h1>
        </div>
        <div class="section-body">

            <?php
                $menusSide = \App\Traits\SpaceUtil::cargarMenus();
                $usuario   = session('usuario');

                // Color palette per grupo
                $colores = [
                    'fac'         => ['bg' => '#4e73df', 'icon_bg' => '#2e59d9'],
                    'informes'    => ['bg' => '#1cc88a', 'icon_bg' => '#17a673'],
                    'gastos'      => ['bg' => '#e74a3b', 'icon_bg' => '#be2617'],
                    'ingresos'    => ['bg' => '#f6c23e', 'icon_bg' => '#dda20a'],
                    'mant'        => ['bg' => '#858796', 'icon_bg' => '#60616f'],
                    'mt_prod'     => ['bg' => '#36b9cc', 'icon_bg' => '#258391'],
                    'cod_ext'     => ['bg' => '#fd7e14', 'icon_bg' => '#c96209'],
                    'entregas'    => ['bg' => '#20c997', 'icon_bg' => '#158a67'],
                    'comandasGen' => ['bg' => '#6f42c1', 'icon_bg' => '#533291'],
                    'comandasPrep'=> ['bg' => '#e83e8c', 'icon_bg' => '#b02b6a'],
                    'mobiliarioGen'=> ['bg' => '#6610f2', 'icon_bg' => '#4a09b0'],
                    'fes'         => ['bg' => '#17a2b8', 'icon_bg' => '#107282'],
                    'usuExt'      => ['bg' => '#343a40', 'icon_bg' => '#1a1d20'],
                ];
            ?>

            @if(empty($menusSide))
                <div class="alert alert-warning">No hay módulos disponibles para tu perfil.</div>
            @else
                <div class="row" id="accesos-rapidos">
                @foreach($menusSide as $grupo)
                    <?php
                        $key    = $grupo->codigo_grupo ?? '';
                        $pal    = $colores[$key] ?? ['bg' => '#4e73df', 'icon_bg' => '#2e59d9'];
                        $icon   = $grupo->icon ?? 'fas fa-th';
                        $titulo = $grupo->titulo ?? '';
                        $subs   = $grupo->submenus ?? [];
                    ?>
                    <div class="col-12 col-sm-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm border-0 acceso-card"
                             style="border-radius:12px; overflow:hidden;">

                            {{-- Header coloreado --}}
                            <div class="card-header d-flex align-items-center py-3 px-4 border-0"
                                 style="background:{{ $pal['bg'] }}; border-radius:12px 12px 0 0;">
                                <div class="mr-3 d-flex align-items-center justify-content-center"
                                     style="width:40px;height:40px;border-radius:50%;
                                            background:{{ $pal['icon_bg'] }};">
                                    <i class="{{ $icon }}" style="color:#fff;font-size:1.1rem;"></i>
                                </div>
                                <span style="color:#fff;font-weight:700;font-size:1rem;
                                             letter-spacing:.03em;">{{ $titulo }}</span>
                            </div>

                            {{-- Botones de acceso --}}
                            <div class="card-body py-3 px-3"
                                 style="background:#fff;">
                                @if(empty($subs))
                                    <span class="text-muted small">Sin módulos disponibles.</span>
                                @else
                                    <div class="d-flex flex-wrap" style="gap:6px;">
                                    @foreach($subs as $sub)
                                        <?php $ruta = Str::startsWith($sub->ruta ?? '', 'http')
                                                        ? $sub->ruta
                                                        : url($sub->ruta ?? '#'); ?>
                                        <a href="{{ $ruta }}"
                                           class="btn btn-sm acceso-btn"
                                           style="background:{{ $pal['bg'] }}1a;
                                                  color:{{ $pal['bg'] }};
                                                  border:1px solid {{ $pal['bg'] }}44;
                                                  border-radius:20px;
                                                  font-size:.82rem;
                                                  font-weight:600;
                                                  padding:4px 14px;
                                                  transition:all .18s ease;"
                                           onmouseover="this.style.background='{{ $pal['bg'] }}';this.style.color='#fff';"
                                           onmouseout="this.style.background='{{ $pal['bg'] }}1a';this.style.color='{{ $pal['bg'] }}';">
                                            {{ $sub->titulo }}
                                        </a>
                                    @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            @endif

        </div>
    </section>
</div>

<style>
.acceso-card {
    transition: transform .2s ease, box-shadow .2s ease;
}
.acceso-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,.12) !important;
}
#accesos-rapidos .row {
    margin-left: -8px;
    margin-right: -8px;
}
</style>
@endsection
