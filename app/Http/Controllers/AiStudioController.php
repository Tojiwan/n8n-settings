<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\AiContext;

class AiStudioController extends Controller
{
    public function edit(Business $business)
    {
        $ctx = AiContext::where('business_id', $business->id)->first();
        $webhookUrl = optional($business->bot)->n8n_webhook_path
            ? rtrim(config('services.n8n.base_url'), '/') . '/webhook/' . $business->bot->n8n_webhook_path
            : null;

        return view('ai.studio', compact('business', 'ctx', 'webhookUrl'));
    }
}
