@extends('admin.layout', ['title' => 'Edit Adage', 'heading' => 'Edit Adage'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.adages.update', $adage) }}">
            @csrf
            @method('PUT')
            @include('admin.adages._form')
        </form>
    </section>
@endsection