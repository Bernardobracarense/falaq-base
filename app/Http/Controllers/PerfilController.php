<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PerfilController extends Controller
{
    public function buscarAvatar(string $username): JsonResponse
    {
        $fallback = 'https://ui-avatars.com/api/?name=' . urlencode($username);
        $avatarUrl = $fallback;

        try {
            $response = Http::timeout(3)->get("https://api.github.com/users/{$username}");

            if ($response->successful()) {
                $githubAvatar = $response->json('avatar_url');
                if (is_string($githubAvatar) && $githubAvatar !== '') {
                    $avatarUrl = $githubAvatar;
                }
            }
        } catch (ConnectionException $exception) {
            Log::warning('GitHub avatar lookup failed.', [
                'username' => $username,
                'exception' => $exception->getMessage(),
            ]);
        }

        return response()->json(['avatar' => $avatarUrl]);
    }
}
