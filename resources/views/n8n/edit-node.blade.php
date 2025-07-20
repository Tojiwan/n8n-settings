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
			<label>AI Agent System Prompt</label>
			<textarea name="system_prompt" rows="20" class="form-control">{{ $systemPrompt }}</textarea>
		</div>

		<button type="submit" class="btn btn-success mt-3">Save</button>
		<a href="{{ url('/admin/workflows') }}" class="btn btn-secondary mt-3">Cancel</a>
	</form>
@endsection

