@extends('admin.layout', ['title' => 'Satın Alınacaklar — Düzenle', 'heading' => 'Alım Düzenle'])

@section('content')
    <div class="max-w-2xl space-y-4">
        <form method="POST" action="{{ route('admin.purchase-items.update', $item->id) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="card-admin">
                @include('admin.purchase-items._form')
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900">
                    {{ __('Update') }}
                </button>
                <a href="{{ route('admin.purchase-items.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.purchase-items.destroy', $item->id) }}"
              data-confirm="Bu kaydı silmek istediğine emin misin?">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400">
                {{ __('Delete') }}
            </button>
        </form>
    </div>
@endsection
