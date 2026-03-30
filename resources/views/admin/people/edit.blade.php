@extends('admin.layout', ['title' => 'Edit Person', 'heading' => 'Edit Person'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.people.update', $person) }}">
            @csrf
            @method('PUT')
            @include('admin.people._form')
        </form>
    </section>
@endsection
