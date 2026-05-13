@extends('adminlte::page')

@section('title', 'Користувачі')

@section('content_header')
    <h1><i class="fas fa-users"></i> Користувачі</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Список користувачів системи</h3>
        <div class="card-tools">
            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#createUserModal">
                <i class="fas fa-plus"></i> Новий користувач
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ім'я</th>
                        <th>Email</th>
                        <th>Ролі</th>
                        <th>Створено</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge badge-primary">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td>{{ $user->created_at->format('d.m.Y') }}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editUser({{ $user->id }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteUser({{ $user->id }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Немає користувачів</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- Modal Create User -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Створити користувача</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Ім'я</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Пароль</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Роль</label>
                        <select name="role" class="form-control" required>
                            <option value="">Виберіть роль</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Скасувати</button>
                    <button type="submit" class="btn btn-success">Створити</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function editUser(id) {
        window.location.href = '/admin/users/' + id + '/edit';
    }
    
    function deleteUser(id) {
        if (confirm('Ви впевнені?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/users/' + id;
            form.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endpush