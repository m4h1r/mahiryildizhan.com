@extends('admin.layout', ['title' => 'Edit Post', 'heading' => 'Edit Post'])

@section('content')
    <section class="card-admin p-6">
        <form method="POST" action="{{ route('admin.posts.update', $post) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.posts._form')
        </form>
    </section>
@endsection
