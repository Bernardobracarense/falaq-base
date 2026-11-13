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
}
