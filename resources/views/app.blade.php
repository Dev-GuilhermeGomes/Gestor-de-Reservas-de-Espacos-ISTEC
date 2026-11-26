@php
    $oldInput = session()->getOldInput();
    unset($oldInput['password'], $oldInput['code'], $oldInput['_token']);

    $initialPage = [
        'page' => $page,
        'auth' => auth()->user()?->only(['id', 'nome', 'email', 'perfil']),
        'errors' => $errors->getBag('default')->messages(),
        'old' => $oldInput,
        'csrfToken' => csrf_token(),
        'flash' => [
            'success' => session('sucesso'),
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reservas ISTEC</title>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    <link rel="stylesheet" href="/css/istec.css">
</head>
<body>
    <div id="app"></div>
    <script>
        window.__APP_PAGE__ = {{ Illuminate\Support\Js::from($initialPage) }};
    </script>
</body>
</html>
