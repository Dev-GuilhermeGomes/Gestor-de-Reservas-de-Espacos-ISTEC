<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar · Reservas ISTEC</title>
    <link rel="stylesheet" href="/css/istec.css">
</head>
<body class="login">
    <section class="login-lado">
        <h2>Gestor de Reservas de Espaços</h2>
        <p>Salas, laboratórios, auditórios e equipamentos do ISTEC num só sítio, sem conflitos de utilização.</p>
    </section>
    <section class="login-form">
        <div class="login-caixa">
            <img src="/img/istec-logo.png" alt="ISTEC - Instituto Superior de Tecnologias Avançadas do Porto">
            <h1>Iniciar sessão</h1>
            <form method="POST" action="/login">
                @csrf
                <label for="email">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                <label for="password">Palavra-passe</label>
                <input id="password" type="password" name="password" required>
                @error('email') <p class="erro">{{ $message }}</p> @enderror
                <button class="btn" type="submit">Entrar</button>
            </form>
        </div>
    </section>
</body>
</html>