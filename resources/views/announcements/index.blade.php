@extends('layouts.app')

@section('title', 'Ofertas e serviços locais')

@section('content')
    <section class="hero">
        <div class="hero-inner">
            <div class="hero-copy">
                <span class="eyebrow"><span class="eyebrow-dot"></span> O que é bom acontece por perto</span>
                <h1>Encontre. Ofereça.<br><span>Fortaleça a comunidade.</span></h1>
                <p>Um espaço feito por vizinhos para descobrir produtos, serviços e oportunidades locais.</p>
                <a class="button button-accent" href="{{ route('announcements.create') }}">＋ Publique seu anúncio</a>
            </div>
            <div class="hero-art" aria-hidden="true">
                <div class="art-sun"></div>
                <div class="art-card art-card-back"><span>feito aqui</span><strong>✳</strong></div>
                <div class="art-card art-card-front"><span>perto de você</span><strong>⌂</strong></div>
                <div class="art-spark spark-one">✦</div>
                <div class="art-spark spark-two">✳</div>
            </div>
        </div>
    </section>

    <section class="board-section">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Mural local</span>
                <h2>Descubra o que tem por perto</h2>
                <p>{{ $announcements->total() }} {{ $announcements->total() === 1 ? 'anúncio publicado' : 'anúncios publicados' }}</p>
            </div>
            <a class="text-link desktop-link" href="{{ route('announcements.create') }}">Tem algo para oferecer? <span>Publique aqui →</span></a>
        </div>

        <form class="filters" action="{{ route('announcements.index') }}" method="GET" id="announcement-filters">
            <label class="search-box">
                <span class="sr-only">Buscar anúncios</span>
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 4.5 4.5"></path></svg>
                <input type="search" name="q" value="{{ $search }}" placeholder="O que você está procurando?" autocomplete="off">
            </label>
            <label class="filter-select">
                <span class="sr-only">Filtrar por categoria</span>
                <select name="category">
                    <option value="">Todas as categorias</option>
                    @foreach ($categories as $option)
                        <option value="{{ $option }}" @selected($category === $option)>{{ $option }}</option>
                    @endforeach
                </select>
                <span aria-hidden="true">⌄</span>
            </label>
            <label class="filter-select sort-select">
                <span class="sr-only">Ordenar anúncios</span>
                <select name="sort">
                    <option value="recentes" @selected($sort !== 'titulo')>Mais recentes</option>
                    <option value="titulo" @selected($sort === 'titulo')>Ordem alfabética</option>
                </select>
                <span aria-hidden="true">⌄</span>
            </label>
            <button class="button button-dark filter-button" type="submit">Buscar</button>
        </form>

        @if ($announcements->isNotEmpty())
            <div class="announcement-grid">
                @foreach ($announcements as $announcement)
                    <article class="announcement-card">
                        @if ($announcement->image_path)
                            <img class="card-image" src="{{ Storage::disk('public')->url($announcement->image_path) }}" alt="{{ $announcement->title }}" loading="lazy">
                        @else
                            <div class="card-image-placeholder" aria-hidden="true">
                                <span>{{ mb_strtoupper(mb_substr($announcement->category, 0, 1)) }}</span>
                                <i>✳</i>
                            </div>
                        @endif
                        <div class="card-content">
                            <div class="card-meta">
                                <span class="category-pill">{{ $announcement->category }}</span>
                                <time datetime="{{ $announcement->created_at->toDateString() }}">{{ $announcement->created_at->format('d/m/Y') }}</time>
                            </div>
                            <h3>{{ $announcement->title }}</h3>
                            <p class="card-description">{{ $announcement->description }}</p>
                            <div class="offer-note"><span aria-hidden="true">✦</span> {{ $announcement->offer_info }}</div>
                            <div class="card-contact">
                                @if ($announcement->contact_url)
                                    <a class="button button-contact" href="{{ $announcement->contact_url }}" @if (! str_starts_with($announcement->contact_url, 'mailto:')) target="_blank" rel="noopener noreferrer" @endif>
                                        Falar com anunciante <span aria-hidden="true">↗</span>
                                    </a>
                                @else
                                    <span class="contact-copy">Contato: {{ $announcement->contact }}</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="pagination-wrap">{{ $announcements->links() }}</div>
        @else
            <div class="empty-state">
                <span class="empty-icon" aria-hidden="true">⌕</span>
                <h3>{{ $search || $category ? 'Nenhum anúncio encontrado' : 'O mural está começando!' }}</h3>
                <p>{{ $search || $category ? 'Experimente outro termo ou escolha uma categoria diferente.' : 'Seja a primeira pessoa a compartilhar uma oferta ou serviço com a comunidade.' }}</p>
                @if ($search || $category)
                    <a class="button button-outline" href="{{ route('announcements.index') }}">Limpar filtros</a>
                @else
                    <a class="button button-accent" href="{{ route('announcements.create') }}">＋ Publique o primeiro anúncio</a>
                @endif
            </div>
        @endif
    </section>
@endsection
