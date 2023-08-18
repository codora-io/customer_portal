@include("layouts.partials.head")
<body>
@include('layouts.partials.root-errors')
<!-- @if(!\Illuminate\Support\Facades\Session::has('token')) -->
<!--   @include("layouts.partials.nav") -->
<!-- @endif -->
@include("layouts.partials.prebody")
@include('layouts.partials.success')
@yield('content')
@include('layouts.partials.js')
</body>
</html>
