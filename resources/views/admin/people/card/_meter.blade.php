{{-- One scored row: $row = [label, score 0–100, confidence ?0–1, text ?string] --}}
<div class="min-w-0">
    <div class="flex items-baseline justify-between gap-3">
        <span class="truncate text-sm font-medium text-ink">{{ $row['label'] }}</span>
        <span class="shrink-0 text-sm font-semibold text-ink admin-tabular">
            {{ $row['score'] ?? '—' }}
            @if (($row['confidence'] ?? null) !== null)
                <span class="ml-1 text-xs font-medium text-ink-muted" title="{{ __('Confidence') }}">%{{ (int) round($row['confidence'] * 100) }}</span>
            @endif
        </span>
    </div>
    <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-[var(--glass-line)]" role="meter" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $row['score'] ?? 0 }}" aria-label="{{ $row['label'] }}">
        <div class="h-full rounded-full bg-[linear-gradient(90deg,oklch(from_var(--brand-primary)_calc(l+0.12)_c_h),var(--brand-primary))]" style="width: {{ max(0, min(100, (int) ($row['score'] ?? 0))) }}%"></div>
    </div>
    @if (! empty($row['text']))
        <p class="mt-1.5 text-xs leading-relaxed text-ink-muted">{{ $row['text'] }}</p>
    @endif
</div>
