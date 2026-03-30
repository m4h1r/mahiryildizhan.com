@extends('admin.layout', ['title' => 'New Post', 'heading' => 'New Post'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.posts._form')
        </form>
    </section>
@endsection
