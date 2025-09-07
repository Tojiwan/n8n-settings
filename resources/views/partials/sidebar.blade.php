<aside id="app-sidebar" class="sidebar">
    <div class="sidebar-header">
        <button id="sidebar-toggle" class="sidebar-toggle" type="button" aria-label="Toggle sidebar">☰</button>
        <span class="sidebar-brand">Portal</span>
    </div>

    <nav class="sidebar-nav">
        <a class="sidebar-link" href="{{ route('businesses.index') }}">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-house-fill" viewBox="0 0 16 16">
                    <path
                        d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L8 2.207l6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293z" />
                    <path d="m8 3.293 6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293z" />
                </svg>
            </span>
            <span class="text">Home</span>
        </a>
        {{-- add more links --}}
    </nav>
</aside>

<script>
    (function() {
        const body = document.body;
        body.classList.add('with-sidebar');

        // Backdrop for mobile (once)
        let backdrop = document.querySelector('.sidebar-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'sidebar-backdrop';
            document.body.appendChild(backdrop);
        }

        const KEY = 'portal.sidebar.collapsed';
        const desktop = () => window.matchMedia('(min-width: 992px)').matches;

        // Restore desktop collapsed state
        if (localStorage.getItem(KEY) === '1' && desktop()) {
            body.classList.add('sidebar-collapsed');
        }

        const toggleBtn = document.getElementById('sidebar-toggle');

        function openMobile() {
            body.classList.add('sidebar-open');
        }

        function closeMobile() {
            body.classList.remove('sidebar-open');
        }

        function toggle() {
            if (desktop()) {
                // Desktop → collapse/expand column; content does not move
                body.classList.toggle('sidebar-collapsed');
                localStorage.setItem(KEY, body.classList.contains('sidebar-collapsed') ? '1' : '0');
            } else {
                // Mobile → act as off-canvas
                if (body.classList.contains('sidebar-open')) closeMobile();
                else openMobile();
            }
        }

        toggleBtn?.addEventListener('click', toggle);
        backdrop.addEventListener('click', closeMobile);
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeMobile();
        });

        // When resizing across breakpoints, cleanly reset mobile open state
        window.addEventListener('resize', () => {
            if (desktop()) {
                body.classList.remove('sidebar-open');
                // Re-apply collapsed preference
                if (localStorage.getItem(KEY) === '1') body.classList.add('sidebar-collapsed');
                else body.classList.remove('sidebar-collapsed');
            }
        });
    })();
</script>
