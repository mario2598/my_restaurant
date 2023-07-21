<label>Permisos</label>
@foreach ($data['vistas'] as $i)
<div class="card-body">
<div class="section-title">{{$i->titulo ?? ''}}</div>
    @foreach ($i->submenus as $m)
        <div class="pretty p-default p-curve p-thick">
        <input type="checkbox" name="menus[]" value="{{$m->id}}"
        @foreach ($data['permisos'] as $p)
        @if ($p->vista == $m->id)
            checked
        @endif
        @endforeach
        />
            <div class="state p-warning">
            <label>{{$m->titulo ?? ''}}</label>
            </div>
        </div>
    @endforeach
</div>
@endforeach