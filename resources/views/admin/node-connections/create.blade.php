@extends('admin.layout', ['title' => 'New Node Connection', 'heading' => 'New Node Connection'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.node-connections.store') }}">
            @csrf
            @include('admin.node-connections._form')
        </form>
    </section>
@endsection