<?php

namespace App\Jobs;

use App\Models\DailyPrompt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class GenerateDailyPrompts implements ShouldQueue
{
    use Queueable;

    private const FALLBACK_PROMPTS = [
        "Qu'est-ce qui t'a fait sourire aujourd'hui ?",
        "Quel est le moment le plus calme de ta journée ?",
        "De quoi es-tu reconnaissant(e) en ce moment ?",
        "Qu'est-ce qui a occupé ton esprit aujourd'hui ?",
        "Y a-t-il quelque chose que tu aimerais partager ?",
        "Quel petit succès as-tu célébré aujourd'hui ?",
        "As-tu appris quelque chose de nouveau aujourd'hui ?",
        "Comment décrirais-tu ton niveau d'énergie aujourd'hui ?",
        "Qu'est-ce qui t'a inspiré(e) récemment ?",
        "Si tu pouvais revivre un moment d'aujourd'hui, lequel serait-ce ?",
        "Quel défi as-tu rencontré aujourd'hui ?",
        "As-tu passé du temps avec quelqu'un d'important pour toi ?",
        "Qu'est-ce que tu ferais différemment aujourd'hui si tu le pouvais ?",
        "Quel moment inattendu a égayé ta journée ?",
        "As-tu pris soin de toi aujourd'hui ? Comment ?",
        "Qu'est-ce que tu attends avec impatience ?",
        "Quel est le repas que tu as le plus apprécié aujourd'hui ?",
        "As-tu écouté une chanson qui t'a touché(e) ?",
        "Qu'est-ce qui te semble important en ce moment ?",
        "Si aujourd'hui était un chapitre d'un livre, quel serait son titre ?",
    ];

    public function handle(): void
    {
        $prompts = $this->fetchFromAi();

        $source = 'ai';

        if (empty($prompts)) {
            $source = 'fallback';
            $keys = array_rand(self::FALLBACK_PROMPTS, min(7, count(self::FALLBACK_PROMPTS)));
            $keys = is_array($keys) ? $keys : [$keys];
            $prompts = array_map(fn ($k) => self::FALLBACK_PROMPTS[$k], $keys);
            $prompts = array_values($prompts);
        }

        $date = now()->toDateString();
        $existing = DailyPrompt::whereDate('date', $date)->count();

        if ($existing > 0) {
            return;
        }

        foreach ($prompts as $content) {
            DailyPrompt::create([
                'content' => $content,
                'date' => $date,
                'source' => $source,
            ]);
        }
    }

    private function fetchFromAi(): array
    {
        $apiKey = config('services.groq.key') ?: env('GROQ_API_KEY');

        if (!$apiKey) {
            return [];
        }

        try {
            $response = Http::timeout(30)
                ->withHeader('Authorization', "Bearer $apiKey")
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Tu es un assistant bienveillant qui aide les utilisateurs à tenir un journal d\'humeur (mood journal). Génère exactement 7 questions douces et variées en français pour les inviter à réfléchir à leur journée. Les questions doivent être courtes, bienveillantes et ouvertes. Réponds uniquement avec un tableau JSON valide, rien d\'autre. Exemple: ["Question 1?", "Question 2?"]',
                        ],
                    ],
                    'temperature' => 0.9,
                    'max_tokens' => 500,
                ]);

            if (!$response->successful()) {
                return [];
            }

            $body = $response->json();
            $content = $body['choices'][0]['message']['content'] ?? '';

            $prompts = json_decode($content, true);

            if (!is_array($prompts) || count($prompts) === 0) {
                return [];
            }

            return array_slice($prompts, 0, 7);
        } catch (\Throwable) {
            return [];
        }
    }
}
