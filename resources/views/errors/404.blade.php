@extends('public.layout', ['robots' => 'noindex,nofollow', 'title' => 'Page not found'])

@section('content')
    <section class="card-admin mx-auto max-w-2xl text-center">
        <h1 class="text-3xl font-semibold">Page not found</h1>
        <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">The page you are looking for does not exist.</p>
        <a href="{{ route('home') }}" class="mt-6 inline-flex rounded-lg bg-[var(--color-brand)] px-4 py-2 text-sm font-medium text-white hover:bg-[var(--color-brand-dark)]">
            Return to Home
        </a>
    </section>
@endsection
