@extends('public.layout', ['robots' => 'noindex,nofollow', 'title' => 'Maintenance mode'])

@section('content')
    <section class="card-admin mx-auto max-w-2xl text-center">
        <h1 class="text-3xl font-semibold">Maintenance mode</h1>
        <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">We are performing maintenance. Please check back shortly.</p>
    </section>
@endsection
