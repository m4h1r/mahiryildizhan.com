@php
    /*
     * Sidebar navigation, as data. Each icon is the inner markup of a 24×24
     * stroke SVG (static, written here — never user input).
     */
    $navGroups = [
        ['key' => 'nav_main', 'label' => __('Main'), 'open' => true, 'items' => [
            ['route' => 'dashboard', 'active' => 'dashboard', 'label' => __('Dashboard'), 'icon' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>'],
            ['route' => 'admin.posts.index', 'active' => 'admin.posts.*', 'label' => __('Posts'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2ZM8 7h8M8 11h8M8 15h5"/>'],
            ['route' => 'admin.media.index', 'active' => 'admin.media.*', 'label' => __('Media'), 'icon' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 15-5-5L5 21"/>'],
            ['route' => 'admin.comments.index', 'active' => 'admin.comments.*', 'label' => __('Comments'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10Z"/>'],
        ]],
        ['key' => 'nav_people', 'label' => __('People & Relationships'), 'open' => true, 'items' => [
            ['route' => 'admin.people.index', 'active' => 'admin.people.*', 'label' => __('People'), 'icon' => '<circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a4 4 0 0 1 0 7.75M21 21v-2a4 4 0 0 0-3-3.85"/>'],
            ['route' => 'admin.interactions.index', 'active' => 'admin.interactions.*', 'label' => __('Interactions'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-2v4l-4-4H9a2 2 0 0 1-2-2v-1M3 8V4a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H9l-4 4V8Z"/>'],
            ['route' => 'admin.nodes.index', 'active' => 'admin.nodes.*', 'label' => __('Nodes'), 'icon' => '<circle cx="5" cy="12" r="2"/><circle cx="19" cy="5" r="2"/><circle cx="19" cy="19" r="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m7 11 10-5M7 13l10 5"/>'],
            ['route' => 'admin.node-connections.index', 'active' => 'admin.node-connections.*', 'label' => __('Node Connections'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>'],
        ]],
        ['key' => 'nav_content', 'label' => __('Content'), 'open' => true, 'items' => [
            ['route' => 'admin.timeline.index', 'active' => 'admin.timeline.*', 'label' => __('Timeline'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>'],
            ['route' => 'admin.adages.index', 'active' => 'admin.adages.*', 'label' => __('Adages'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1Zm14 0c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1Z"/>'],
        ]],
        ['key' => 'nav_personel', 'label' => __('Personel'), 'open' => true, 'items' => [
            ['route' => 'admin.todo-items.index', 'active' => 'admin.todo-items.*', 'label' => __('Yapılacaklar'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>'],
            ['route' => 'admin.purchase-items.index', 'active' => 'admin.purchase-items.*', 'label' => __('Satın Alınacaklar'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13 5.4 5M7 13l-2 9m5-9v9m4-9v9m5-9-2 9"/>'],
            ['route' => 'admin.bucketlist', 'active' => 'admin.bucketlist', 'label' => __('Bucket List'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69l1.519-4.674Z"/>'],
            ['route' => 'admin.foods.index', 'active' => 'admin.foods.*', 'label' => __('Besinler'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v6.75A2.25 2.25 0 0 1 6 12H4.5m0-9v18m0 0h1.5m-1.5 0V12m15-9v18m0-13.5a3 3 0 1 0-6 0v4.5a3 3 0 1 0 6 0v-4.5Z"/>'],
            ['route' => 'admin.consumptions.index', 'active' => 'admin.consumptions.*', 'label' => __('Tüketimler'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>'],
        ]],
        ['key' => 'nav_finance', 'label' => __('Finance'), 'open' => true, 'items' => [
            ['route' => 'admin.expenses.index', 'active' => 'admin.expenses.*', 'label' => __('Expenses'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2Zm0 6v2m0 0a2 2 0 1 0 0 4m0-4a2 2 0 1 1 0 4m0 0v2"/>'],
            ['route' => 'admin.incomes.index', 'active' => 'admin.incomes.*', 'label' => __('Incomes'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8v1m0 7v1m0-9V4m0 16v-1"/>'],
            ['route' => 'admin.stakeholders.index', 'active' => 'admin.stakeholders.*', 'label' => __('Stakeholders'), 'icon' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>'],
            ['route' => 'admin.reports', 'active' => 'admin.reports', 'label' => __('Reports'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2Zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2Z"/>'],
        ]],
        ['key' => 'nav_systems', 'label' => __('Systems'), 'open' => true, 'items' => [
            ['route' => 'admin.subscribers.index', 'active' => 'admin.subscribers.*', 'label' => __('Subscribers'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.99 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.92 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z"/>'],
            ['route' => 'admin.import.index', 'active' => 'admin.import.*', 'label' => __('Import'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4m4-5 5 5 5-5m-5 5V3"/>'],
            ['route' => 'admin.activity-log.index', 'active' => 'admin.activity-log.*', 'label' => __('Activity Log'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v5h5"/>'],
            ['route' => 'admin.about', 'active' => 'admin.about', 'label' => __('About Page'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z"/>'],
            ['route' => 'admin.settings', 'active' => 'admin.settings', 'label' => __('Settings'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2Z"/><circle cx="12" cy="12" r="3"/>'],
        ]],
    ];

    $dictionaryIcon = '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h10M4 17h7"/>';
    $navGroups[] = [
        'key' => 'nav_dict',
        'label' => __('Dictionaries'),
        'open' => false,
        'items' => collect($dictionaryNavigation)->map(fn ($item) => [
            'url' => route('admin.dictionaries.index', $item['table']),
            'is_active' => request()->routeIs('admin.dictionaries.*') && request()->route('table') === $item['table'],
            'label' => __($item['label']),
            'icon' => $dictionaryIcon,
        ])->all(),
    ];
@endphp

@foreach ($navGroups as $group)
    @php
        $groupHasActive = collect($group['items'])->contains(
            fn ($item) => $item['is_active'] ?? request()->routeIs($item['active'])
        );
    @endphp
    @if (! $loop->first)
        <div class="nav-divider" aria-hidden="true"></div>
    @endif
    <div class="mb-1" x-data="navGroup('{{ $group['key'] }}', {{ ($group['open'] || $groupHasActive) ? 'true' : 'false' }})">
        <button type="button" @click="toggle()" class="nav-group-label" :aria-expanded="open.toString()">
            <span>{{ $group['label'] }}</span>
            <svg class="h-3 w-3 transition-transform duration-150" :class="open ? '' : '-rotate-90'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
        </button>
        <div x-show="open" class="nav-group-items mt-1 mb-3 space-y-0.5">
            @foreach ($group['items'] as $item)
                @php
                    $isActive = $item['is_active'] ?? request()->routeIs($item['active']);
                    $url = $item['url'] ?? route($item['route']);
                @endphp
                <a
                    href="{{ $url }}"
                    data-tip="{{ $item['label'] }}"
                    class="admin-nav-link {{ $isActive ? 'admin-nav-link-active' : '' }}"
                    @if ($isActive) aria-current="page" @endif
                >
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">{!! $item['icon'] !!}</svg>
                    <span class="nav-label truncate">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endforeach
