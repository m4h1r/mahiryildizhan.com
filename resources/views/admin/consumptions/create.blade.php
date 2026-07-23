@extends('admin.layout', ['title' => 'Yeni Tüketim', 'heading' => 'Yeni Tüketim'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.consumptions.store') }}">
            @csrf
            @include('admin.consumptions._form')
        </form>
    </section>
@endsection
