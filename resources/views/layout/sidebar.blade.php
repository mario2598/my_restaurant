<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
      <div class="sidebar-brand">
        <a href="{{ url('/') }}"> <img title="Nombre empresa" alt="Nombre empresa" src="{{asset("assets/images/default-image.png")}}" style="background-color: transparent;border-color: transparent;" class="img-thumbnail"/> <span
            class="logo-name" style="color: transparent;" >Admin</span>
        </a>
      </div>
      <ul class="sidebar-menu">
        <li class="menu-header">Menú</li>
     
        @foreach ($data['menus'] ?? [] as $m)
          <li class="dropdown">
            <a href="#" class="menu-toggle nav-link has-dropdown"><i class="{{$m->icon ?? ''}}" style="font-size:24px;margin-left:-1px;"></i><span>{{$m->titulo}}</span></a>
            <ul class="dropdown-menu">
            @foreach ($m->submenus as $sm)
              <li><a href="{{url($sm->ruta)}}">{{$sm->titulo}}</a></li>
            @endforeach
          </ul>
        </li>
        @endforeach
   
      
          </ul>
        </li>
      </ul>
    </aside>
  </div>