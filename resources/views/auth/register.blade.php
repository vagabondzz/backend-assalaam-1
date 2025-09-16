<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>Form Register</h2>
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif
    @if($errors->any())
        <ul style="color: red;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ url('register') }}" method="POST">
        @csrf
        <label>Nama:</label><br>
        <input type="text" name="name" value="{{ old('name') }}" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="{{ old('email') }}" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Konfirmasi Password:</label><br>
        <input type="password" name="password_confirmation" required><br><br>

        <button type="submit">Daftar</button>
    </form>

    <p>Sudah punya akun? <a href="{{ url('login') }}">Login</a></p>
</body>
</html>
