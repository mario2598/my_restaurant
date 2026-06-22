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

            {{-- ============================================================ --}}
            {{-- AUXILIAR POS - Acceso rápido al Punto de Venta               --}}
            {{-- ============================================================ --}}
            @php
                $posFacGrupo = collect($menusSide)->firstWhere('codigo_grupo', 'fac');
                $ordenesActivas = 0;
                if ($posFacGrupo) {
                    $sucursalUsuario = session('usuario.sucursal') ?? session('usuario')['sucursal'] ?? null;
                    $qOrdenes = \Illuminate\Support\Facades\DB::table('orden')
                        ->where('pagado', 0);
                    if ($sucursalUsuario) {
                        $qOrdenes->where('sucursal', $sucursalUsuario);
                    }
                    $ordenesActivas = $qOrdenes->count();
                }
            @endphp

            @if($posFacGrupo ?? null)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="aux-pos-wrap">
                        <div class="aux-pos-ident">
                            <div class="aux-pos-ico"><i class="fas fa-cash-register"></i></div>
                            <div>
                                <div class="aux-pos-name">Punto de Venta</div>
                                @if($ordenesActivas > 0)
                                    <div class="aux-pos-chip">
                                        <i class="fas fa-circle" style="font-size:7px;vertical-align:middle;color:#fbbf24;margin-right:4px;"></i>
                                        {{ $ordenesActivas }} orden{{ $ordenesActivas != 1 ? 'es' : '' }} abierta{{ $ordenesActivas != 1 ? 's' : '' }} en tu sucursal
                                    </div>
                                @else
                                    <div class="aux-pos-chip aux-pos-chip--muted">Sin órdenes abiertas en tu sucursal</div>
                                @endif
                            </div>
                        </div>
                        <div class="aux-pos-btns">
                            @php
                                $posIcons = ['fas fa-desktop', 'fas fa-truck', 'fas fa-list-alt', 'fas fa-receipt'];
                            @endphp
                            @foreach($posFacGrupo->submenus as $pIdx => $pSub)
                                @php
                                    $pRuta = Str::startsWith($pSub->ruta ?? '', 'http') ? $pSub->ruta : url($pSub->ruta ?? '#');
                                @endphp
                                <a href="{{ $pRuta }}"
                                   class="aux-pos-btn {{ $pIdx === 0 ? 'aux-pos-btn--primary' : 'aux-pos-btn--ghost' }}">
                                    <i class="{{ $posIcons[$pIdx % count($posIcons)] }}"></i>
                                    <span>{{ $pSub->titulo }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
            {{-- ============================================================ --}}

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

<style>
/* === Auxiliar POS === */
.aux-pos-wrap {
    background: linear-gradient(135deg, #1a3a8f 0%, #2e59d9 100%);
    border-radius: 14px;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
    box-shadow: 0 4px 18px rgba(46,89,217,.28);
}
.aux-pos-ident { display: flex; align-items: center; gap: 14px; }
.aux-pos-ico {
    width: 48px; height: 48px; border-radius: 50%;
    background: rgba(255,255,255,.15);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.aux-pos-ico i { color: #fff; font-size: 1.3rem; }
.aux-pos-name { color: #fff; font-size: 1.05rem; font-weight: 700; }
.aux-pos-chip {
    margin-top: 3px;
    display: inline-block;
    background: rgba(255,255,255,.15);
    border-radius: 20px;
    padding: 2px 10px;
    font-size: 11px;
    color: #dce8ff;
}
.aux-pos-chip--muted { color: rgba(255,255,255,.45); background: transparent; padding-left: 0; }
.aux-pos-btns { display: flex; flex-wrap: wrap; gap: 8px; }
.aux-pos-btn {
    display: inline-flex; align-items: center; gap: 7px;
    border-radius: 24px; padding: 8px 18px;
    font-size: .88rem; font-weight: 600;
    text-decoration: none !important;
    transition: all .18s ease;
    white-space: nowrap;
}
.aux-pos-btn--primary {
    background: #fff;
    color: #1a3a8f !important;
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
}
.aux-pos-btn--primary:hover { box-shadow: 0 4px 14px rgba(0,0,0,.2); transform: translateY(-1px); }
.aux-pos-btn--ghost {
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.28);
    color: #fff !important;
}
.aux-pos-btn--ghost:hover { background: rgba(255,255,255,.24); transform: translateY(-1px); }
@media (max-width: 576px) {
    .aux-pos-wrap { flex-direction: column; align-items: flex-start; }
    .aux-pos-btns { width: 100%; }
    .aux-pos-btn { flex: 1; justify-content: center; }
}
</style>
@endsection
