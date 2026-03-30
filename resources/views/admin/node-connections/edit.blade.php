@extends('admin.layout', ['title' => 'Edit Node Connection', 'heading' => 'Edit Node Connection'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.node-connections.update', $connection) }}">
            @csrf
            @method('PUT')
            @include('admin.node-connections._form')
        </form>
    </section>
@endsection