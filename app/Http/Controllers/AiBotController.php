<?php

// app/Http/Controllers/AiBotController.php
namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\AiBot;
use App\Models\AiContext;
use App\Services\N8nService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiBotController extends Controller
{
    public function store(Request $req, N8nService $n8n)
    {
        $businessId = (int) $req->integer('businessId');
        $biz = Business::findOrFail($businessId);

        $bot = AiBot::firstOrNew(['business_id' => $businessId]);
        if ($bot->enabled) {
            return response()->json([
                'message' => 'Already enabled',
                'workflowId' => $bot->n8n_workflow_id,
                'webhookUrl' => config('services.n8n.base_url') . '/webhook/' . $bot->n8n_webhook_path
            ]);
        }

        $secret = Str::random(10);
        [$workflowId, $path] = $n8n->createWorkflow($biz, $secret);

        $bot->fill([
            'enabled' => false,
            'n8n_workflow_id' => $workflowId,
            'n8n_webhook_path' => $path,
            'n8n_webhook_secret' => $secret
        ])->save();

        AiContext::firstOrCreate(
            ['business_id' => $businessId],
            [
                'goal' => 'Convert visitors into qualified leads for ' . $biz->name,
                'context' => "Brand voice: friendly Taglish. Offer: free consult. Hours: 9am-6pm.\nFAQs: pricing, domains, hosting.",
                'guardrails' => "No legal/medical advice. If unsure, ask for email/phone.",
                'system' => "You are an assistant for {$biz->name}."
            ]
        );

        return response()->json([
            'message' => 'Bot enabled',
            'workflowId' => $workflowId,
            'webhookUrl' => config('services.n8n.base_url') . '/webhook/' . $path
        ], 201);
    }

    public function updateContext($businessId, Request $req)
    {
        $data = $req->validate([
            'goal' => 'required|string',
            'context' => 'required|string',
            'guardrails' => 'nullable|string',
            'system' => 'nullable|string',
        ]);
        $ctx = AiContext::updateOrCreate(['business_id' => (int)$businessId], $data);

        // Notify n8n webhook server-side (if a bot/workflow exists)
        // $bot = AiBot::where('business_id', (int)$businessId)->first();
        // if ($bot && $bot->n8n_webhook_path) {
        //     $webhook = rtrim(config('services.n8n.base_url'), '/') . '/webhook-test/' . $bot->n8n_webhook_path;
        //     Http::asJson()->post($webhook, [
        //         'event'      => 'context_updated',
        //         'businessId' => (int)$businessId,
        //         'goal'       => $data['goal'],
        //         'context'    => $data['context'],
        //         'guardrails' => $data['guardrails'] ?? '',
        //         'system'     => $data['system'] ?? '',
        //     ])->throw();
        // }

        return response()->json(['message' => 'Context updated', 'context' => $ctx]);
    }

    // n8n calls this (bearer protected)
    public function getContext($identifier)
    {
        // If numeric → treat as ID; otherwise slug
        $business = ctype_digit((string)$identifier)
            ? Business::findOrFail((int)$identifier)
            : Business::where('name_slug', $identifier)->firstOrFail();

        $ctx = AiContext::where('business_id', $business->id)->first();

        return response()->json([
            'goal'       => $ctx->goal ?? '',
            'context'    => $ctx->context ?? '',
            'guardrails' => $ctx->guardrails ?? '',
            'system'     => $ctx->system ?? '',
        ]);
    }

    public function destroy($businessId)
    {
        $bot = AiBot::where('business_id', (int)$businessId)->firstOrFail();
        $bot->update(['enabled' => false]); // (optional) also DELETE workflow via n8n API
        return response()->json(['message' => 'Bot disabled']);
    }

    public function activate($id)
{
    $bot = AiBot::where('business_id', (int)$id)->firstOrFail();
    if (!$bot->n8n_workflow_id) {
        return response()->json(['message' => 'No workflow to activate. Enable the bot first.'], 422);
    }

    $base = rtrim(config('services.n8n.base_url'), '/');
    $resp = Http::withHeaders(['X-N8N-API-KEY' => config('services.n8n.api_key')])
        ->post($base . "/api/v1/workflows/{$bot->n8n_workflow_id}/activate");

    if ($resp->failed()) {
        return response()->json(['message' => 'n8n activation failed', 'n8n' => $resp->json()], 502);
    }

    $bot->update(['enabled' => true]);
    return response()->json(['message' => 'Workflow activated', 'enabled' => true]);
}

    public function deactivate($id)
    {
        $bot = AiBot::where('business_id', (int)$id)->firstOrFail();
        if (!$bot->n8n_workflow_id) {
            return response()->json(['message' => 'No workflow to deactivate.'], 422);
        }

        $base = rtrim(config('services.n8n.base_url'), '/');
        $resp = Http::withHeaders(['X-N8N-API-KEY' => config('services.n8n.api_key')])
            ->post($base . "/api/v1/workflows/{$bot->n8n_workflow_id}/deactivate");

        if ($resp->failed()) {
            return response()->json(['message' => 'n8n deactivation failed', 'n8n' => $resp->json()], 502);
        }

        $bot->update(['enabled' => false]);
        return response()->json(['message' => 'Workflow deactivated', 'enabled' => false]);
    }
}
