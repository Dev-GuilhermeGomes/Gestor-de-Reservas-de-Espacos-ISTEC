<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificação · Reservas ISTEC</title>
    <link rel="stylesheet" href="/css/istec.css">
</head>
<body class="login">
    <section class="login-lado">
        <h2>Confirma a tua identidade</h2>
        <p>Introduz o código atual da tua aplicação autenticadora para continuar.</p>
    </section>
    <section class="login-form">
        <div class="login-caixa">
            <img src="/img/istec-logo.png" alt="ISTEC - Instituto Superior de Tecnologias Avançadas do Porto">
            <h1>Código de autenticação</h1>
            <form method="POST" action="/two-factor/challenge">
                @csrf
                <label for="code">Código de 6 dígitos</label>
                <input id="code" type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus>
                @error('code') <p class="erro">{{ $message }}</p> @enderror
                <button class="btn" type="submit">Verificar código</button>
            </form>
            <p><a href="/login">Voltar ao início de sessão</a></p>
        </div>
    </section>
</body>
</html>
