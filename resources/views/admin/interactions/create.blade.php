@extends('admin.layout', ['title' => 'New Interaction', 'heading' => 'New Interaction'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.interactions.store') }}">
            @csrf
            @include('admin.interactions._form')
        </form>
    </section>
@endsection
