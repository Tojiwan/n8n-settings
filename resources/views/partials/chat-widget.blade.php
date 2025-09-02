@php
    $brand = $brand ?? 'Assistant';
    $webhook = $webhookUrl ?? null;
@endphp

@if ($webhook)
    <link rel="stylesheet" href="{{ asset('css/chat-widget.css') }}">

    <div id="cw-panel" class="cw-panel" aria-live="polite">
        <div class="cw-header">
            <div class="cw-title">{{ $brand }} Chat</div>
            <button class="cw-close" type="button" aria-label="Close">&times;</button>
        </div>
        <div id="cw-body" class="cw-body">
            <div class="cw-row cw-bot">
                <div class="cw-bubble">Hello! Ask me about pricing, services, or company info.</div>
            </div>
        </div>
        <div class="cw-quick">
            <button class="cw-chip" data-q="Who are you?">Who are you?</button>
            <button class="cw-chip" data-q="What do you do?">What do you do?</button>
            <button class="cw-chip" data-q="Do you have hosting plans?">Hosting plans</button>
        </div>
        <div class="cw-footer">
            <input id="cw-input" class="cw-input" placeholder="Type your query" autocomplete="off">
            <button id="cw-send" class="cw-send" type="button">Send</button>
        </div>
    </div>

    <button id="cw-launcher" class="cw-launcher" type="button" aria-label="Open chat">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z" />
        </svg>
    </button>

    <script>
        (() => {
            const WEBHOOK = @json($webhook);
            const BRAND = @json($brand);

            const panel = document.getElementById('cw-panel');
            const launch = document.getElementById('cw-launcher');
            const close = panel.querySelector('.cw-close');
            const body = document.getElementById('cw-body');
            const input = document.getElementById('cw-input');
            const sendBtn = document.getElementById('cw-send');

            // Helper: is the panel currently visible?
            const isOpen = () => panel.style.display === 'flex';

            function openChat() {
                if (isOpen()) return;
                panel.style.display = 'flex'; // set flex only when opening
                // ensure the body scroll area computes correctly after paint
                requestAnimationFrame(() => {
                    body.scrollTop = body.scrollHeight;
                    input.focus();
                });
            }

            function closeChat() {
                if (!isOpen()) return;
                panel.style.display = 'none';
                typing(false);
            }

            // Toggle on launcher click
            launch.addEventListener('click', () => {
                isOpen() ? closeChat() : openChat();
            });

            // Explicit close
            close.addEventListener('click', closeChat);

            // ESC to close
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeChat();
            });

            // Disable send if empty
            const toggleSend = () => sendBtn.disabled = !input.value.trim();
            input.addEventListener('input', toggleSend);
            toggleSend();

            // Quick chips
            panel.querySelectorAll('.cw-chip').forEach(chip => {
                chip.addEventListener('click', () => {
                    input.value = (chip.dataset.q || '').trim();
                    toggleSend();
                    doSend();
                });
            });

            function addBubble(who, text) {
                const row = document.createElement('div');
                row.className = 'cw-row ' + (who === 'You' ? 'cw-you you' : 'cw-bot');
                const b = document.createElement('div');
                b.className = 'cw-bubble';
                b.textContent = text;
                row.appendChild(b);
                body.appendChild(row);
                body.scrollTop = body.scrollHeight;
            }

            function typing(on) {
                let t = document.getElementById('cw-typing');
                if (on) {
                    if (!t) {
                        t = document.createElement('div');
                        t.id = 'cw-typing';
                        t.className = 'cw-typing';
                        t.innerHTML = '<span class="cw-dots"><span></span><span></span><span></span></span> typing…';
                        body.appendChild(t);
                    }
                } else if (t) {
                    t.remove();
                }
                body.scrollTop = body.scrollHeight;
            }

            async function doSend() {
                const msg = (input.value || '').trim();
                if (!msg) return;
                addBubble('You', msg);
                input.value = '';
                toggleSend();
                input.focus();
                typing(true);
                sendBtn.disabled = true;

                try {
                    const r = await fetch(WEBHOOK, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            message: msg
                        })
                    });
                    const isJson = (r.headers.get('content-type') || '').includes('application/json');
                    const data = isJson ? await r.json() : {
                        reply: await r.text()
                    };
                    typing(false);
                    addBubble(BRAND, data.reply || '(no reply)');
                } catch (e) {
                    typing(false);
                    addBubble('System', 'Network error: ' + e.message);
                } finally {
                    sendBtn.disabled = false;
                }
            }

            sendBtn.addEventListener('click', doSend);
            input.addEventListener('keydown', e => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    doSend();
                }
            });

            // IMPORTANT: start closed (CSS already sets display:none)
            // no need to call openChat() on load
        })();
    </script>
@endif
