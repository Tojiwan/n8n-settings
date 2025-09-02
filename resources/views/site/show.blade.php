@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row align-items-center mb-5">
            <div class="col-md-7">
                <h1 class="display-6 mb-3">{{ $business->name }}</h1>
                <p class="lead text-muted">Premium solutions for domains, hosting, and IT services.</p>
                <a href="#pricing" class="btn btn-primary me-2">See Pricing</a>
                <a href="#contact" class="btn btn-outline-secondary">Contact Us</a>
            </div>
            <div class="col-md-5 text-md-end mt-4 mt-md-0">
                <div class="border rounded p-3 bg-light">
                    <div class="small text-muted">Email · Phone</div>
                    <div>{{ $business->email ?? 'hello@example.com' }}</div>
                    <div>{{ $business->phone ?? '+63 900 000 0000' }}</div>
                </div>
            </div>
        </div>

        {{-- A couple of sample sections… --}}
        <h3 class="h4 mb-3" id="products">Products & Services</h3>
        <div class="row g-3 mb-5">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Shared Hosting</h5>
                        <p class="card-text small text-muted">Reliable, affordable hosting for small sites.</p>
                        <a href="#pricing" class="btn btn-sm btn-outline-primary">View plans</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Domains</h5>
                        <p class="card-text small text-muted">Register and manage your domain names.</p>
                        <a href="#pricing" class="btn btn-sm btn-outline-primary">Pricing</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">SSL Certificates</h5>
                        <p class="card-text small text-muted">Keep your site secure and trusted.</p>
                        <a href="#pricing" class="btn btn-sm btn-outline-primary">Learn more</a>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="h4 mb-3" id="pricing">Pricing (sample)</h3>
        <div class="row g-3 mb-5">
            <div class="col-md-4">
                <div class="card h-100 border-primary">
                    <div class="card-body">
                        <h5 class="card-title">Basic</h5>
                        <div class="display-6">₱299<span class="fs-6">/mo</span></div>
                        <ul class="small mt-2 mb-3">
                            <li>10 GB storage</li>
                            <li>Free SSL</li>
                            <li>1 website</li>
                        </ul>
                        <a href="#contact" class="btn btn-primary w-100">Get started</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-primary">
                    <div class="card-body">
                        <h5 class="card-title">Standard</h5>
                        <div class="display-6">₱699<span class="fs-6">/mo</span></div>
                        <ul class="small mt-2 mb-3">
                            <li>50 GB storage</li>
                            <li>Free SSL & email</li>
                            <li>3 websites</li>
                        </ul>
                        <a href="#contact" class="btn btn-primary w-100">Choose plan</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-primary">
                    <div class="card-body">
                        <h5 class="card-title">Business</h5>
                        <div class="display-6">₱1,099<span class="fs-6">/mo</span></div>
                        <ul class="small mt-2 mb-3">
                            <li>Unlimited storage</li>
                            <li>Priority support</li>
                            <li>Unlimited sites</li>
                        </ul>
                        <a href="#contact" class="btn btn-primary w-100">Talk to sales</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- <h3 class="h4 mb-3" id="contact">Contact</h3>
        <div class="row g-3 mb-5">
            <div class="col-md-6">
                <div class="border rounded p-3">Email: {{ $business->email ?? 'support@example.com' }}</div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3">Phone: {{ $business->phone ?? '+63 900 000 0000' }}</div>
            </div>
        </div> --}}
    </div>

    {{-- Only render the widget if AI is enabled --}}
    @if ($webhookUrl)
        @include('partials.chat-widget', [
            'webhookUrl' => $webhookUrl,
            'brand' => $business->name,
        ])
    @endif
@endsection
