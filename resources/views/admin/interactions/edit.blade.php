@extends('admin.layout', ['title' => 'Edit Interaction', 'heading' => 'Edit Interaction'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.interactions.update', $interaction) }}">
            @csrf
            @method('PUT')
            @include('admin.interactions._form')
        </form>
    </section>
@endsection
