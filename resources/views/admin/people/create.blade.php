@extends('admin.layout', ['title' => 'New Person', 'heading' => 'New Person'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.people.store') }}">
            @csrf
            @include('admin.people._form')
        </form>
    </section>
@endsection
