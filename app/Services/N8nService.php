<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Business;

class N8nService
{
    /**
     * Create an n8n workflow for a business.
     * Returns [workflowId, webhookPath]
     */
    public function createWorkflow(Business $business, string $secret): array
    {
        $base = rtrim(config('services.n8n.base_url'), '/');
        $slug = $business->name_slug;
        $path = "ai/{$slug}/{$secret}";

        $model    = config('services.n8n.openai_model', 'gpt-4o-mini');
        $credId   = config('services.n8n.openai_cred_id'); // must exist in your n8n
        if (!$credId) {
            throw new \RuntimeException('N8N_OPENAI_CRED_ID is not set.');
        }

        // Build workflow: Webhook → AI Agent (uses OpenAI Chat Model) → Set(reply) → Respond
        $workflow = [
            "name"  => "AI - {$business->name}",
            "nodes" => [
                [
                    "id"          => "webhook",
                    "name"        => "Incoming Webhook",
                    "type"        => "n8n-nodes-base.webhook",
                    "typeVersion" => 1,
                    "position"    => [520, 320],
                    "parameters"  => [
                        "httpMethod"   => "POST",
                        "path"         => $path,
                        "responseMode" => "lastNode",
                        "options"      => (object)[],
                    ],
                ],

                // OpenAI Chat Model wired into the Agent
                [
                    "id"          => "openai-chat-model",
                    "name"        => "OpenAI Chat Model",
                    "type"        => "@n8n/n8n-nodes-langchain.lmChatOpenAi",
                    "typeVersion" => 1.2,
                    "position"    => [680, 600],
                    "parameters"  => [
                        "model"   => [
                            "__rl" => true,
                            "mode"  => "list",
                            "value" => $model,
                        ],
                        "options" => (object)[],
                    ],
                    "credentials" => [
                        "openAiApi" => [
                            "id"   => $credId,
                            "name" => "OpenAI account", // label in n8n; can be anything
                        ],
                    ],
                ],

                // AI Agent (LangChain)
                [
                    "id"          => "agent",
                    "name"        => "AI Agent",
                    "type"        => "@n8n/n8n-nodes-langchain.agent",
                    "typeVersion" => 2,
                    "position"    => [740, 320],
                    "parameters"  => [
                        "promptType" => "define",
                        // Pull the user message from the webhook payload
                        "text"       => '={{ $node["Incoming Webhook"].json.body.message }}',
                        "options"    => [
                            // Expect goal/context to be sent in the POST body (like your curl)
                            "systemMessage" =>
                                '=Your Goal is:\n{{ $node["Incoming Webhook"].json.body.goal }}\n\n' .
                                'Your Context is:\n{{ $node["Incoming Webhook"].json.body.context }}',
                        ],
                    ],
                ],

                // Normalize the agent's output to { reply: "..." }
                [
                    "id"          => "set-reply",
                    "name"        => "Set Reply",
                    "type"        => "n8n-nodes-base.set",
                    "typeVersion" => 2,
                    "position"    => [940, 320],
                    "parameters"  => [
                        "keepOnlySet" => true,
                        "values"      => [
                            "string" => [
                                [
                                    "name"  => "reply",
                                    // Agent may output different keys depending on version; guard for them
                                    "value" => '={{ $json.text || $json.output || $json.result || $json.response || "" }}',
                                ],
                            ],
                        ],
                    ],
                ],

                // Final HTTP response to whoever called the webhook
                [
                    "id"          => "respond",
                    "name"        => "Respond to Webhook",
                    "type"        => "n8n-nodes-base.respondToWebhook",
                    "typeVersion" => 1.4,
                    "position"    => [1100, 320],
                    "parameters"  => [
                        "respondWith"  => "json",
                        "responseBody" => '={{ { "reply": $json.reply } }}',
                        "options"      => (object)[],
                    ],
                ],
            ],

            "connections" => [
                "Incoming Webhook" => [
                    "main" => [[["node" => "AI Agent", "type" => "main", "index" => 0]]],
                ],
                "OpenAI Chat Model" => [
                    "ai_languageModel" => [[["node" => "AI Agent", "type" => "ai_languageModel", "index" => 0]]],
                ],
                "AI Agent" => [
                    "main" => [[["node" => "Set Reply", "type" => "main", "index" => 0]]],
                ],
                "Set Reply" => [
                    "main" => [[["node" => "Respond to Webhook", "type" => "main", "index" => 0]]],
                ],
            ],

            "settings" => [
                "saveExecutionProgress"    => true,
                "saveManualExecutions"     => true,
                "saveDataErrorExecution"   => "all",
                "saveDataSuccessExecution" => "all",
                "timezone"                 => "Asia/Manila",
            ],
        ];

        $resp = Http::withHeaders(['X-N8N-API-KEY' => config('services.n8n.api_key')])
            ->post($base . '/api/v1/workflows', $workflow)
            ->throw()
            ->json();

        return [$resp['id'] ?? null, $path];
    }
}
