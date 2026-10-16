{{-- MY Teknoloji mark (V6). Inline SVG so it inherits currentColor on any
     background; the green dot is the only brand colour in the mark and never
     recolours. Do not render this as an <img>. --}}
<svg {{ $attributes->merge(['class' => 'inline-block h-3.5 w-auto align-[-0.15em]']) }}
     viewBox="0 0 48 16" fill="none" xmlns="http://www.w3.org/2000/svg"
     role="img" aria-label="MY Teknoloji">
    <text x="0" y="13" font-family="ui-sans-serif, system-ui, sans-serif"
          font-size="15" font-weight="700" letter-spacing="-0.5"
          fill="currentColor">MY</text>
    <circle cx="44" cy="4" r="3" fill="#16a34a"/>
</svg>
