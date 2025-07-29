<?php

namespace App\Http\Controllers;

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
        ])->get(config('services.n8n.base_url') . '/api/v1/workflows');

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
                ? config('services.n8n.base_url') . "/api/v1/workflows/{$id}/activate"
                : config('services.n8n.base_url') . "/api/v1/workflows/{$id}/deactivate";

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
            // Log::error('Workflow toggle error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function edit($id)
    {
        $workflowResponse = Http::withHeaders([
            'X-N8N-API-KEY' => config('services.n8n.api_key'),
        ])->get(config('services.n8n.base_url') . "/api/v1/workflows/{$id}");

        if (!$workflowResponse->successful()) {
            abort(500, 'Failed to fetch workflow from n8n.');
        }

        $workflow = $workflowResponse->json();

        // Filter nodes to AI Agent / OpenAI nodes, excluding sticky notes & disabled nodes
        // Filter nodes to only AI-related nodes (exclude sticky & disabled)
        $aiNodes = collect($workflow['nodes'] ?? [])
            ->filter(function ($node) {
                $type = $node['type'] ?? '';
                $disabled = $node['disabled'] ?? false; // <-- Safely check here
                $isAiType = in_array($type, [
                    '@n8n/n8n-nodes-langchain.agent',
                    '@n8n/n8n-nodes-langchain.openAi',
                ]);
                return $isAiType && !$disabled && $type !== '@n8n/n8n-nodes-base.stickyNote';
            })
            ->map(function ($node) {
                $prompt = '';

                // Case 1: OpenAI messages
                if ($node['type'] === '@n8n/n8n-nodes-langchain.openAi') {
                    $systemMsg = collect($node['parameters']['messages']['values'] ?? [])
                        ->firstWhere('role', 'system');
                    $prompt = $systemMsg['content'] ?? '';
                }
                // Case 2: Agent nodes with `text` or `systemMessage`
                elseif ($node['type'] === '@n8n/n8n-nodes-langchain.agent') {
                    $prompt = $node['parameters']['options']['systemMessage']
                        ?? $node['parameters']['text']
                        ?? '';
                }

                return [
                    'id' => $node['id'],
                    'name' => $node['name'] ?? 'AI Node',
                    'type' => $node['type'],
                    'prompt' => $prompt,
                ];
            })
            ->values()
            ->toArray();

        return view('n8n.edit-node', [
            'workflow' => $workflow,
            'nodes' => $aiNodes,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'node_id' => 'required|string',
            'system_prompt' => 'nullable|string',
        ]);

        try {
            $workflowResponse = Http::withHeaders([
                'X-N8N-API-KEY' => config('services.n8n.api_key'),
            ])->get(config('services.n8n.base_url') . "/api/v1/workflows/{$id}");

            if (!$workflowResponse->successful()) {
                return back()->with('error', 'Failed to fetch workflow from n8n.');
            }

            $workflow = $workflowResponse->json();
            $workflow['name'] = $validated['name'];

            // Update the selected node's prompt
            foreach ($workflow['nodes'] as &$node) {
                if ($node['id'] === $request->input('node_id')) {
                    // Log::info("Updating node: {$node['id']}", ['node' => $node]);

                    if ($node['type'] === '@n8n/n8n-nodes-langchain.agent') {
                        $systemPrompt = html_entity_decode($request->input('system_prompt'), ENT_QUOTES | ENT_HTML5);

                        if (!isset($node['parameters']['options']) || !is_array($node['parameters']['options'])) {
                            $node['parameters']['options'] = [];
                        }

                        // Update only systemMessage
                        $node['parameters']['options']['systemMessage'] = $systemPrompt;
                    } elseif ($node['type'] === '@n8n/n8n-nodes-langchain.openAi') {
                        $systemPrompt = $request->input('system_prompt');

                        if (!isset($node['parameters']['messages']['values']) || !is_array($node['parameters']['messages']['values'])) {
                            $node['parameters']['messages']['values'] = [];
                        }

                        $found = false;
                        foreach ($node['parameters']['messages']['values'] as &$msg) {
                            if (($msg['role'] ?? '') === 'system') {
                                $msg['content'] = $systemPrompt;
                                $found = true;
                            }
                        }
                        if (!$found) {
                            $node['parameters']['messages']['values'][] = [
                                'role' => 'system',
                                'content' => $systemPrompt
                            ];
                        }
                    }
                }
            }

            $cleanWorkflow = [
                'name' => $workflow['name'],
                'nodes' => $workflow['nodes'],
                'connections' => $workflow['connections'],
                'settings' => (object) ($workflow['settings'] ?? []),
            ];

            // Ensure settings only has allowed keys
            $allowedSettingsKeys = [
                'saveDataErrorExecution',
                'saveDataSuccessExecution',
                'saveManualExecutions',
                'executionTimeout',
                'timezone',
                'versionId'
            ];

            $cleanWorkflow['settings'] = (object) array_intersect_key(
                (array)$cleanWorkflow['settings'],
                array_flip($allowedSettingsKeys)
            );

            // Normalize nodes
            foreach ($cleanWorkflow['nodes'] as &$node) {
                // Remove read-only fields
                unset($node['webhookId']);

                if (!isset($node['parameters'])) {
                    $node['parameters'] = (object)[];
                } elseif (is_array($node['parameters'])) {
                    $node['parameters'] = (object) $node['parameters'];
                }

                // Fix OpenAI messages
                if (isset($node['parameters']->messages) && is_array($node['parameters']->messages)) {
                    $node['parameters']->messages = (object)[
                        'values' => $node['parameters']->messages['values'] ?? []
                    ];
                }
            }
            unset($node);

            // Log::info('Updated workflow payload', $cleanWorkflow);
            // dd(json_encode($cleanWorkflow, JSON_PRETTY_PRINT));

            $response = Http::withHeaders([
                'X-N8N-API-KEY' => config('services.n8n.api_key'),
                'accept' => 'application/json',
            ])->put(config('services.n8n.base_url') . "/api/v1/workflows/{$id}", $cleanWorkflow);
            // Log::info('n8n update response', [$response->status(), $response->body()]);

            if (!$response->successful()) {
                return back()->with('error', 'n8n API update failed: ' . $response->body());
            }

            $exists = DB::table('workflow_prompts')
                ->where('workflow_id', $id)
                ->where('node_id', $validated['node_id'])
                ->exists();

            if ($exists) {
                // Update the existing record
                DB::table('workflow_prompts')
                    ->where('workflow_id', $id)
                    ->where('node_id', $validated['node_id'])
                    ->update([
                        'workflow_name' => $validated['name'],
                        'system_prompt' => $validated['system_prompt'] ?? '',
                        'updated_at' => now(),
                    ]);
            } else {
                // Insert a new record only if not found
                DB::table('workflow_prompts')->insert([
                    'workflow_id'   => $id,
                    'node_id'       => $validated['node_id'],
                    'workflow_name' => $validated['name'],
                    'system_prompt' => $validated['system_prompt'] ?? '',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            return redirect('/admin/workflows')->with('success', 'Workflow updated successfully.');
        } catch (\Exception $e) {
            // Log::error('Failed to update workflow: ' . $e->getMessage());
            return back()->with('error', 'Failed to update workflow.');
        }
    }
}
