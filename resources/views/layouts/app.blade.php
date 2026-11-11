<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f7f2">
    <title>@yield('title', 'Mural da Comunidade') | Mural da Comunidade</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('announcements.index') }}" aria-label="Mural da Comunidade, página inicial">
                <span class="brand-mark" aria-hidden="true">M</span>
                <span>Mural<span class="brand-light"> da comunidade</span></span>
            </a>
            <a class="button button-dark header-action" href="{{ route('announcements.create') }}">
                <span aria-hidden="true">＋</span> Anunciar
            </a>
        </div>
    </header>

    <main>
        @if (session('status'))
            <div class="flash-message" role="status">{{ session('status') }}</div>
        @endif
        @if (session('management_url'))
            <div class="management-message">
                <div>
                    <strong>Guarde seu link privado de gerenciamento</strong>
                    <p>Somente quem tiver este link poderá editar ou excluir o anúncio.</p>
                </div>
                <a class="button button-dark" href="{{ session('management_url') }}">Abrir meu anúncio <span aria-hidden="true">→</span></a>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="site-footer">
        <p>Feito para aproximar quem oferece de quem procura. <span aria-hidden="true">♥</span></p>
    </footer>
</body>
</html>
