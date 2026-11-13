@extends('layouts.app')

@section('title', $evento->titulo . ' — FalaQ')

@section('content')
<div class="row">
    <!-- Formularço de envio de Pergunta -->
    @can('update', $evento)
        <form action="{{ route('eventos.status', $evento->id) }}" method="POST">
            @csrf
            @method('patch')
            {{-- <input type="text" name="status" value="{{ $evento->isPublicado() ? EventoStatus::ENCERRADO : EventoStatus::PUBLICADO }}"> --}}
            <input type="text" name="status" value="{{ $evento->isPublicado() ? 'encerrado' : 'publicado' }}" hidden>
            <button type="submit" class="btn {{ $evento->isPublicado() ? 'bg-red-600' : 'bg-green-600' }}">{{ $evento->isPublicado() ? "Encerrar" : "Publicar" }}</button>
        </form>
    @endcan
    @can('moderate', $evento)
        <a href="{{ route('moderacoes.index', $evento) }}" class="btn btn-secondary mb-3">Fila de moderação</a>
    @endcan
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm p-3">
            <h4 class="fw-bold mb-3">💬 Faça sua Pergunta</h4>
            <form action="{{ route('eventos.perguntas.store', $evento->id) }}" method="POST">
                @csrf
                <input type="hidden" name="evento_id" value="{{ $evento->id }}">
                <div class="mb-3">
                    <label for="texto" class="form-label text-secondary">Texto da Pergunta</label>

                    <textarea name="texto" id="texto" rows="4" 
                              class="form-control bg-dark text-white border-secondary @error('texto') is-invalid @enderror"
                              placeholder="Digite sua dúvida ou comentário para o palestrante..."></textarea>

                    @error('texto')
                        <div class="invalid-feedback fw-bold">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold">Enviar Pergunta</button>
            </form>
        </div>
    </div>

    <!-- Lista de Perguntas (TICKET #002) -->
    <div class="col-md-7">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold m-0">📋 Perguntas do Evento</h4>
            <span class="text-secondary small">Total no Banco: {{ $perguntas->total() }}</span>
        </div>

        @forelse($perguntas as $pergunta)
            <div class="card mb-3 shadow-sm border-start border-4 border-primary">
                <div class="card-body">
                    <p class="fs-5 mb-2 text-white">{{ $pergunta->texto }}</p>
                    <div class="d-flex justify-content-between align-items-center text-secondary small">
                        <span class="d-flex align-items-center gap-2">
                            <img
                                class="question-avatar d-none rounded-circle"
                                width="32"
                                height="32"
                                alt=""
                                data-username="{{ $pergunta->user->github_username ?? '' }}"
                            >
                            {{ $pergunta->user->name ?? 'Anônimo' }}
                        </span>
                        <span>Status: <span class="badge bg-success">{{ $pergunta->status }}</span></span>
                        <span>{{ $pergunta->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <form
                        action="{{ route('perguntas.votar', $pergunta) }}"
                        method="POST"
                        class="mt-3"
                        onsubmit="event.preventDefault(); votarPergunta({{ $pergunta->id }}, this.querySelector('button')); return false;"
                    >
                        @csrf
                        <button
                            type="submit"
                            title="Clique novamente para remover seu voto"
                            class="{{ $votadas->contains($pergunta->id) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' }} px-3 py-1 rounded-md"
                        >↑ <span class="vote-count">{{ $pergunta->votos_count }}</span></button>
                    </form>
                    <p class="vote-error text-danger small mt-1" data-vote-error role="status"></p>
                    <details class="mt-3">
                        <summary class="text-sm cursor-pointer">Denunciar</summary>
                        <form action="{{ route('moderacoes.store', $pergunta) }}" method="POST" class="mt-2">
                            @csrf
                            <label for="motivo-{{ $pergunta->id }}" class="form-label">Motivo</label>
                            <textarea
                                name="motivo"
                                id="motivo-{{ $pergunta->id }}"
                                rows="2"
                                class="form-control @error('motivo') is-invalid @enderror"
                                required
                            >{{ old('motivo') }}</textarea>
                            @error('motivo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <button type="submit" class="btn btn-outline-warning btn-sm mt-2">Enviar denúncia</button>
                        </form>
                    </details>
                </div>
            </div>
        @empty
            <div class="alert alert-dark text-center p-4">
                Nenhuma pergunta enviada ainda. Seja o primeiro!
            </div>
        @endforelse

        <!-- TICKET #002: Renderização dos Botões de Paginação -->
        <div class="d-flex justify-content-center mt-4">
            {{ $perguntas->links() }}
        </div>
    </div>
</div>

<script>
    function votarPergunta(perguntaId, buttonElement) {
        const errorMessage = buttonElement.closest('form').parentElement.querySelector('[data-vote-error]');
        errorMessage.textContent = '';

        fetch(`/perguntas/${perguntaId}/votar`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
            .then(response => {
                if (response.status === 401 || response.status === 419) {
                    window.location.href = '/login';
                    return null;
                }

                if (!response.ok) {
                    throw new Error(`Não foi possível registrar o voto (${response.status}).`);
                }

                return response.json();
            })
            .then(data => {
                if (!data) {
                    return;
                }

                buttonElement.querySelector('.vote-count').textContent = data.votos_count;
                buttonElement.classList.toggle('bg-blue-600', data.votado);
                buttonElement.classList.toggle('text-white', data.votado);
                buttonElement.classList.toggle('bg-gray-200', !data.votado);
                buttonElement.classList.toggle('text-gray-800', !data.votado);
            })
            .catch(error => {
                console.error('Falha ao atualizar o voto.', error);
                errorMessage.textContent = 'Não foi possível registrar seu voto. Tente novamente.';
            });
    }

    document.querySelectorAll('img[data-username]').forEach(image => {
        const username = image.dataset.username;
        if (!username) {
            return;
        }

        fetch(`/perfil/${encodeURIComponent(username)}/avatar`, {
            headers: { 'Accept': 'application/json' }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Falha ao buscar avatar (${response.status}).`);
                }

                return response.json();
            })
            .then(data => {
                if (!data.avatar) {
                    throw new Error('A resposta de avatar não contém uma URL.');
                }

                image.src = data.avatar;
                image.classList.remove('d-none');
            })
            .catch(error => {
                console.error('Falha ao carregar o avatar.', error);
                image.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(username)}`;
                image.classList.remove('d-none');
            });
    });
</script>
@endsection
