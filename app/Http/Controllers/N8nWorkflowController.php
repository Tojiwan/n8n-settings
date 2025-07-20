<?php

namespace App\Http\Controllers;

use App\Models\WorkflowPrompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function edit($id)
    {
        $workflow = Http::withHeaders([
            'X-N8N-API-KEY' => config('services.n8n.api_key'),
        ])->get(config('services.n8n.base_url') . "/api/v1/workflows/{$id}")->json();

        $nodes = $workflow['nodes'];
        $aiAgentNode = collect($nodes)->firstWhere('type', '@n8n/n8n-nodes-langchain.agent');
        $systemPrompt = $aiAgentNode['parameters']['options']['systemMessage'] ?? '';

        return view('n8n.edit-node', compact('workflow', 'systemPrompt'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'system_prompt' => 'nullable|string',
        ]);

        try {
            // Fetch the current workflow from n8n
            $workflowResponse = Http::withHeaders([
                'X-N8N-API-KEY' => config('services.n8n.api_key'),
            ])->get(config('services.n8n.base_url') . "/api/v1/workflows/{$id}");

            if (!$workflowResponse->successful()) {
                Log::error('Failed to fetch workflow from n8n', [
                    'status' => $workflowResponse->status(),
                    'body' => $workflowResponse->body(),
                ]);
                return back()->with('error', 'Failed to fetch workflow from n8n.');
            }

            $workflow = $workflowResponse->json();
            Log::info("Updating Workflow ID {$id}", $workflow);

            // Modify AI Agent system prompt if present
            foreach ($workflow['nodes'] as &$node) {
                if ($node['type'] === '@n8n/n8n-nodes-langchain.agent') {
                    $node['parameters']['options']['systemMessage'] = $validated['system_prompt'];
                }
            }

            // Update workflow name
            $workflow['name'] = $validated['name'];

            // Clean the payload for n8n update
            $cleanWorkflow = [
                'name' => $workflow['name'] ?? '',
                'nodes' => $workflow['nodes'] ?? [],
                'connections' => $workflow['connections'] ?? [],
                'settings' => [],
            ];

            // Keep only allowed `settings` keys
            $allowedSettings = [
                "saveExecutionProgress",
                "saveManualExecutions",
                "saveDataErrorExecution",
                "saveDataSuccessExecution",
                "executionTimeout",
                "errorWorkflow",
                "timezone",
                "executionOrder"
            ];
            if (isset($workflow['settings'])) {
                $cleanWorkflow['settings'] = array_filter(
                    $workflow['settings'],
                    fn($key) => in_array($key, $allowedSettings),
                    ARRAY_FILTER_USE_KEY
                );
            }

            // Send PUT request to n8n
            $response = Http::withHeaders([
                'X-N8N-API-KEY' => config('services.n8n.api_key'),
                'accept' => 'application/json',
            ])->put(config('services.n8n.base_url') . "/api/v1/workflows/{$id}", $cleanWorkflow);

            Log::info('n8n API Response:', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if (!$response->successful()) {
                return back()->with('error', 'n8n API update failed: ' . $response->body());
            }

            DB::table('workflow_prompts')->updateOrInsert(
                ['workflow_id' => $id],
                [
                    'workflow_name' => $validated['name'],
                    'system_prompt' => $validated['system_prompt'] ?? '',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            return redirect('/admin/workflows')->with('success', 'Workflow updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update workflow: ' . $e->getMessage());
            return back()->with('error', 'Failed to update workflow.');
        }
    }
}
