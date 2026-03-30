@extends('admin.layout', ['title' => 'New Timeline Event', 'heading' => 'New Timeline Event'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.timeline.store') }}">
            @csrf
            @include('admin.timeline._form')
        </form>
    </section>
@endsection