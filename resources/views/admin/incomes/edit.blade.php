@extends('admin.layout', ['title' => 'Edit Income', 'heading' => 'Edit Income'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.incomes.update', $income) }}">
            @csrf
            @method('PUT')
            @include('admin.incomes._form')
        </form>
    </section>
@endsection
