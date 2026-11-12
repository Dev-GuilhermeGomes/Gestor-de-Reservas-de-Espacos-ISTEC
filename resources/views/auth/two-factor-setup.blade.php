<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurar autenticação · Reservas ISTEC</title>
    <link rel="stylesheet" href="/css/istec.css">
</head>
<body class="login">
    <section class="login-lado">
        <h2>Autenticação protegida</h2>
        <p>Configura uma aplicação autenticadora para proteger o acesso à tua conta.</p>
    </section>
    <section class="login-form">
        <div class="login-caixa">
            <img src="/img/istec-logo.png" alt="ISTEC - Instituto Superior de Tecnologias Avançadas do Porto">
            <h1>Configurar segundo fator</h1>
            <p>Adiciona este endereço à tua aplicação autenticadora. Se a aplicação aceitar QR, podes usar o endereço abaixo.</p>
            <label for="secret">Chave de configuração</label>
            <input id="secret" value="{{ $secret }}" readonly>
            <label for="uri">Endereço otpauth</label>
            <textarea id="uri" readonly>{{ $uri }}</textarea>
            <form method="POST" action="/two-factor/setup">
                @csrf
                <label for="code">Código de 6 dígitos</label>
                <input id="code" type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus>
                @error('code') <p class="erro">{{ $message }}</p> @enderror
                <button class="btn" type="submit">Confirmar configuração</button>
            </form>
        </div>
    </section>
</body>
</html>
