<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModeracaoRequest;
use App\Models\Evento;
use App\Models\Moderacao;
use App\Models\Pergunta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModeracaoController extends Controller
{
    use AuthorizesRequests;

    public function store(StoreModeracaoRequest $request, Pergunta $pergunta): RedirectResponse
    {
        $pergunta->moderacoes()->create($request->validated());

        return back()->with('status', 'Denúncia enviada para moderação.');
    }

    public function index(Evento $evento): View
    {
        $this->authorize('moderate', $evento);

        $moderacoes = Moderacao::with('pergunta')
            ->whereHas('pergunta', fn ($query) => $query->where('evento_id', $evento->id))
            ->latest()
            ->paginate(15);

        return view('moderacoes.index', compact('evento', 'moderacoes'));
    }

    public function updateStatus(Request $request, Moderacao $moderacao): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:analisando,aprovado,rejeitado'],
        ]);

        $this->authorize('moderate', $moderacao->pergunta->evento);
        $moderacao->update($validated);

        return back()->with('status', 'Status da moderação atualizado.');
    }
}
