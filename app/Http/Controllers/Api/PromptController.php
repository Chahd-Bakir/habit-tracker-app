<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateDailyPrompts;
use App\Models\DailyPrompt;
use Illuminate\Http\JsonResponse;

class PromptController extends Controller
{
    private const FALLBACK_PROMPTS = [
        "Qu'est-ce qui t'a fait sourire aujourd'hui ?",
        "Quel est le moment le plus calme de ta journée ?",
        "De quoi es-tu reconnaissant(e) en ce moment ?",
        "Qu'est-ce qui a occupé ton esprit aujourd'hui ?",
        "Y a-t-il quelque chose que tu aimerais partager ?",
        "Quel petit succès as-tu célébré aujourd'hui ?",
        "As-tu appris quelque chose de nouveau aujourd'hui ?",
    ];

    public function today(): JsonResponse
    {
        $prompt = DailyPrompt::whereDate('date', now()->toDateString())
            ->inRandomOrder()
            ->first();

        if (!$prompt) {
            dispatch_sync(new GenerateDailyPrompts);

            $prompt = DailyPrompt::whereDate('date', now()->toDateString())
                ->inRandomOrder()
                ->first();
        }

        if (!$prompt) {
            $fallback = self::FALLBACK_PROMPTS[array_rand(self::FALLBACK_PROMPTS)];

            return response()->json([
                'success' => true,
                'prompt' => [
                    'id' => null,
                    'content' => $fallback,
                    'date' => now()->toDateString(),
                    'source' => 'fallback',
                    'is_ai' => false,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'prompt' => [
                'id' => $prompt->id,
                'content' => $prompt->content,
                'date' => $prompt->date,
                'source' => $prompt->source,
                'is_ai' => $prompt->source === 'ai',
            ],
        ]);
    }
}
