@extends('layouts.app')

@section('title', $editing ? 'Editar anúncio' : 'Novo anúncio')

@section('content')
    <section class="form-section">
        <a class="back-link" href="{{ route('announcements.index') }}">← Voltar ao mural</a>
        <div class="form-heading">
            <span class="eyebrow">{{ $editing ? 'Atualize seu anúncio' : 'Compartilhe com a comunidade' }}</span>
            <h1>{{ $editing ? 'Editar anúncio' : 'O que você oferece?' }}</h1>
            <p>Conte para a vizinhança como seu produto ou serviço pode ajudar.</p>
        </div>

        @if ($errors->any())
            <div class="form-errors" role="alert">
                <strong>Confira os campos abaixo:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="announcement-form" action="{{ $editing ? route('announcements.update', [$announcement, $managementCode]) : route('announcements.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="field">
                <label for="title">Título do anúncio <span>*</span></label>
                <input id="title" name="title" type="text" maxlength="120" value="{{ old('title', $announcement->title) }}" placeholder="Ex.: Pães artesanais feitos em casa" required>
                @error('title') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="field">
                <label for="category">Categoria <span>*</span></label>
                <select id="category" name="category" required>
                    <option value="" disabled @selected(old('category', $announcement->category) === '')>Escolha uma categoria</option>
                    @foreach ($categories as $option)
                        <option value="{{ $option }}" @selected(old('category', $announcement->category) === $option)>{{ $option }}</option>
                    @endforeach
                </select>
                @error('category') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="field">
                <label for="description">Descrição <span>*</span></label>
                <textarea id="description" name="description" rows="5" maxlength="5000" placeholder="Descreva seu produto ou serviço, diferenciais e região de atendimento..." required>{{ old('description', $announcement->description) }}</textarea>
                <small class="field-hint">Até 5.000 caracteres.</small>
                @error('description') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="field">
                <label for="offer_info">Informação da oferta <span>*</span></label>
                <input id="offer_info" name="offer_info" type="text" maxlength="500" value="{{ old('offer_info', $announcement->offer_info) }}" placeholder="Ex.: Entrega grátis no bairro ou a partir de R$ 25" required>
                <small class="field-hint">Preço, condição especial, horário ou outra informação importante.</small>
                @error('offer_info') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="field">
                <label for="contact">Como entrar em contato <span>*</span></label>
                <input id="contact" name="contact" type="text" maxlength="255" value="{{ old('contact', $announcement->contact) }}" placeholder="WhatsApp, telefone, e-mail ou link" required>
                <small class="field-hint">Números de telefone geram um botão para conversar pelo WhatsApp.</small>
                @error('contact') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="field">
                <label for="image">Imagem do anúncio <span class="optional">(opcional)</span></label>
                @if ($editing && $announcement->image_path)
                    <div class="current-image">
                        <img src="{{ Storage::disk('public')->url($announcement->image_path) }}" alt="Imagem atual do anúncio">
                        <label class="remove-image"><input type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))> Remover imagem atual</label>
                    </div>
                @endif
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
                <div class="image-preview" id="image-preview" aria-live="polite"></div>
                <small class="field-hint">JPG, PNG ou WEBP. Tamanho máximo: 4 MB.</small>
                @error('image') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-actions">
                <a class="button button-outline" href="{{ route('announcements.index') }}">Cancelar</a>
                <button class="button button-accent" type="submit">{{ $editing ? 'Salvar alterações' : 'Publicar anúncio' }} <span aria-hidden="true">→</span></button>
            </div>
        </form>
        @if ($editing)
            <form class="delete-form" action="{{ route('announcements.destroy', [$announcement, $managementCode]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este anúncio?')">
                @csrf
                @method('DELETE')
                <button type="submit">Excluir este anúncio</button>
            </form>
        @endif
    </section>
@endsection
