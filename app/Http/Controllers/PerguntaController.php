<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePerguntaRequest;
use App\Models\Evento;
use App\Models\Pergunta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PerguntaController extends Controller
{
    public function store(StorePerguntaRequest $request, int $id): RedirectResponse
    {
        $evento = Evento::findOrFail($id);

        Pergunta::create([
            ...$request->validated(),
            'evento_id' => $evento->id,
            'user_id' => auth()->id(),
            'is_public' => false,
        ]);

        return redirect()->route('eventos.show', $evento)
            ->with('sucesso', 'Sua pergunta foi enviada com sucesso!');
    }

    public function votar(Request $request, Pergunta $pergunta): JsonResponse|RedirectResponse
    {
        $pergunta->votos()->toggle(auth()->id());

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'votos_count' => $pergunta->votos()->count(),
                'votado' => $pergunta->votos()->where('user_id', auth()->id())->exists(),
            ]);
        }

        return back()->with('status', 'Seu voto foi atualizado.');
    }
}
