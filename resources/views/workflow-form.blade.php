@extends(backpack_view('blank'))

@section('content')
    <h3>Edit AI Context</h3>
    <form method="POST" action="{{ url('admin/workflow-form') }}">
        @csrf

        <div class="form-group">
            <label>Message</label>
            <textarea name="message" class="form-control" rows="4" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary mt-2">Submit</button>
    </form>
@endsection
