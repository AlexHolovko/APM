@extends('adminlte::page')

@section('title', 'Профіль')

@section('content')
<div class="container">

    <h3>Профіль</h3>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">Інформація</div>
        <div class="card-body">
            <p><strong>Ім’я:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Роль:</strong> {{ $user->role }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Змінити пароль</div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf

                <div class="form-group">
                    <label>Новий пароль</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="form-group mt-2">
                    <label>Підтвердіть пароль</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button class="btn btn-primary mt-3">Оновити пароль</button>
            </form>
        </div>
    </div>

</div>
@endsection