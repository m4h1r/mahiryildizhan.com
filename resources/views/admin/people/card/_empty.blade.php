{{-- Empty layer: $message, optional $action (bool) --}}
<div class="glass-well flex flex-col items-start gap-2 px-4 py-4 text-sm text-ink-muted">
    <p>{{ $message }}</p>
    @if ($action ?? false)
        <a href="{{ route('admin.people.assessments.create', $person) }}" class="admin-btn-sm admin-btn-ghost">{{ __('Add assessment') }}</a>
    @endif
</div>
