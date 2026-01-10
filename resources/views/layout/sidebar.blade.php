<div class="main-sidebar sidebar-style-2">
  <aside id="sidebar-wrapper">
      <div class="sidebar-brand">
          <a href="{{ url('/') }}">
              <img title="Nombre empresa" alt="Logo empresa"
                  src="{{ asset('assets/images/default-logo.png') }}"
                  style="background-color: transparent; border-color: transparent;" class="img-thumbnail" />
          </a>
      </div>
      <ul class="sidebar-menu">
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
  </aside>
</div>

<style>
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
