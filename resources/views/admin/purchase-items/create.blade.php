@extends('admin.layout', ['title' => 'Satın Alınacaklar — Yeni', 'heading' => 'Yeni Alım'])

@section('content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.purchase-items.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="card-admin">
                @include('admin.purchase-items._form')
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">
                    {{ __('Save') }}
                </button>
                <a href="{{ route('admin.purchase-items.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>
@endsection
