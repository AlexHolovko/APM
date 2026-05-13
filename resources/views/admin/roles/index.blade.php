@extends('adminlte::page')

@section('title', 'Ролі та доступи')

@section('content_header')
    <h1><i class="fas fa-user-tag"></i> Ролі та доступи</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Список ролей</h3>
                <div class="card-tools">
                    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#createRoleModal">
                        <i class="fas fa-plus"></i> Нова роль
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="list-group">
                    @foreach($roles as $role)
                        <a href="#" class="list-group-item list-group-item-action" onclick="loadPermissions({{ $role->id }})">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-tag"></i>
                                    <strong>{{ $role->name }}</strong>
                                    @if(in_array($role->name, ['admin', 'manager', 'specialist', 'accountant']))
                                        <span class="badge badge-warning ml-1">Системна</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="badge badge-primary">{{ $role->users_count ?? 0 }} користувачів</span>
                                    <span class="badge badge-info">{{ $role->permissions->count() }} прав</span>
                                </div>
                            </div>
                            @if(!in_array($role->name, ['admin', 'manager', 'specialist', 'accountant']))
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-warning" onclick="event.stopPropagation(); editRole({{ $role->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteRole({{ $role->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card" id="permissions-card" style="display: none;">
            <div class="card-header">
                <h3 class="card-title">Права доступу для <span id="role-name"></span></h3>
                <div class="card-tools">
                    <button class="btn btn-primary btn-sm" onclick="savePermissions()" id="save-permissions-btn">
                        <i class="fas fa-save"></i> Зберегти
                    </button>
                </div>
            </div>
            <div class="card-body" id="permissions-list" style="max-height: 500px; overflow-y: auto;">
                <div class="text-center text-muted p-5">
                    <i class="fas fa-shield-alt fa-3x mb-3"></i>
                    <p>Виберіть роль зліва для керування правами</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create Role -->
<div class="modal fade" id="createRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Створити нову роль</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('admin.roles.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Назва ролі</label>
                        <input type="text" name="name" class="form-control" required placeholder="наприклад: supervisor">
                        <small class="text-muted">Латинські літери, цифри та знак підкреслення</small>
                    </div>
                    <div class="form-group">
                        <label>Опис (необов'язково)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Опишіть призначення ролі"></textarea>
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

<!-- Modal Edit Role -->
<div class="modal fade" id="editRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редагувати роль</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="editRoleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Назва ролі</label>
                        <input type="text" name="name" id="edit_role_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Скасувати</button>
                    <button type="submit" class="btn btn-primary">Зберегти</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    let currentRoleId = null;
    let isSystemRole = false;
    
    function loadPermissions(roleId) {
        currentRoleId = roleId;
        
        $.get('/admin/roles/' + roleId + '/permissions', function(data) {
            $('#role-name').text(data.role_name);
            isSystemRole = data.is_system_role;
            
            if (isSystemRole) {
                $('#save-permissions-btn').prop('disabled', true).text('Системну роль не можна змінювати');
            } else {
                $('#save-permissions-btn').prop('disabled', false).text('Зберегти');
            }
            
            let html = '<div class="row">';
            
            // Группировка разрешений по модулям
            const permissionsByModule = {};
            data.permissions.forEach(function(permission) {
                let module = 'Інше';
                if (permission.name.includes('users')) module = '👥 Користувачі';
                else if (permission.name.includes('roles')) module = '🏷️ Ролі';
                else if (permission.name.includes('policies')) module = '📄 Поліси';
                else if (permission.name.includes('clients')) module = '👤 Клієнти';
                else if (permission.name.includes('audit')) module = '📊 Аудит';
                else if (permission.name.includes('payments')) module = '💰 Платежі';
                
                if (!permissionsByModule[module]) {
                    permissionsByModule[module] = [];
                }
                permissionsByModule[module].push(permission);
            });
            
            for (const [module, perms] of Object.entries(permissionsByModule)) {
                html += `<div class="col-md-12 mb-3">
                            <h6 class="border-bottom pb-2">${module}</h6>`;
                perms.forEach(function(permission) {
                    const isChecked = data.role_permissions.includes(permission.id);
                    html += `
                        <div class="form-check form-check-inline mr-3 mb-2">
                            <input type="checkbox" class="form-check-input permission-checkbox" 
                                id="perm_${permission.id}" 
                                value="${permission.id}"
                                ${isChecked ? 'checked' : ''}
                                ${isSystemRole ? 'disabled' : ''}>
                            <label class="form-check-label" for="perm_${permission.id}">
                                ${permission.name}
                            </label>
                        </div>
                    `;
                });
                html += '</div>';
            }
            
            html += '</div>';
            
            if (data.permissions.length === 0) {
                html = '<div class="alert alert-info">Немає доступних прав для налаштування</div>';
            }
            
            $('#permissions-list').html(html);
            $('#permissions-card').show();
        }).fail(function() {
            $('#permissions-list').html('<div class="alert alert-danger">Помилка завантаження даних</div>');
            $('#permissions-card').show();
        });
    }
    
    function savePermissions() {
        if (!currentRoleId || isSystemRole) return;
        
        const permissions = [];
        $('.permission-checkbox:checked').each(function() {
            permissions.push($(this).val());
        });
        
        $.post('/admin/roles/' + currentRoleId + '/permissions', {
            _token: '{{ csrf_token() }}',
            permissions: permissions
        }, function(response) {
            if (response.success) {
                toastr.success('Права доступу збережено!');
                loadPermissions(currentRoleId);
            } else {
                toastr.error(response.message || 'Помилка збереження');
            }
        }).fail(function() {
            toastr.error('Помилка при збереженні прав');
        });
    }
    
    function editRole(roleId) {
        $.get('/admin/roles/' + roleId + '/edit', function(data) {
            $('#edit_role_name').val(data.name);
            $('#editRoleForm').attr('action', '/admin/roles/' + roleId);
            $('#editRoleModal').modal('show');
        });
    }
    
    function deleteRole(roleId) {
        if (confirm('Ви впевнені, що хочете видалити цю роль? Вона буде видалена назавжди.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/roles/' + roleId;
            form.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endpush

@push('css')
<style>
    .list-group-item {
        cursor: pointer;
        transition: all 0.2s;
    }
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
    .form-check-inline {
        margin-right: 15px;
    }
    .permission-checkbox:disabled + label {
        color: #6c757d;
        cursor: not-allowed;
    }
</style>
@endpush