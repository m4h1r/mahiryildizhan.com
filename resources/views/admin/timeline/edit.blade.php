@extends('admin.layout', ['title' => 'Edit Timeline Event', 'heading' => 'Edit Timeline Event'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.timeline.update', $event) }}">
            @csrf
            @method('PUT')
            @include('admin.timeline._form')
        </form>
    </section>
@endsection