<?php

use App\Models\Evento;
use App\Models\Moderacao;
use App\Models\Pergunta;
use App\Models\User;

it('allows authenticated users to report a question', function () {
    $pergunta = Pergunta::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('moderacoes.store', $pergunta), [
            'motivo' => 'Esta pergunta contém conteúdo inadequado.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('moderacoes', [
        'pergunta_id' => $pergunta->id,
        'motivo' => 'Esta pergunta contém conteúdo inadequado.',
        'status' => 'analisando',
    ]);
});

it('restricts moderation queues and status changes to the event owner', function () {
    $owner = User::factory()->create();
    $evento = Evento::factory()->create(['user_id' => $owner->id]);
    $pergunta = Pergunta::factory()->for($evento)->create();
    $moderacao = Moderacao::create([
        'pergunta_id' => $pergunta->id,
        'motivo' => 'Esta pergunta contém conteúdo inadequado.',
    ]);
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->get(route('moderacoes.index', $evento))
        ->assertForbidden();

    $this->actingAs($owner)
        ->get(route('moderacoes.index', $evento))
        ->assertOk();

    $this->actingAs($otherUser)
        ->patch(route('moderacoes.status', $moderacao), ['status' => 'aprovado'])
        ->assertForbidden();
});
