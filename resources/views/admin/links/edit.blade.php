@extends('admin.layout', ['title' => 'Edit Link', 'heading' => 'Edit Link'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.links.update', $link) }}">
            @csrf
            @method('PUT')
            @include('admin.links._form')
        </form>
    </section>
@endsection