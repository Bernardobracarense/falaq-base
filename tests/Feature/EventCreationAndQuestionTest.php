<?php

use App\Models\Evento;
use App\Models\Pergunta;
use App\Models\User;

it('validates question submissions and creates private questions for the authenticated user', function () {
    $user = User::factory()->create();
    $evento = Evento::factory()->create();

    $this->actingAs($user)
        ->postJson(route('eventos.perguntas.store', $evento))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['texto', 'evento_id']);

    $this->post(route('eventos.perguntas.store', $evento), [
        'evento_id' => $evento->id,
        'texto' => 'Como funciona a moderação?',
    ])->assertRedirect(route('eventos.show', $evento));

    $this->assertDatabaseHas('perguntas', [
        'evento_id' => $evento->id,
        'user_id' => $user->id,
        'texto' => 'Como funciona a moderação?',
        'is_public' => false,
    ]);
});

it('creates an event with the authenticated owner and required description', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('eventos.create'))
        ->assertOk()
        ->assertSee('max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md');

    $this->post(route('eventos.store'), [
        'titulo' => 'Evento de tecnologia',
    ])->assertSessionHasErrors(['descricao']);

    $this->post(route('eventos.store'), [
        'titulo' => 'Evento de tecnologia',
        'descricao' => 'Uma apresentação sobre desenvolvimento web.',
    ])->assertRedirect();

    $this->assertDatabaseHas('eventos', [
        'titulo' => 'Evento de tecnologia',
        'user_id' => $user->id,
    ]);
});

it('only lists public questions on the event page', function () {
    $evento = Evento::factory()->create();
    $publicQuestion = Pergunta::factory()->for($evento)->create([
        'texto' => 'Pergunta aprovada e visível.',
        'is_public' => true,
    ]);
    $pendingQuestion = Pergunta::factory()->for($evento)->create([
        'texto' => 'Pergunta pendente e oculta.',
        'is_public' => false,
    ]);

    $this->get(route('eventos.show', $evento))
        ->assertOk()
        ->assertSee($publicQuestion->texto)
        ->assertDontSee($pendingQuestion->texto);
});
