<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class N8nWorkflowController extends Controller
{
    public function index()
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'X-N8N-API-KEY' => config('services.n8n.api_key'),
            'Authorization' => 'Bearer ' . config('services.n8n.api_key'),
        ])->get('https://n8n.tigernethost.com/api/v1/workflows');

        if (!$response->successful()) {
            abort(500, 'Failed to fetch workflows from n8n.');
        }

        $json = $response->json();
        $workflows = $json['data'] ?? [];

        return view('n8n.workflows', compact('workflows'));
    }

public function toggle($id, Request $request)
{
    try {
        $isActive = $request->input('active');

        $endpoint = $isActive
            ? "https://n8n.tigernethost.com/api/v1/workflows/{$id}/activate"
            : "https://n8n.tigernethost.com/api/v1/workflows/{$id}/deactivate";

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'X-N8N-API-KEY' => config('services.n8n.api_key'),
        ])->post($endpoint);

        if (!$response->successful()) {
            Log::error('Failed to toggle workflow: ' . $response->body());
            return response()->json(['success' => false], 500);
        }

        return response()->json(['success' => true]);

    } catch (\Throwable $e) {
        Log::error('Workflow toggle error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Server error'], 500);
    }
}
}
