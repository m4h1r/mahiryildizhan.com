@extends('admin.layout', ['title' => 'Yeni Besin', 'heading' => 'Yeni Besin'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.foods.store') }}">
            @csrf
            @include('admin.foods._form')
        </form>
    </section>
@endsection
