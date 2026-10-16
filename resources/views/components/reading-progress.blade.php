{{-- Reading progress (V13) — CSS-only scroll-driven bar, guarded by
     @supports(animation-timeline) so unsupported browsers simply see nothing.
     No JS, no layout shift. Place once near the top of a content page. --}}
<div class="reading-progress" aria-hidden="true"></div>

@once
    @push('styles')
        <style>
            @supports (animation-timeline: scroll()) {
                .reading-progress {
                    position: fixed;
                    top: 0;
                    left: 0;
                    height: 3px;
                    width: 100%;
                    transform-origin: 0 50%;
                    transform: scaleX(0);
                    background: var(--color-primary);
                    z-index: 60;
                    animation: reading-progress-grow linear;
                    animation-timeline: scroll(root block);
                }
                @keyframes reading-progress-grow {
                    to { transform: scaleX(1); }
                }
                @media (prefers-reduced-motion: reduce) {
                    .reading-progress { animation: none; transform: scaleX(0); }
                }
            }
        </style>
    @endpush
@endonce
