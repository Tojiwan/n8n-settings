@extends('layouts.app')

@section('content')
    <div class="container my-4 py-2">

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="m-0">AI Studio — {{ $business->name }}</h3>
            @if ($webhookUrl)
                <span class="badge text-bg-light">Webhook ready</span>
            @else
                <span class="badge text-bg-warning text-dark">Enable & Activate the bot to test</span>
            @endif
        </div>

        @if ($webhookUrl)
            <div class="alert alert-secondary small">
                <div class="fw-semibold">Webhook URL</div>
                <code class="text-break">{{ $webhookUrl }}</code>
            </div>
        @endif

        <div class="row g-4">
            {{-- LEFT: Editor --}}
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <label class="form-label fw-semibold">Tell AI what to do (Goal)</label>
                        <input id="goal" class="form-control mb-3" value="{{ $ctx->goal ?? '' }}"
                            placeholder="Set a goal for the conversation">

                        <label class="form-label fw-semibold">Give AI context</label>
                        <textarea id="context" class="form-control mb-3" rows="8" style="resize:none" placeholder="Share all the info">{{ $ctx->context ?? '' }}</textarea>

                        {{-- <details class="mb-3">
                            <summary class="fw-semibold" style="cursor:pointer">Advanced (optional)</summary>
                            <label class="form-label mt-3">Guardrails</label>
                            <textarea id="guardrails" class="form-control mb-3" rows="3" style="resize:none">{{ $ctx->guardrails ?? '' }}</textarea>
                            <label class="form-label">System</label>
                            <textarea id="system" class="form-control mb-3" rows="3" style="resize:none">{{ $ctx->system ?? '' }}</textarea>
                        </details> --}}

                        <div class="d-flex gap-2">
                            <button id="saveBtn" class="btn btn-primary">Save</button>
                            <button id="fillDemoBtn" type="button" class="btn btn-outline-secondary">Fill demo</button>
                        </div>
                        <div id="status" class="mt-3 small text-muted"></div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Test Chat --}}
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column" style="min-height:480px">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="m-0">Test the chatbot</h6>
                            <div class="btn-group btn-group-sm">
                                <button id="clearChatBtn" class="btn btn-outline-secondary">Clear</button>
                                <button id="helloBtn" class="btn btn-outline-secondary">Send “Hello”</button>
                            </div>
                        </div>

                        <div id="chatLog" class="border rounded p-2 bg-light flex-grow-1 mb-2 overflow-auto"
                            style="max-height:420px">
                            <div class="text-muted small">Chat preview will appear here.</div>
                        </div>

                        <form id="chatForm" class="d-flex gap-2">
                            <input id="chatInput" class="form-control" placeholder="Type a message and hit Enter…"
                                autocomplete="off">
                            <button id="chatSend" class="btn btn-primary" type="submit">Send</button>
                        </form>

                        <div id="chatHint" class="small text-muted mt-2">
                            @if ($webhookUrl)
                                Messages are sent to your bot’s webhook above.
                            @else
                                <span class="text-danger">Webhook not available yet. Enable & activate the bot first.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const businessId = {{ $business->id }};
        const apiBase = "{{ url('/api') }}";
        const webhookUrl = @json($webhookUrl);

        // ---------- Helpers ----------
        function setStatus(msg, ok = true) {
            const el = document.getElementById('status');
            el.textContent = msg;
            el.className = 'mt-3 small ' + (ok ? 'text-success' : 'text-danger');
        }

        function addBubble(who, text) {
            const log = document.getElementById('chatLog');
            const wrap = document.createElement('div');
            wrap.className = 'd-flex mb-2 ' + (who === 'You' ? 'justify-content-end' : 'justify-content-start');
            const b = document.createElement('div');
            b.className = 'p-2 rounded ' + (who === 'You' ? 'bg-primary text-white' : 'bg-white border');
            b.style.maxWidth = '80%';
            b.innerHTML = `<div class="small fw-semibold mb-1">${who}</div><div>${escapeHtml(text)}</div>`;
            wrap.appendChild(b);
            log.appendChild(wrap);
            log.scrollTop = log.scrollHeight;
        }

        function typing(on) {
            const log = document.getElementById('chatLog');
            let t = document.getElementById('typing');
            if (on) {
                if (!t) {
                    t = document.createElement('div');
                    t.id = 'typing';
                    t.className = 'text-muted small fst-italic mb-2';
                    t.textContent = 'Bot is typing…';
                    log.appendChild(t);
                }
            } else if (t) {
                t.remove();
            }
            log.scrollTop = log.scrollHeight;
        }

        function escapeHtml(s) {
            return (s || '').replace(/[&<>"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [m]));
        }

        // ---------- Save Goal/Context ----------
        document.getElementById('saveBtn').addEventListener('click', async () => {
            const body = {
                goal: document.getElementById('goal').value.trim(),
                context: document.getElementById('context').value.trim(),
                guardrails: document.getElementById('guardrails')?.value.trim(),
                system: document.getElementById('system')?.value.trim(),
            };
            if (!body.goal || !body.context) return setStatus('Goal and Context are required.', false);

            try {
                const res = await fetch(`${apiBase}/ai/bots/${businessId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(body)
                });
                const data = await res.json();
                if (!res.ok) return setStatus(data.message || 'Failed to save', false);
                setStatus('Saved ✓');
            } catch (e) {
                setStatus('Network error: ' + e.message, false);
            }
        });

        // Demo filler
        document.getElementById('fillDemoBtn').addEventListener('click', () => {
            document.getElementById('goal').value =
                'Convert visitors into qualified leads for {{ $business->name }}';
            document.getElementById('context').value =
                'Brand voice: friendly Taglish. Offer: free consult. Hours: 9am–6pm.\nFAQs: pricing, domains, hosting.';
        });

        // ---------- Chat Tester ----------
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');

        document.getElementById('clearChatBtn').addEventListener('click', () => {
            document.getElementById('chatLog').innerHTML =
                '<div class="text-muted small">Chat cleared.</div>';
        });

        document.getElementById('helloBtn').addEventListener('click', (e) => {
            e.preventDefault();
            chatInput.value = 'Hello!';
            chatForm.dispatchEvent(new Event('submit', {
                cancelable: true
            }));
        });

        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const msg = (chatInput.value || '').trim();
            if (!msg) return;
            if (!webhookUrl) {
                addBubble('System', 'Webhook not available yet. Enable & activate the bot first.');
                return;
            }

            // NEW: include editor values in the POST
            const goal = document.getElementById('goal').value.trim();
            const context = document.getElementById('context').value.trim();
            // const guardrails = (document.getElementById('guardrails')?.value || '').trim();
            // const system = (document.getElementById('system')?.value || '').trim();

            addBubble('You', msg);
            chatInput.value = '';
            typing(true);

            try {
                const r = await fetch(webhookUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        message: msg,
                        goal,
                        context,
                        // guardrails,
                        // system,
                        webhookUrl
                    })
                });

                const ct = r.headers.get('content-type') || '';
                const j = ct.includes('application/json') ? await r.json() : {
                    reply: await r.text()
                };
                typing(false);

                if (!r.ok) {
                    addBubble('System', `Error ${r.status}: ${j.message || j.reply || 'Request failed'}`);
                    return;
                }

                addBubble('Bot', j.reply || '(no reply)');
            } catch (err) {
                typing(false);
                addBubble('System', 'Network error: ' + err.message);
            }
        });
    </script>
@endsection
