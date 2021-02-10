@extends('layouts.template')
@section('content')
<div class="container">
    @foreach ($assignments as $assign)
    <div class="text-center">
        <h3>{{ $assign->courses['name'] }}</h3>
        <div class="center">
        </div>
    </div>
    @endforeach
</div>
@endsection