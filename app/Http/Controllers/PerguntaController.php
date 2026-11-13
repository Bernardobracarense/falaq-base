<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePerguntaRequest;
use App\Models\Pergunta;
use Illuminate\Http\RedirectResponse;

class PerguntaController extends Controller
{
    public function store(StorePerguntaRequest $request): RedirectResponse
    {
        Pergunta::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
            'is_public' => false,
        ]);

        return redirect()->route('eventos.show', $request->validated('evento_id'))
            ->with('sucesso', 'Sua pergunta foi enviada com sucesso!');
    }

    public function votar(Pergunta $pergunta): RedirectResponse
    {
        $pergunta->votos()->toggle(auth()->id());

        return back()->with('status', 'Seu voto foi atualizado.');
    }
}
