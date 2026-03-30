@extends('admin.layout', ['title' => 'New Stakeholder', 'heading' => 'New Stakeholder'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.stakeholders.store') }}">
            @csrf
            @include('admin.stakeholders._form')
        </form>
    </section>
@endsection
