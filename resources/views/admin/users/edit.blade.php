@extends('adminlte::page')

@section('title', 'Редагувати користувача')

@section('content_header')
    <h1><i class="fas fa-user-edit"></i> Редагувати користувача</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Редагування: {{ $user->name }}</h3>
        <div class="card-tools">
            <a href="{{ route('admin.users.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </div>
    </div>
    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>Ім'я</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="form-group">
                <label>Пароль (залиште порожнім, якщо не змінюєте)</label>
                <input type="password" name="password" class="form-control" placeholder="Новий пароль">
            </div>
            <div class="form-group">
                <label>Роль</label>
                <select name="role" class="form-control" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Зберегти зміни</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-default">Скасувати</a>
        </div>
    </form>
</div>
@endsection