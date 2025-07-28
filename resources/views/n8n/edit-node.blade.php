@extends(backpack_view('blank'))

@section('content')
    <h3>Edit Workflow</h3>

    <form method="POST" action="{{ url('/admin/workflows/' . $workflow['id'] . '/update') }}">
        @csrf

        <div class="form-group">
            <label>Workflow Name</label>
            <input type="text" name="name" class="form-control" value="{{ $workflow['name'] }}" required>
        </div>

        <div class="form-group mt-3">
            <label>Select AI Node</label>
            <select name="node_id" id="node_id" class="form-control" required>
                @foreach ($nodes as $node)
                    <option value="{{ $node['id'] }}" data-prompt="{{ e($node['prompt']) }}">
                        {{ $node['name'] ?? 'AI Node (' . $node['type'] . ')' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mt-3">
            <label>System Prompt</label>
            <textarea name="system_prompt" id="system_prompt" rows="15" class="form-control">{{ old('system_prompt', $nodes[0]['prompt'] ?? '') }}</textarea>
        </div>

        <button type="submit" class="btn btn-success mt-3">Save</button>
        <a href="{{ url('/admin/workflows') }}" class="btn btn-secondary mt-3">Cancel</a>
    </form>
@endsection

@push('after_scripts')
<script>
    // When user changes the selected node, update the textarea with that node's current prompt
    document.getElementById('node_id').addEventListener('change', function () {
        let selectedOption = this.options[this.selectedIndex];
        let prompt = selectedOption.getAttribute('data-prompt') || '';
        document.getElementById('system_prompt').value = prompt;
    });
</script>
@endpush
