@extends('public.layout', ['robots' => 'noindex,nofollow', 'title' => 'Server error'])

@section('content')
    <section class="card-admin mx-auto max-w-2xl text-center">
        <h1 class="text-3xl font-semibold">Server error</h1>
        <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">An unexpected error occurred. Please try again later.</p>
    </section>
@endsection
