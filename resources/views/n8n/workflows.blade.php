@extends(backpack_view('blank'))

@section('content')
    <h1>My n8n Workflows</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Created</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($workflows as $workflow)
                <tr>
                    <td>{{ $workflow['name'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($workflow['createdAt'])->toDateTimeString() }}</td>
                    <td>
                        <label class="form-check form-switch">
                            <input type="checkbox" class="form-check-input toggle-switch" data-id="{{ $workflow['id'] }}"
                                {{ $workflow['active'] ? 'checked' : '' }}>
                        </label>
                    </td>
                    <td>
                        <a href="{{ url('/admin/workflows/' . $workflow['id'] . '/edit') }}" class="btn btn-sm btn-warning">
                            Edit
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@push('after_scripts')
    <script>
        document.querySelectorAll('.toggle-switch').forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                const id = this.dataset.id;
                const active = this.checked;

                fetch(`/admin/workflows/${id}/toggle`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            active: active
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            alert('Failed to toggle workflow.');
                            this.checked = !active; // revert on error
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Request failed.');
                        this.checked = !active; // revert on error
                    });
            });
        });
    </script>
@endpush
