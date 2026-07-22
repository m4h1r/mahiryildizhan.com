@if (session('success') || session('error') || session('status'))
    @php
        $message = session('success') ?? session('error') ?? session('status');
        $isError = session()->has('error');
    @endphp

    <x-status-message :type="$isError ? 'danger' : 'success'" dismissible class="mb-4">
        {{ $message }}
    </x-status-message>
@endif
