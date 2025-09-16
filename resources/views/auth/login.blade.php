<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Form Login</h2>
    @if(session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <form action="{{ url('login') }}" method="POST">
        @csrf
        <label>Email:</label><br>
        <input type="email" name="email" value="{{ old('email') }}" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>

    <p>Belum punya akun? <a href="{{ url('register') }}">Daftar</a></p>
</body>
</html>
