{{-- @extends('layouts.clienteApp')

@section('content')
@endsection --}}
@extends('layouts.clienteApp')

@section('content')

<!-- Page -->
<div class="page">
      <div class="page-content container-fluid">
        <div id="app">
            <perfil/>
        </div>
      </div>
    </div>

@endsection 
@section('scripts')
<script src="/js/app.js"></script>
<script>
// page is now ready, initialize the calendar...
var id=$('#userId').val();
</script>
@endsection