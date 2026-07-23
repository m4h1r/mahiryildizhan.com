@extends('admin.layout', ['title' => 'Besin Düzenle', 'heading' => 'Besin Düzenle'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.foods.update', $food) }}">
            @csrf
            @method('PUT')
            @include('admin.foods._form')
        </form>
    </section>
@endsection
