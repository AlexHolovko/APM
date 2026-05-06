<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <title>Вход</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3/dist/css/adminlte.min.css">
</head>

<body class="hold-transition login-page">

  <div class="login-box">
    <div class="login-logo">
      <b>Insurance</b>App
    </div>

    <div class="card">
      <div class="card-body login-card-body">
        <p class="login-box-msg">Вход в систему</p>

        <form method="POST" action="{{ route('login.perform') }}">
          @csrf

          <div class="input-group mb-3">
            <input type="email" name="email" class="form-control" placeholder="Email" required>
          </div>

          <div class="input-group mb-3">
            <input type="password" name="password" class="form-control" placeholder="Пароль" required>
          </div>

          <button type="submit" class="btn btn-primary btn-block">
            Войти
          </button>
        </form>

        @if($errors->any())
          <div class="text-danger mt-2">
            Неверный логин или пароль
          </div>
        @endif

      </div>
    </div>
  </div>

</body>

</html>