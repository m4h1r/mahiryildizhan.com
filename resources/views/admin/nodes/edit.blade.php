@extends('admin.layout', ['title' => 'Edit Node', 'heading' => 'Edit Node'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.nodes.update', $node) }}">
            @csrf
            @method('PUT')
            @include('admin.nodes._form')
        </form>
    </section>
@endsection