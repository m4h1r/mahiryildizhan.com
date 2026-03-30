@extends('admin.layout', ['title' => 'New Income', 'heading' => 'New Income'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.incomes.store') }}">
            @csrf
            @include('admin.incomes._form')
        </form>
    </section>
@endsection
