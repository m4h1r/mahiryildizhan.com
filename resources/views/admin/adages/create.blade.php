@extends('admin.layout', ['title' => 'New Adage', 'heading' => 'New Adage'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.adages.store') }}">
            @csrf
            @include('admin.adages._form')
        </form>
    </section>
@endsection