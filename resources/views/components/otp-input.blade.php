@props(['name' => 'code', 'length' => 6, 'autofocus' => false])

<div
    x-data="{
        length: {{ $length }},
        digits: Array.from({ length: {{ $length }} }, () => ''),
        get value() {
            return this.digits.join('');
        },
        boxes() {
            return Array.from({ length: this.length }, (_, i) => this.$refs['box' + i]).filter(Boolean);
        },
        focusBox(index) {
            this.boxes()[index]?.focus();
        },
        handleInput(index, event) {
            const digitsOnly = event.target.value.replace(/\D/g, '');

            if (digitsOnly.length > 1) {
                this.distribute(digitsOnly, index);
                return;
            }

            this.digits[index] = digitsOnly;
            event.target.value = digitsOnly;

            if (digitsOnly && index < this.length - 1) {
                this.$nextTick(() => this.focusBox(index + 1));
            }
        },
        handleKeydown(index, event) {
            if (event.key === 'Backspace' && !this.digits[index] && index > 0) {
                event.preventDefault();
                this.digits[index - 1] = '';
                this.focusBox(index - 1);
            }
        },
        handlePaste(index, event) {
            event.preventDefault();
            const pasted = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            this.distribute(pasted, index);
        },
        distribute(str, startIndex) {
            const chars = str.split('');
            for (let i = 0; i < chars.length && (startIndex + i) < this.length; i++) {
                this.digits[startIndex + i] = chars[i];
            }
            const nextIndex = Math.min(startIndex + chars.length, this.length - 1);
            this.$nextTick(() => this.focusBox(nextIndex));
        },
    }"
    x-init="{{ $autofocus ? '$nextTick(() => focusBox(0))' : '' }}"
    class="flex gap-2"
>
    <input type="hidden" name="{{ $name }}" :value="value">

    @for ($i = 0; $i < $length; $i++)
        <input
            type="text"
            inputmode="numeric"
            maxlength="1"
            x-ref="box{{ $i }}"
            x-bind:value="digits[{{ $i }}]"
            @input="handleInput({{ $i }}, $event)"
            @keydown="handleKeydown({{ $i }}, $event)"
            @paste="handlePaste({{ $i }}, $event)"
            autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
            class="form-input-admin h-12 w-11 text-center text-lg font-semibold"
            aria-label="{{ __('Digit') }} {{ $i + 1 }}"
        />
    @endfor
</div>
