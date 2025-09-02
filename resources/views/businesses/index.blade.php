@extends('layouts.app')

@section('content')
<div class="container my-4">

    <h2 class="mb-4">Businesses</h2>

    <div class="row">
        @foreach($businesses as $business)
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">{{ $business->name }}</h5>
                        <p class="card-text small text-muted">
                            {{ $business->email }} <br>
                            {{ $business->phone }}
                        </p>

                        {{-- Status Badge --}}
                        <div id="status-{{ $business->id }}" class="mb-2">
                            @if($business->bot && $business->bot->enabled)
                                <span class="badge bg-success">Active</span>
                            @elseif($business->bot)
                                <span class="badge bg-secondary">Inactive</span>
                            @else
                                <span class="badge bg-secondary">Chatbot Disabled</span>
                            @endif
                        </div>

                        {{-- Primary Button + Toggle --}}
                        @if($business->bot)
                            {{-- Chatbot already provisioned --}}
                            <a class="btn btn-primary btn-sm" href="{{ url('/businesses/'.$business->id.'/ai') }}">
                                Chatbot Settings
                            </a>

                            @if($business->bot->enabled)
                                <button class="btn btn-outline-danger btn-sm ms-2"
                                        onclick="toggleActive({{ $business->id }}, false)">
                                    Deactivate
                                </button>
                            @else
                                <button class="btn btn-outline-success btn-sm ms-2"
                                        onclick="toggleActive({{ $business->id }}, true)">
                                    Activate
                                </button>
                            @endif
                        @else
                            {{-- No bot yet --}}
                            <button class="btn btn-primary btn-sm"
                                    onclick="enableBot({{ $business->id }})">
                                Enable AI Chatbot
                            </button>
                        @endif

                        {{-- Webhook display --}}
                        {{-- <div id="webhook-{{ $business->id }}" class="mt-2 small text-break text-success"></div> --}}

                        <a class="btn btn-outline-secondary btn-sm ms-0 mt-2"
                            href="{{ route('site.show', ['business' => $business->name_slug]) }}"
                            target="_blank">
                            Preview Site
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>

<script>
async function enableBot(businessId) {
    try {
        const res = await fetch("{{ url('/api/ai/bots') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
            },
            body: JSON.stringify({ businessId }),
        });

        const data = await res.json();

        if (res.ok) {
            // refresh to show "Chatbot Settings" + toggle
            window.location.reload();
        } else {
            alert(data.message || "Failed to enable bot");
        }
    } catch (err) {
        alert("Error: " + err.message);
    }
}

async function toggleActive(businessId, activate) {
    const url = activate
        ? "{{ url('/api/ai/bots') }}/" + businessId + "/activate"
        : "{{ url('/api/ai/bots') }}/" + businessId + "/deactivate";

    try {
        const res = await fetch(url, {
            method: "POST",
            headers: { "Accept": "application/json" },
        });

        const data = await res.json();

        if (res.ok) {
            // refresh to update badge + button
            window.location.reload();
        } else {
            alert(data.message || "Failed to toggle workflow");
        }
    } catch (err) {
        alert("Error: " + err.message);
    }
}
</script>
@endsection
