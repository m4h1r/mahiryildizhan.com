@extends('admin.layout', ['title' => 'Tüketim Düzenle', 'heading' => 'Tüketim Düzenle'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.consumptions.update', $consumption) }}">
            @csrf
            @method('PUT')
            @include('admin.consumptions._form')
        </form>
    </section>
@endsection
