@extends('admin.layout', ['title' => 'New Node', 'heading' => 'New Node'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.nodes.store') }}">
            @csrf
            @include('admin.nodes._form')
        </form>
    </section>
@endsection