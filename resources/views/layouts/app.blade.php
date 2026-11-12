@php
    $u = auth()->user();
    $nomes = ['UTILIZADOR' => 'Utilizador', 'GESTOR_ESPACOS' => 'Gestor de Espaços', 'ADMINISTRADOR' => 'Administrador'];
    $gere = in_array($u->perfil, ['GESTOR_ESPACOS', 'ADMINISTRADOR']);
@endphp
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo') · Reservas ISTEC</title>
    <link rel="stylesheet" href="/css/istec.css">
</head>
<body class="painel">
    <aside class="lateral">
        <div class="lateral-logo"><img src="/img/istec-logo.png" alt="ISTEC"></div>
        <nav>
            <a href="/painel" class="{{ request()->is('painel') ? 'ativo' : '' }}">Início</a>
            <span class="breve">Reservar espaço</span>
            <span class="breve">As minhas reservas</span>

            @if($gere)
                <span class="grupo">Gestor de Espaços</span>
                <a href="/gestor" class="{{ request()->is('gestor') ? 'ativo' : '' }}">Pedidos</a>
                <a href="/gestor/ocupacao" class="{{ request()->is('gestor/ocupacao') ? 'ativo' : '' }}">Ocupação global</a>
                <a href="/gestor/indisponibilidades" class="{{ request()->is('gestor/indisponibilidades') ? 'ativo' : '' }}">Indisponibilidades</a>
                <a href="/gestor/salas" class="{{ request()->is('gestor/salas') ? 'ativo' : '' }}">Salas</a>
                <span class="breve">Regras de utilização</span>
            @endif

            @if($u->perfil === 'ADMINISTRADOR')
                <span class="grupo">Administração</span>
                <a href="/admin" class="{{ request()->is('admin') ? 'ativo' : '' }}">Painel</a>
                <span class="breve">Espaços e recursos</span>
                <span class="breve">Relatórios</span>
            @endif
        </nav>
        <div class="lateral-fim">
            {{ $u->nome }}
            <form method="POST" action="/logout">@csrf<button class="btn" type="submit">Sair</button></form>
        </div>
    </aside>
    <div class="corpo">
        <header class="topo">
            <h1>@yield('titulo')</h1>
            <span class="etiqueta">{{ $nomes[$u->perfil] ?? $u->perfil }}</span>
        </header>
        <main>@yield('conteudo')</main>
        <footer class="rodape">
            <span>ISTEC · Gestor de Reservas de Espaços</span>
            <span>© {{ now()->year }}</span>
        </footer>
    </div>
</body>
</html>