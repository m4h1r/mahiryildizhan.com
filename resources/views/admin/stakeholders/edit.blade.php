@extends('admin.layout', ['title' => 'Edit Stakeholder', 'heading' => 'Edit Stakeholder'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.stakeholders.update', $stakeholder) }}">
            @csrf
            @method('PUT')
            @include('admin.stakeholders._form')
        </form>
    </section>
@endsection
