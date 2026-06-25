@extends('admin.layout', ['title' => 'Women In Circle', 'heading' => 'Women In Circle'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-4 md:p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold text-red-600">{{ __('Women In Circle') }}</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Newest to oldest, by last interaction date.') }}</p>
                </div>
                <a href="{{ route('admin.interactions.index') }}" class="admin-btn admin-btn-ghost">{{ __('Back') }}</a>
            </div>

            @if ($people->isEmpty())
                <p class="py-12 text-center text-gray-500 dark:text-gray-400">{{ __('No interactions of this type yet.') }}</p>
            @else
                <div
                    id="circle-stage"
                    class="relative mx-auto aspect-square w-full max-w-[640px] rounded-full bg-[radial-gradient(circle,_rgba(220,38,38,0.08)_0%,_transparent_70%)] ring-1 ring-red-100 dark:ring-red-900/40"
                >
                    @foreach ($people as $woman)
                        <div class="circle-card-slot absolute left-0 top-0">
                            <a
                                href="{{ route('admin.people.show', $woman) }}"
                                class="circle-card block w-24 -translate-x-1/2 -translate-y-1/2 rounded-xl border border-red-200 bg-white p-2 text-center shadow-md transition-transform duration-200 ease-out hover:z-10 hover:scale-[1.2] hover:shadow-xl dark:border-red-900/50 dark:bg-gray-900"
                            >
                                <img
                                    src="{{ $woman->picture_url }}"
                                    alt="{{ $woman->fullName() }}"
                                    class="mx-auto h-16 w-16 rounded-full border-2 border-red-300 object-cover dark:border-red-700"
                                >
                                <span class="mt-1 block truncate text-xs font-medium text-gray-900 dark:text-gray-100">{{ $woman->fullName() }}</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <script>
        (function () {
            const stage = document.getElementById('circle-stage');

            if (!stage) {
                return;
            }

            const layout = function () {
                const slots = stage.querySelectorAll('.circle-card-slot');
                const count = slots.length;

                if (!count) {
                    return;
                }

                const size = stage.clientWidth;
                const center = size / 2;
                const radius = center - 60;

                slots.forEach(function (slot, index) {
                    const angle = ((2 * Math.PI * index) / count) - (Math.PI / 2);
                    const x = center + radius * Math.cos(angle);
                    const y = center + radius * Math.sin(angle);
                    slot.style.left = x + 'px';
                    slot.style.top = y + 'px';
                });
            };

            window.addEventListener('resize', layout);
            layout();
        })();
    </script>
@endsection
