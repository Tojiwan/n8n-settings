<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function show(Business $business)
    {
        $bot = $business->bot; // hasOne relation
        $webhookUrl = null;

        if ($bot && $bot->enabled && $bot->n8n_webhook_path) {
            $webhookUrl = rtrim(config('services.n8n.base_url'), '/') . '/webhook/' . $bot->n8n_webhook_path;
        }

        return view('site.show', [
            'business'   => $business,
            'webhookUrl' => $webhookUrl, // null if not enabled
        ]);
    }
}
