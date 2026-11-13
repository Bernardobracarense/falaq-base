<?php

namespace App\Http\Controllers;

use App\Enums\EventoStatus;
use App\Http\Requests\EventoFormRequest;
use App\Models\Evento;
use App\Models\Pergunta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class EventoController extends Controller
{
    public function index(Request $request)
    {
        $eventos = Evento::where('status', $request->status ?? 'publicado')->get();
        $title = $request->status === EventoStatus::PUBLICADO->value ? "Ativos" : "Encerrados";
        return view('eventos.index', compact('eventos', 'title'));
    }

    public function userEventos()
    {
        $eventos = Auth::user()->eventos;
        return view('eventos.index', compact('eventos'));
    }

    public function show($id)
    {
        $evento = Evento::findOrFail($id);
        $perguntas = Pergunta::where('evento_id', $evento->id)
            ->where('is_public', true)
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('eventos.show', compact('evento', 'perguntas'));
    }

    public function create()
    {
        return view('eventos.create');
    }

    public function store(EventoFormRequest $request)
    {
        $evento = Evento::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('eventos.show', $evento->id);
    }

    public function updateStatus(Request $request, Evento $evento)
{
    // dd($request, $evento);
    $request->validate([
        'status' => ['required', new Enum(EventoStatus::class)],
    ]);

    $evento->update([
        'status' => $request->status,
    ]);

    return back()->with('status', 'Status do evento atualizado com sucesso!');
}
}
