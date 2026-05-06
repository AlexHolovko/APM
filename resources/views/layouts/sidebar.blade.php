<ul class="sidebar-menu">

    @role('admin')
        <li><a href="{{ url('/admin') }}"><i class="fa fa-user-shield"></i> Админ-панель</a></li>
    @endrole

    @role('manager')
        <li><a href="{{ url('/manager') }}"><i class="fa fa-tasks"></i> Менеджер</a></li>
    @endrole

    @role('specialist')
        <li><a href="{{ url('/specialist') }}"><i class="fa fa-user-md"></i> Специалист</a></li>
    @endrole

    @role('accountant')
        <li><a href="{{ url('/accountant') }}"><i class="fa fa-calculator"></i> Бухгалтер</a></li>
    @endrole

</ul>