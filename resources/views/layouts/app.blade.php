<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Businesses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
</head>

<body class="with-sidebar">
  @include('partials.sidebar')        {{-- <aside class="sidebar">… --}}
  <main class="app-content">          {{-- content sibling --}}
    <div class="container">
      @yield('content')
    </div>
  </main>
</body>
</html>
