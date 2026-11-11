@extends('admin.layout', ['title' => 'New Person', 'heading' => 'New Person'])

@section('content')
    <section class="card-admin">
        <form method="POST" action="{{ route('admin.people.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.people._form')
        </form>
    </section>
@endsection
