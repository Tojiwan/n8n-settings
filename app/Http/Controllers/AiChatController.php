<?php

// app/Http/Controllers/AiChatController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiChatController extends Controller
{
    public function generate(Request $req)
    {
        $data = $req->validate([
            'businessId' => 'required|integer',
            'userMessage' => 'required|string',
            'goal' => 'nullable|string',
            'context' => 'nullable|string',
            'guardrails' => 'nullable|string',
            'system' => 'nullable|string',
        ]);

        $system = trim(($data['system'] ?? 'You are a helpful business assistant.') . "\nGoal: " . ($data['goal'] ?? '') . "\nGuardrails: " . ($data['guardrails'] ?? ''));
        $prompt = "Business context:\n" . $data['context'] . "\n\nCustomer: " . $data['userMessage'];

        $r = Http::withToken(env('OPENAI_API_KEY'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => env('AI_MODEL', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.6
            ])->throw()->json();

        $reply = $r['choices'][0]['message']['content'] ?? 'Sorry, no reply.';
        return response()->json(['reply' => $reply]);
    }
}
