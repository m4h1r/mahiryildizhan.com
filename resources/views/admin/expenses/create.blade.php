@extends('admin.layout', ['title' => 'Yeni Gider', 'heading' => 'Yeni Gider'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.expenses.store') }}">
            @csrf
            @include('admin.expenses._form')
        </form>
    </section>
@endsection
