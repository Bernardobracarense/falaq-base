<?php

use App\Models\Evento;
use App\Models\Pergunta;
use App\Models\User;

it('redirects guests to login when voting', function () {
    $pergunta = Pergunta::factory()->create();

    $this->post(route('perguntas.votar', $pergunta))
        ->assertRedirect(route('login.create'));
});

it('adds a vote to the pivot table', function () {
    $user = User::factory()->create();
    $pergunta = Pergunta::factory()->create();

    $this->actingAs($user)
        ->post(route('perguntas.votar', $pergunta))
        ->assertRedirect();

    $this->assertDatabaseHas('pergunta_user', [
        'pergunta_id' => $pergunta->id,
        'user_id' => $user->id,
    ]);
});

it('removes a vote when voting a second time', function () {
    $user = User::factory()->create();
    $pergunta = Pergunta::factory()->create();

    $this->actingAs($user)
        ->post(route('perguntas.votar', $pergunta))
        ->assertRedirect();
    $this->post(route('perguntas.votar', $pergunta))
        ->assertRedirect();

    $this->assertDatabaseMissing('pergunta_user', [
        'pergunta_id' => $pergunta->id,
        'user_id' => $user->id,
    ]);
});

it('returns the updated vote count for JSON requests', function () {
    $user = User::factory()->create();
    $pergunta = Pergunta::factory()->create();

    $this->actingAs($user)
        ->postJson(route('perguntas.votar', $pergunta))
        ->assertOk()
        ->assertJson([
            'votos_count' => 1,
            'votado' => true,
        ]);
});

it('ranks public event questions by votes with latest as the tiebreaker', function () {
    $evento = Evento::factory()->create();
    $topQuestion = Pergunta::factory()->for($evento)->create([
        'created_at' => now()->subDay(),
    ]);
    $recentQuestion = Pergunta::factory()->for($evento)->create();

    foreach (range(1, 2) as $index) {
        $voter = User::factory()->create();
        $topQuestion->votos()->attach($voter);
    }

    $recentQuestion->votos()->attach(User::factory()->create());

    $this->get(route('eventos.show', $evento))
        ->assertOk()
        ->assertViewHas('perguntas', function ($perguntas) use ($topQuestion, $recentQuestion): bool {
            return $perguntas->pluck('id')->all() === [$topQuestion->id, $recentQuestion->id];
        });
});
