@extends('layouts.app')

@section('title', 'Moderação — ' . $evento->titulo)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Fila de moderação: {{ $evento->titulo }}</h1>
    <a href="{{ route('eventos.show', $evento) }}" class="btn btn-outline-secondary">Voltar ao evento</a>
</div>

@forelse($moderacoes as $moderacao)
    <article class="card mb-3">
        <div class="card-body">
            <p class="mb-2">{{ $moderacao->pergunta->texto }}</p>
            <p class="text-secondary mb-2">Motivo: {{ $moderacao->motivo }}</p>
            <p class="text-secondary">Status: {{ $moderacao->status }}</p>
            <div class="d-flex gap-2">
                <form action="{{ route('moderacoes.status', $moderacao) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="aprovado">
                    <button type="submit" class="btn btn-success">Aprovar</button>
                </form>
                <form action="{{ route('moderacoes.status', $moderacao) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="rejeitado">
                    <button type="submit" class="btn btn-danger">Rejeitar</button>
                </form>
            </div>
        </div>
    </article>
@empty
    <div class="alert alert-secondary">Nenhuma denúncia pendente para este evento.</div>
@endforelse

<div class="d-flex justify-content-center mt-4">
    {{ $moderacoes->links() }}
</div>
@endsection
