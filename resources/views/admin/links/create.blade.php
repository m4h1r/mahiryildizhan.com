@extends('admin.layout', ['title' => 'New Link', 'heading' => 'New Link'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.links.store') }}">
            @csrf
            @include('admin.links._form')
        </form>
    </section>
@endsection