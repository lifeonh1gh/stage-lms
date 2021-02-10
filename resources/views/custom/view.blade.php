@extends('layouts.template')
@section('content')
<div class="container">
    <div class="text-center">
        <img src="{{ route('file.get',$data->image) }}" class="rounded" width='200' height='200'>
        <h3>{{ $data->name }}</h3>
        <div class="center">
            <a href="{{ route('custom-block', $data->id) }}" class="btn btn-primary btn-lg">Изучить материал</a>
        </div>
    </div>
</div>
@endsection