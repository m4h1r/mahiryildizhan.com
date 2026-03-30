@extends('admin.layout', ['title' => 'Family Graph', 'heading' => 'Family Graph'])

@section('seo')
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $person->fullName() }} - Family Graph</title>
@endsection

@section('content')
<style>
    @media print {
        a {
            pointer-events: none;
            cursor: default;
        }
    }
</style>
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-8 px-4 transition-colors duration-200">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="mb-8 text-center">
            <div class="flex justify-between items-center mb-4 relative z-50">
                <a href="{{ route('admin.people.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to People List
                </a>
                <button onclick="downloadPDF(this)" class="inline-flex items-center bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 shadow-lg hover:shadow-xl relative z-50">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download PDF
                </button>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                Family Graph
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Interactive family tree visualization
            </p>
        </div>

        {{-- Family Tree Container --}}
        <div id="family-tree-content" class="relative overflow-x-auto pb-4">
            <div class="inline-block min-w-full">
            
            {{-- Rule 3: Paternal Grandparents Row (Baba Tarafı Büyükanne-Büyükbaba) --}}
            @if(($person->father && ($person->father->father || $person->father->mother)) || ($person->mother && ($person->mother->father || $person->mother->mother)))
            <div class="flex justify-center items-center gap-16 mb-8 flex-nowrap">
                {{-- Paternal Grandparents Group --}}
                @if($person->father && ($person->father->father || $person->father->mother))
                <div class="flex items-center">
                    {{-- Paternal Grandfather --}}
                    @if($person->father->father)
                    <a href="{{ route('admin.people.graph', $person->father->father->id) }}" class="block w-48 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-800 dark:to-blue-700 rounded-xl shadow-lg p-3 border border-blue-200 dark:border-blue-600 hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer relative">
                        {{-- Blood Type Badge --}}
                        @if($person->father->father->blood_type)
                        <div class="absolute -top-1 -left-1 w-6 h-6 bg-gradient-to-br from-red-400 to-red-500 dark:from-red-500 dark:to-red-600 rounded-full flex items-center justify-center text-white text-[10px] font-semibold shadow-md ring-2 ring-white dark:ring-gray-800 z-10">
                            {{ $person->father->father->blood_type }}
                        </div>
                        @endif
                        
                        {{-- Death Ribbon --}}
                        @if($person->father->father->deathday)
                        <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 dark:from-gray-800 dark:to-black rounded-full flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-800 z-10">🕊️</div>
                        @endif
                        
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto mb-2 rounded-full overflow-hidden border-2 border-blue-200 dark:border-blue-500 shadow-md">
                                <img src="/assets/img/people/{{ $person->father->father->picture ?? 'user.png' }}" 
                                     alt="{{ $person->father->father->fullName() }}"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="mb-2">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                    {{ $person->father->father->fullName() }}
                                </h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Paternal Grandfather</p>
                            </div>
                            <div class="space-y-1 text-xs text-gray-700 dark:text-gray-300">
                                @if($person->father->father->birthday)
                                <p>🎂 {{ $person->father->father->birthday }}</p>
                                @endif
                            </div>
                        </div>
                    </a>
                    @endif

                    {{-- Connection Line between Paternal Grandparents --}}
                    @if($person->father->father && $person->father->mother)
                    <div class="h-px w-12 -mx-3 bg-gradient-to-r from-blue-300 to-pink-300 dark:from-blue-600 dark:to-pink-600"></div>
                    @endif

                    {{-- Paternal Grandmother --}}
                    @if($person->father->mother)
                    <a href="{{ route('admin.people.graph', $person->father->mother->id) }}" class="block w-48 bg-gradient-to-br from-pink-50 to-pink-100 dark:from-pink-800 dark:to-pink-700 rounded-xl shadow-lg p-3 border border-pink-200 dark:border-pink-600 hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer relative">
                        {{-- Blood Type Badge --}}
                        @if($person->father->mother->blood_type)
                        <div class="absolute -top-1 -left-1 w-6 h-6 bg-gradient-to-br from-red-400 to-red-500 dark:from-red-500 dark:to-red-600 rounded-full flex items-center justify-center text-white text-[10px] font-semibold shadow-md ring-2 ring-white dark:ring-gray-800 z-10">
                            {{ $person->father->mother->blood_type }}
                        </div>
                        @endif
                        
                        {{-- Death Ribbon --}}
                        @if($person->father->mother->deathday)
                        <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 dark:from-gray-800 dark:to-black rounded-full flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-800 z-10">🕊️</div>
                        @endif
                        
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto mb-2 rounded-full overflow-hidden border-2 border-pink-200 dark:border-pink-500 shadow-md">
                                <img src="/assets/img/people/{{ $person->father->mother->picture ?? 'user.png' }}" 
                                     alt="{{ $person->father->mother->fullName() }}"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="mb-2">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                    {{ $person->father->mother->fullName() }}
                                </h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Paternal Grandmother</p>
                            </div>
                            <div class="space-y-1 text-xs text-gray-700 dark:text-gray-300">
                                @if($person->father->mother->birthday)
                                <p>🎂 {{ $person->father->mother->birthday }}</p>
                                @endif
                            </div>
                        </div>
                    </a>
                    @endif
                </div>
                @endif

                {{-- Rule 4: Maternal Grandparents Group (Anne Tarafı Büyükanne-Büyükbaba) --}}
                @if($person->mother && ($person->mother->father || $person->mother->mother))
                <div class="flex items-center">
                    {{-- Maternal Grandfather --}}
                    @if($person->mother->father)
                    <a href="{{ route('admin.people.graph', $person->mother->father->id) }}" class="block w-48 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-800 dark:to-blue-700 rounded-xl shadow-lg p-3 border border-blue-200 dark:border-blue-600 hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer relative">
                        {{-- Blood Type Badge --}}
                        @if($person->mother->father->blood_type)
                        <div class="absolute -top-1 -left-1 w-6 h-6 bg-gradient-to-br from-red-400 to-red-500 dark:from-red-500 dark:to-red-600 rounded-full flex items-center justify-center text-white text-[10px] font-semibold shadow-md ring-2 ring-white dark:ring-gray-800 z-10">
                            {{ $person->mother->father->blood_type }}
                        </div>
                        @endif
                        
                        {{-- Death Ribbon --}}
                        @if($person->mother->father->deathday)
                        <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 dark:from-gray-800 dark:to-black rounded-full flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-800 z-10">🕊️</div>
                        @endif
                        
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto mb-2 rounded-full overflow-hidden border-2 border-blue-200 dark:border-blue-500 shadow-md">
                                <img src="/assets/img/people/{{ $person->mother->father->picture ?? 'user.png' }}" 
                                     alt="{{ $person->mother->father->fullName() }}"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="mb-2">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                    {{ $person->mother->father->fullName() }}
                                </h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Maternal Grandfather</p>
                            </div>
                            <div class="space-y-1 text-xs text-gray-700 dark:text-gray-300">
                                @if($person->mother->father->birthday)
                                <p>🎂 {{ $person->mother->father->birthday }}</p>
                                @endif
                            </div>
                        </div>
                    </a>
                    @endif

                    {{-- Connection Line between Maternal Grandparents --}}
                    @if($person->mother->father && $person->mother->mother)
                    <div class="h-px w-12 -mx-3 bg-gradient-to-r from-blue-300 to-pink-300 dark:from-blue-600 dark:to-pink-600"></div>
                    @endif

                    {{-- Maternal Grandmother --}}
                    @if($person->mother->mother)
                    <a href="{{ route('admin.people.graph', $person->mother->mother->id) }}" class="block w-48 bg-gradient-to-br from-pink-50 to-pink-100 dark:from-pink-800 dark:to-pink-700 rounded-xl shadow-lg p-3 border border-pink-200 dark:border-pink-600 hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer relative">
                        {{-- Blood Type Badge --}}
                        @if($person->mother->mother->blood_type)
                        <div class="absolute -top-1 -left-1 w-6 h-6 bg-gradient-to-br from-red-400 to-red-500 dark:from-red-500 dark:to-red-600 rounded-full flex items-center justify-center text-white text-[10px] font-semibold shadow-md ring-2 ring-white dark:ring-gray-800 z-10">
                            {{ $person->mother->mother->blood_type }}
                        </div>
                        @endif
                        
                        {{-- Death Ribbon --}}
                        @if($person->mother->mother->deathday)
                        <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 dark:from-gray-800 dark:to-black rounded-full flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-800 z-10">🕊️</div>
                        @endif
                        
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto mb-2 rounded-full overflow-hidden border-2 border-pink-200 dark:border-pink-500 shadow-md">
                                <img src="/assets/img/people/{{ $person->mother->mother->picture ?? 'user.png' }}" 
                                     alt="{{ $person->mother->mother->fullName() }}"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="mb-2">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                    {{ $person->mother->mother->fullName() }}
                                </h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Maternal Grandmother</p>
                            </div>
                            <div class="space-y-1 text-xs text-gray-700 dark:text-gray-300">
                                @if($person->mother->mother->birthday)
                                <p>🎂 {{ $person->mother->mother->birthday }}</p>
                                @endif
                            </div>
                        </div>
                    </a>
                    @endif
                </div>
                @endif
            </div>

            {{-- Connection Line --}}
            <div class="flex justify-center mb-6">
                <div class="w-px h-8 bg-gradient-to-b from-blue-300 to-blue-500 dark:from-blue-600 dark:to-blue-400"></div>
            </div>
            @endif

            {{-- Rule 2: Parents Row (Anne-Baba) --}}
            @if($person->father || $person->mother)
            <div class="flex justify-center items-center mb-10">
                {{-- Father Card --}}
                @if($person->father)
                <a href="{{ route('admin.people.graph', $person->father->id) }}" class="block w-48 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900 dark:to-blue-800 rounded-2xl shadow-xl p-4 border-2 border-blue-300 dark:border-blue-700 hover:shadow-2xl transition-all duration-300 transform hover:scale-105 cursor-pointer relative">
                    {{-- Blood Type Badge --}}
                    @if($person->father->blood_type)
                    <div class="absolute -top-1 -left-1 w-6 h-6 bg-gradient-to-br from-red-400 to-red-500 dark:from-red-500 dark:to-red-600 rounded-full flex items-center justify-center text-white text-[10px] font-semibold shadow-md ring-2 ring-white dark:ring-gray-800 z-10">
                        {{ $person->father->blood_type }}
                    </div>
                    @endif
                    
                    {{-- Death Ribbon --}}
                    @if($person->father->deathday)
                    <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 dark:from-gray-800 dark:to-black rounded-full flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-800 z-10">🕊️</div>
                    @endif
                    
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-2 rounded-full overflow-hidden border-4 border-blue-300 dark:border-blue-600 shadow-lg">
                            <img src="/assets/img/people/{{ $person->father->picture ?? 'user.png' }}" 
                                 alt="{{ $person->father->fullName() }}"
                                 class="w-full h-full object-cover">
                        </div>
                        <div class="mb-2">
                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">
                                {{ $person->father->fullName() }}
                            </h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Father</p>
                        </div>
                        <div class="space-y-1 text-xs text-gray-700 dark:text-gray-300">
                            @if($person->father->birthday)
                            <p class="flex items-center justify-center">
                                <span class="mr-2">🎂</span>
                                {{ $person->father->birthday }}
                            </p>
                            @endif
                        </div>
                    </div>
                </a>
                @endif

                {{-- Connection Line between Parents --}}
                @if($person->father && $person->mother)
                <div class="h-px w-16 -mx-4 bg-gradient-to-r from-blue-400 to-pink-400 dark:from-blue-600 dark:to-pink-600"></div>
                @endif

                {{-- Mother Card --}}
                @if($person->mother)
                <a href="{{ route('admin.people.graph', $person->mother->id) }}" class="block w-48 bg-gradient-to-br from-pink-100 to-pink-200 dark:from-pink-900 dark:to-pink-800 rounded-2xl shadow-xl p-4 border-2 border-pink-300 dark:border-pink-700 hover:shadow-2xl transition-all duration-300 transform hover:scale-105 cursor-pointer relative">
                    {{-- Blood Type Badge --}}
                    @if($person->mother->blood_type)
                    <div class="absolute -top-1 -left-1 w-6 h-6 bg-gradient-to-br from-red-400 to-red-500 dark:from-red-500 dark:to-red-600 rounded-full flex items-center justify-center text-white text-[10px] font-semibold shadow-md ring-2 ring-white dark:ring-gray-800 z-10">
                        {{ $person->mother->blood_type }}
                    </div>
                    @endif
                    
                    {{-- Death Ribbon --}}
                    @if($person->mother->deathday)
                    <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 dark:from-gray-800 dark:to-black rounded-full flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-800 z-10">🕊️</div>
                    @endif
                    
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-2 rounded-full overflow-hidden border-4 border-pink-300 dark:border-pink-600 shadow-lg">
                            <img src="/assets/img/people/{{ $person->mother->picture ?? 'user.png' }}" 
                                 alt="{{ $person->mother->fullName() }}"
                                 class="w-full h-full object-cover">
                        </div>
                        <div class="mb-2">
                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">
                                {{ $person->mother->fullName() }}
                            </h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Mother</p>
                        </div>
                        <div class="space-y-1 text-xs text-gray-700 dark:text-gray-300">
                            @if($person->mother->birthday)
                            <p class="flex items-center justify-center">
                                <span class="mr-2">🎂</span>
                                {{ $person->mother->birthday }}
                            </p>
                            @endif
                        </div>
                    </div>
                </a>
                @endif
            </div>

            {{-- Connection Line System: Parents to Center --}}
            <div class="flex justify-center items-center mb-6">
                <div class="flex items-center relative" style="width: 500px;">
                    {{-- Left vertical line (from father) --}}
                    <div class="absolute left-24 top-0 w-px h-8 bg-gradient-to-b from-blue-400 to-blue-600 dark:from-blue-600 dark:to-blue-400"></div>
                    
                    {{-- Horizontal connecting line --}}
                    <div class="absolute left-24 top-8 w-64 h-px bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 dark:from-blue-600 dark:via-purple-600 dark:to-pink-600" style="left: 96px; width: 308px;"></div>
                    
                    {{-- Right vertical line (from mother) --}}
                    <div class="absolute right-24 top-0 w-px h-8 bg-gradient-to-b from-pink-400 to-pink-600 dark:from-pink-600 dark:to-pink-400"></div>
                    
                    {{-- Center vertical line (to center person) --}}
                    <div class="absolute left-1/2 transform -translate-x-1/2 top-8 w-px h-8 bg-gradient-to-b from-purple-400 to-purple-600 dark:from-purple-600 dark:to-purple-400"></div>
                </div>
            </div>
            @endif

            {{-- Rule 1: Center Person Card (Merkez Kişi) --}}
            <div class="flex justify-center items-center gap-8 mb-10 flex-nowrap">
                {{-- Rule 5: Siblings (Kardeşler) - Left Side --}}
                @if($siblings->count() > 0)
                <div class="flex flex-row gap-8 flex-nowrap">
                    @foreach($siblings->where('birthday', '<', $person->birthday ?? '9999-12-31') as $sibling)
                    <a href="{{ route('admin.people.graph', $sibling->id) }}" class="block w-48 bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-800 dark:to-indigo-700 rounded-xl shadow-lg p-3 border border-indigo-200 dark:border-indigo-600 hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer relative">
                        {{-- Blood Type Badge --}}
                        @if($sibling->blood_type)
                        <div class="absolute -top-1 -left-1 w-6 h-6 bg-gradient-to-br from-red-400 to-red-500 dark:from-red-500 dark:to-red-600 rounded-full flex items-center justify-center text-white text-[10px] font-semibold shadow-md ring-2 ring-white dark:ring-gray-800 z-10">
                            {{ $sibling->blood_type }}
                        </div>
                        @endif
                        
                        {{-- Death Ribbon --}}
                        @if($sibling->deathday)
                        <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 dark:from-gray-800 dark:to-black rounded-full flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-800 z-10">🕊️</div>
                        @endif
                        
                        <div class="text-center">
                            <div class="w-14 h-14 mx-auto mb-2 rounded-full overflow-hidden border-2 border-indigo-200 dark:border-indigo-500 shadow-md">
                                <img src="/assets/img/people/{{ $sibling->picture ?? 'user.png' }}" 
                                     alt="{{ $sibling->fullName() }}"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="mb-1">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                    {{ $sibling->fullName() }}
                                </h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Sibling (Older)</p>
                            </div>
                            <div class="text-xs text-gray-700 dark:text-gray-300">
                                @if($sibling->birthday)
                                <p>🎂 {{ $sibling->birthday }}</p>
                                @endif
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif

                {{-- Center Person & Spouse --}}
                <div class="flex items-center gap-6">
                    @php
                        // Determine center person card colors based on gender
                        if ($person->gender_id == 1) {
                            $centerCardBg = 'bg-gradient-to-br from-blue-100 via-blue-200 to-blue-300 dark:from-blue-900 dark:via-blue-800 dark:to-blue-700';
                            $centerCardBorder = 'border-blue-400 dark:border-blue-600';
                            $centerPhotoRing = 'ring-blue-300 dark:ring-blue-700';
                        } elseif ($person->gender_id == 2) {
                            $centerCardBg = 'bg-gradient-to-br from-pink-100 via-pink-200 to-pink-300 dark:from-pink-900 dark:via-pink-800 dark:to-pink-700';
                            $centerCardBorder = 'border-pink-400 dark:border-pink-600';
                            $centerPhotoRing = 'ring-pink-300 dark:ring-pink-700';
                        } else {
                            $centerCardBg = 'bg-gradient-to-br from-gray-100 via-gray-200 to-gray-300 dark:from-gray-900 dark:via-gray-800 dark:to-gray-700';
                            $centerCardBorder = 'border-gray-400 dark:border-gray-600';
                            $centerPhotoRing = 'ring-gray-300 dark:ring-gray-700';
                        }
                    @endphp
                    <div class="w-60 {{ $centerCardBg }} rounded-3xl shadow-2xl p-5 border-4 {{ $centerCardBorder }} relative">
                        {{-- Blood Type Badge --}}
                        @if($person->blood_type)
                        <div class="absolute -top-1 -left-1 w-6 h-6 bg-gradient-to-br from-red-400 to-red-500 dark:from-red-500 dark:to-red-600 rounded-full flex items-center justify-center text-white text-[10px] font-semibold shadow-md ring-2 ring-white dark:ring-gray-800 z-10">
                            {{ $person->blood_type }}
                        </div>
                        @endif
                        
                        {{-- Death Ribbon --}}
                        @if($person->deathday)
                        <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 dark:from-gray-800 dark:to-black rounded-full flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-800 z-10">🕊️</div>
                        @endif
                        
                        <div class="relative text-center">
                            <div class="w-20 h-20 mx-auto mb-3 rounded-full overflow-hidden border-4 border-yellow-400 dark:border-yellow-600 shadow-2xl ring-4 {{ $centerPhotoRing }}">
                                <img src="/assets/img/people/{{ $person->picture ?? 'user.png' }}" 
                                     alt="{{ $person->fullName() }}"
                                     class="w-full h-full object-cover">
                            </div>
                            
                            <div class="mb-3">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $person->fullName() }}
                                </h2>
                            </div>
                            
                            <div class="space-y-1 text-xs text-gray-800 dark:text-gray-200 mb-3">
                                @if($person->birthday)
                                <p class="flex items-center justify-center">
                                    <span class="mr-2 text-base">🎂</span>
                                    <span class="font-medium">{{ $person->birthday }}</span>
                                </p>
                                @endif
                                @if($person->deathday)
                                <p class="flex items-center justify-center">
                                    <span class="mr-2 text-base">⚰️</span>
                                    <span class="font-medium">{{ $person->deathday }}</span>
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Rule 6: Spouse (Eş) --}}
                    @if($person->partner)
                    <div class="flex items-center">
                        <div class="h-px w-16 -mx-5 bg-gradient-to-r from-purple-400 to-rose-400 dark:from-purple-600 dark:to-rose-600"></div>
                        @php
                            // Determine spouse card colors based on gender
                            if ($person->partner->gender_id == 1) {
                                $spouseCardBg = 'bg-gradient-to-br from-blue-100 via-blue-200 to-blue-300 dark:from-blue-900 dark:via-blue-800 dark:to-blue-700';
                                $spouseCardBorder = 'border-blue-400 dark:border-blue-600';
                                $spousePhotoBorder = 'border-blue-400 dark:border-blue-600';
                                $spousePhotoRing = 'ring-blue-300 dark:ring-blue-700';
                                $spouseDecoTop = 'from-blue-200 to-blue-400 dark:from-blue-600 dark:to-blue-800';
                                $spouseDecoBottom = 'from-blue-200 to-blue-400 dark:from-blue-600 dark:to-blue-800';
                            } elseif ($person->partner->gender_id == 2) {
                                $spouseCardBg = 'bg-gradient-to-br from-pink-100 via-pink-200 to-pink-300 dark:from-pink-900 dark:via-pink-800 dark:to-pink-700';
                                $spouseCardBorder = 'border-pink-400 dark:border-pink-600';
                                $spousePhotoBorder = 'border-pink-400 dark:border-pink-600';
                                $spousePhotoRing = 'ring-pink-300 dark:ring-pink-700';
                                $spouseDecoTop = 'from-pink-200 to-pink-400 dark:from-pink-600 dark:to-pink-800';
                                $spouseDecoBottom = 'from-pink-200 to-pink-400 dark:from-pink-600 dark:to-pink-800';
                            } else {
                                $spouseCardBg = 'bg-gradient-to-br from-gray-100 via-gray-200 to-gray-300 dark:from-gray-900 dark:via-gray-800 dark:to-gray-700';
                                $spouseCardBorder = 'border-gray-400 dark:border-gray-600';
                                $spousePhotoBorder = 'border-gray-400 dark:border-gray-600';
                                $spousePhotoRing = 'ring-gray-300 dark:ring-gray-700';
                                $spouseDecoTop = 'from-gray-200 to-gray-400 dark:from-gray-600 dark:to-gray-800';
                                $spouseDecoBottom = 'from-gray-200 to-gray-400 dark:from-gray-600 dark:to-gray-800';
                            }
                        @endphp
                        <a href="{{ route('admin.people.graph', $person->partner->id) }}" class="block w-60 {{ $spouseCardBg }} rounded-3xl shadow-2xl p-5 border-4 {{ $spouseCardBorder }} hover:shadow-3xl transition-all duration-300 transform hover:scale-105 cursor-pointer relative">
                            {{-- Blood Type Badge --}}
                            @if($person->partner->blood_type)
                            <div class="absolute -top-1 -left-1 w-6 h-6 bg-gradient-to-br from-red-400 to-red-500 dark:from-red-500 dark:to-red-600 rounded-full flex items-center justify-center text-white text-[10px] font-semibold shadow-md ring-2 ring-white dark:ring-gray-800 z-10">
                                {{ $person->partner->blood_type }}
                            </div>
                            @endif
                            
                            {{-- Death Ribbon --}}
                            @if($person->partner->deathday)
                            <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 dark:from-gray-800 dark:to-black rounded-full flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-800 z-10">🕊️</div>
                            @endif
                            
                            <div class="relative text-center">
                                <div class="w-20 h-20 mx-auto mb-3 rounded-full overflow-hidden border-4 {{ $spousePhotoBorder }} shadow-2xl ring-4 {{ $spousePhotoRing }}">
                                    <img src="/assets/img/people/{{ $person->partner->picture ?? 'user.png' }}" 
                                         alt="{{ $person->partner->fullName() }}"
                                         class="w-full h-full object-cover">
                                </div>
                                
                                <div class="mb-3">
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ $person->partner->fullName() }}
                                    </h2>
                                </div>
                                
                                <div class="space-y-1 text-xs text-gray-800 dark:text-gray-200">
                                    @if($person->partner->birthday)
                                    <p class="flex items-center justify-center">
                                        <span class="mr-2 text-base">🎂</span>
                                        <span class="font-medium">{{ $person->partner->birthday }}</span>
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif
                </div>

                {{-- Rule 5: Siblings (Kardeşler) - Right Side --}}
                @if($siblings->count() > 0)
                <div class="flex flex-row gap-8 flex-nowrap">
                    @foreach($siblings->where('birthday', '>=', $person->birthday ?? '0000-01-01') as $sibling)
                    <a href="{{ route('admin.people.graph', $sibling->id) }}" class="block w-48 bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-800 dark:to-indigo-700 rounded-xl shadow-lg p-3 border border-indigo-200 dark:border-indigo-600 hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer relative">
                        {{-- Blood Type Badge --}}
                        @if($sibling->blood_type)
                        <div class="absolute -top-1 -left-1 w-6 h-6 bg-gradient-to-br from-red-400 to-red-500 dark:from-red-500 dark:to-red-600 rounded-full flex items-center justify-center text-white text-[10px] font-semibold shadow-md ring-2 ring-white dark:ring-gray-800 z-10">
                            {{ $sibling->blood_type }}
                        </div>
                        @endif
                        
                        {{-- Death Ribbon --}}
                        @if($sibling->deathday)
                        <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 dark:from-gray-800 dark:to-black rounded-full flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-800 z-10">🕊️</div>
                        @endif
                        
                        <div class="text-center">
                            <div class="w-14 h-14 mx-auto mb-2 rounded-full overflow-hidden border-2 border-indigo-200 dark:border-indigo-500 shadow-md">
                                <img src="/assets/img/people/{{ $sibling->picture ?? 'user.png' }}" 
                                     alt="{{ $sibling->fullName() }}"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="mb-1">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                    {{ $sibling->fullName() }}
                                </h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Sibling (Younger)</p>
                            </div>
                            <div class="text-xs text-gray-700 dark:text-gray-300">
                                @if($sibling->birthday)
                                <p>🎂 {{ $sibling->birthday }}</p>
                                @endif
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Rule 7: Children Connection Line --}}
            @if($person->allChildren()->count() > 0)
            <div class="flex justify-center mb-6">
                <div class="w-px h-10 bg-gradient-to-b from-purple-400 to-green-600 dark:from-purple-600 dark:to-green-400"></div>
            </div>

            {{-- Rule 7 & 8: Children and Their Spouses --}}
            <div class="flex justify-center items-start gap-10 flex-nowrap mb-10">
                @foreach($person->allChildren() as $child)
                <div class="flex flex-col items-center">
                    <div class="flex items-center gap-3">
                    {{-- Child Card --}}
                    @php
                        // Determine child card colors based on gender
                        if ($child->gender_id == 1) {
                            $childCardBg = 'bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-800 dark:to-blue-700';
                            $childCardBorder = 'border-blue-200 dark:border-blue-600';
                            $childPhotoBorder = 'border-blue-200 dark:border-blue-500';
                        } elseif ($child->gender_id == 2) {
                            $childCardBg = 'bg-gradient-to-br from-pink-50 to-pink-100 dark:from-pink-800 dark:to-pink-700';
                            $childCardBorder = 'border-pink-200 dark:border-pink-600';
                            $childPhotoBorder = 'border-pink-200 dark:border-pink-500';
                        } else {
                            $childCardBg = 'bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700';
                            $childCardBorder = 'border-gray-200 dark:border-gray-600';
                            $childPhotoBorder = 'border-gray-200 dark:border-gray-500';
                        }
                    @endphp
                    <a href="{{ route('admin.people.graph', $child->id) }}" class="block w-48 {{ $childCardBg }} rounded-xl shadow-lg p-3 border {{ $childCardBorder }} hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer relative">
                        {{-- Blood Type Badge --}}
                        @if($child->blood_type)
                        <div class="absolute -top-1 -left-1 w-6 h-6 bg-gradient-to-br from-red-400 to-red-500 dark:from-red-500 dark:to-red-600 rounded-full flex items-center justify-center text-white text-[10px] font-semibold shadow-md ring-2 ring-white dark:ring-gray-800 z-10">
                            {{ $child->blood_type }}
                        </div>
                        @endif
                        
                        {{-- Death Ribbon --}}
                        @if($child->deathday)
                        <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 dark:from-gray-800 dark:to-black rounded-full flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-800 z-10">🕊️</div>
                        @endif
                        
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto mb-2 rounded-full overflow-hidden border-2 {{ $childPhotoBorder }} shadow-md">
                                <img src="/assets/img/people/{{ $child->picture ?? 'user.png' }}" 
                                     alt="{{ $child->fullName() }}"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="mb-2">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                    {{ $child->fullName() }}
                                </h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Child</p>
                            </div>
                            <div class="space-y-1 text-xs text-gray-700 dark:text-gray-300">
                                @if($child->birthday)
                                <p class="flex items-center justify-center">
                                    <span class="mr-2">🎂</span>
                                    {{ $child->birthday }}
                                </p>
                                @endif
                            </div>
                        </div>
                    </a>

                    {{-- Rule 8: Child's Spouse --}}
                    @if($child->partner)
                    <div class="h-px w-10 -mx-2 bg-gradient-to-r from-blue-300 to-pink-300 dark:from-blue-600 dark:to-pink-600"></div>
                    @php
                        // Determine child's spouse card colors based on gender
                        if ($child->partner->gender_id == 1) {
                            $childSpouseBg = 'bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-800 dark:to-blue-700';
                            $childSpouseBorder = 'border-blue-200 dark:border-blue-600';
                            $childSpousePhotoBorder = 'border-blue-200 dark:border-blue-500';
                        } elseif ($child->partner->gender_id == 2) {
                            $childSpouseBg = 'bg-gradient-to-br from-pink-50 to-pink-100 dark:from-pink-800 dark:to-pink-700';
                            $childSpouseBorder = 'border-pink-200 dark:border-pink-600';
                            $childSpousePhotoBorder = 'border-pink-200 dark:border-pink-500';
                        } else {
                            $childSpouseBg = 'bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700';
                            $childSpouseBorder = 'border-gray-200 dark:border-gray-600';
                            $childSpousePhotoBorder = 'border-gray-200 dark:border-gray-500';
                        }
                    @endphp
                    <a href="{{ route('admin.people.graph', $child->partner->id) }}" class="block w-48 {{ $childSpouseBg }} rounded-xl shadow-lg p-3 border {{ $childSpouseBorder }} hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer relative">
                        {{-- Blood Type Badge --}}
                        @if($child->partner->blood_type)
                        <div class="absolute -top-1 -left-1 w-6 h-6 bg-gradient-to-br from-red-400 to-red-500 dark:from-red-500 dark:to-red-600 rounded-full flex items-center justify-center text-white text-[10px] font-semibold shadow-md ring-2 ring-white dark:ring-gray-800 z-10">
                            {{ $child->partner->blood_type }}
                        </div>
                        @endif
                        
                        {{-- Death Ribbon --}}
                        @if($child->partner->deathday)
                        <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 dark:from-gray-800 dark:to-black rounded-full flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-800 z-10">🕊️</div>
                        @endif
                        
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto mb-2 rounded-full overflow-hidden border-2 {{ $childSpousePhotoBorder }} shadow-md">
                                <img src="/assets/img/people/{{ $child->partner->picture ?? 'user.png' }}" 
                                     alt="{{ $child->partner->fullName() }}"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="mb-2">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                    {{ $child->partner->fullName() }}
                                </h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Child's Spouse</p>
                            </div>
                            <div class="space-y-1 text-xs text-gray-700 dark:text-gray-300">
                                @if($child->partner->birthday)
                                <p class="flex items-center justify-center">
                                    <span class="mr-2">🎂</span>
                                    {{ $child->partner->birthday }}
                                </p>
                                @endif
                            </div>
                        </div>
                    </a>
                    @endif
                    </div>
                    
                    {{-- Rule 11: Grandchildren --}}
                    @if($child->allChildren()->count() > 0)
                    <div class="flex flex-col items-center gap-2 mt-3">
                        {{-- Connecting Line --}}
                        <div class="h-4 w-px bg-gradient-to-b from-blue-300 to-purple-300 dark:from-blue-600 dark:to-purple-600"></div>
                        
                        {{-- Grandchildren Cards --}}
                        <div class="flex flex-col gap-2">
                        @foreach($child->allChildren() as $grandchild)
                            <div class="flex items-center gap-3">
                            @php
                                // Determine grandchild card colors based on gender
                                if ($grandchild->gender_id == 1) {
                                    $grandchildCardBg = 'bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-800 dark:to-blue-700';
                                    $grandchildCardBorder = 'border-blue-200 dark:border-blue-600';
                                    $grandchildPhotoBorder = 'border-blue-200 dark:border-blue-500';
                                } elseif ($grandchild->gender_id == 2) {
                                    $grandchildCardBg = 'bg-gradient-to-br from-pink-50 to-pink-100 dark:from-pink-800 dark:to-pink-700';
                                    $grandchildCardBorder = 'border-pink-200 dark:border-pink-600';
                                    $grandchildPhotoBorder = 'border-pink-200 dark:border-pink-500';
                                } else {
                                    $grandchildCardBg = 'bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700';
                                    $grandchildCardBorder = 'border-gray-200 dark:border-gray-600';
                                    $grandchildPhotoBorder = 'border-gray-200 dark:border-gray-500';
                                }
                            @endphp
                            <a href="{{ route('admin.people.graph', $grandchild->id) }}" class="block w-48 {{ $grandchildCardBg }} rounded-xl shadow-lg p-3 border {{ $grandchildCardBorder }} hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer relative">
                                {{-- Blood Type Badge --}}
                                @if($grandchild->blood_type)
                                <div class="absolute -top-1 -left-1 w-6 h-6 bg-gradient-to-br from-red-400 to-red-500 dark:from-red-500 dark:to-red-600 rounded-full flex items-center justify-center text-white text-[10px] font-semibold shadow-md ring-2 ring-white dark:ring-gray-800 z-10">
                                    {{ $grandchild->blood_type }}
                                </div>
                                @endif
                                
                                {{-- Death Badge --}}
                                @if($grandchild->deathday)
                                <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 dark:from-gray-800 dark:to-black rounded-full flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-800 z-10">🕊️</div>
                                @endif
                                
                                <div class="text-center">
                                    <div class="w-16 h-16 mx-auto mb-2 rounded-full overflow-hidden border-2 {{ $grandchildPhotoBorder }} shadow-md">
                                        <img src="/assets/img/people/{{ $grandchild->picture ?? 'user.png' }}" 
                                             alt="{{ $grandchild->fullName() }}"
                                             class="w-full h-full object-cover">
                                    </div>
                                    <div class="mb-2">
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ $grandchild->fullName() }}
                                        </h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Grandchild</p>
                                    </div>
                                    <div class="space-y-1 text-xs text-gray-700 dark:text-gray-300">
                                        @if($grandchild->birthday)
                                        <p class="flex items-center justify-center">
                                            <span class="mr-2">🎂</span>
                                            {{ $grandchild->birthday }}
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                            
                            {{-- Grandchild's Spouse --}}
                            @if($grandchild->partner)
                            <div class="h-px w-10 -mx-2 bg-gradient-to-r from-blue-300 to-pink-300 dark:from-blue-600 dark:to-pink-600"></div>
                            @php
                                // Determine grandchild's spouse card colors based on gender
                                if ($grandchild->partner->gender_id == 1) {
                                    $grandchildSpouseBg = 'bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-800 dark:to-blue-700';
                                    $grandchildSpouseBorder = 'border-blue-200 dark:border-blue-600';
                                    $grandchildSpousePhotoBorder = 'border-blue-200 dark:border-blue-500';
                                } elseif ($grandchild->partner->gender_id == 2) {
                                    $grandchildSpouseBg = 'bg-gradient-to-br from-pink-50 to-pink-100 dark:from-pink-800 dark:to-pink-700';
                                    $grandchildSpouseBorder = 'border-pink-200 dark:border-pink-600';
                                    $grandchildSpousePhotoBorder = 'border-pink-200 dark:border-pink-500';
                                } else {
                                    $grandchildSpouseBg = 'bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700';
                                    $grandchildSpouseBorder = 'border-gray-200 dark:border-gray-600';
                                    $grandchildSpousePhotoBorder = 'border-gray-200 dark:border-gray-500';
                                }
                            @endphp
                            <a href="{{ route('admin.people.graph', $grandchild->partner->id) }}" class="block w-48 {{ $grandchildSpouseBg }} rounded-xl shadow-lg p-3 border {{ $grandchildSpouseBorder }} hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer relative">
                                {{-- Blood Type Badge --}}
                                @if($grandchild->partner->blood_type)
                                <div class="absolute -top-1 -left-1 w-6 h-6 bg-gradient-to-br from-red-400 to-red-500 dark:from-red-500 dark:to-red-600 rounded-full flex items-center justify-center text-white text-[10px] font-semibold shadow-md ring-2 ring-white dark:ring-gray-800 z-10">
                                    {{ $grandchild->partner->blood_type }}
                                </div>
                                @endif
                                
                                {{-- Death Badge --}}
                                @if($grandchild->partner->deathday)
                                <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-gray-700 to-gray-900 dark:from-gray-800 dark:to-black rounded-full flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-800 z-10">🕊️</div>
                                @endif
                                
                                <div class="text-center">
                                    <div class="w-16 h-16 mx-auto mb-2 rounded-full overflow-hidden border-2 {{ $grandchildSpousePhotoBorder }} shadow-md">
                                        <img src="/assets/img/people/{{ $grandchild->partner->picture ?? 'user.png' }}" 
                                             alt="{{ $grandchild->partner->fullName() }}"
                                             class="w-full h-full object-cover">
                                    </div>
                                    <div class="mb-2">
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ $grandchild->partner->fullName() }}
                                        </h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Grandchild's Spouse</p>
                                    </div>
                                    <div class="space-y-1 text-xs text-gray-700 dark:text-gray-300">
                                        @if($grandchild->partner->birthday)
                                        <p class="flex items-center justify-center">
                                            <span class="mr-2">🎂</span>
                                            {{ $grandchild->partner->birthday }}
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                            @endif
                            </div>
                        @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            </div>
        </div>
    </div>
</div>

{{-- html2pdf.js CDN --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
function downloadPDF(button) {
    const element = document.getElementById('family-tree-content');
    const personName = '{{ $person->fullName() }}';
    
    // Show loading state
    const originalHTML = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Generating...';
    
    const opt = {
        margin: [0.3, 0.3, 0.3, 0.3],
        filename: personName + '_Family_Tree.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { 
            scale: 1.5,
            useCORS: true,
            logging: false,
            scrollX: 0,
            scrollY: 0
        },
        jsPDF: { 
            unit: 'in', 
            format: 'a1', 
            orientation: 'landscape' 
        }
    };
    
    html2pdf().set(opt).from(element).save().then(() => {
        // Restore button state
        button.disabled = false;
        button.innerHTML = originalHTML;
    }).catch(err => {
        console.error('PDF generation failed:', err);
        button.disabled = false;
        button.innerHTML = originalHTML;
        alert('Failed to generate PDF. Please try again.');
    });
}
</script>
@endsection
