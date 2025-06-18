@if(isset($type) && $type === 'number' && !isset($min))
    @php
        throw new Exception("Для поля типа number нужно передавать параметр min.");
    @endphp
@endif

<div class="mb-3 form-group">
    @if(isset($inputGroup) && $inputGroup)
        <div class="input-group">
    @endif
        <input
            type="{{ $type ?? 'text' }}"
            id="{{ $id }}"
            class="form-control @error($name) is-invalid @enderror"
            placeholder="{{ $placeholder ?? ' ' }}"
            name="{{ $name }}"
            value="{{ old($name, $value ?? '') }}"
            @if(isset($min)) min="{{ $min }}" @endif
            @if(isset($max)) max="{{ $max }}" @endif
            @if(isset($readonly) && $readonly) readonly @endif
            @if(isset($required) && $required) required @endif
        >
        @if(isset($button))
            <button type="button" class="btn {{ $button['class'] ?? 'btn-secondary' }}" id="{{ $button['id'] ?? '' }}">
                {!! $button['text'] !!}
            </button>
        @endif
    @if(isset($inputGroup) && $inputGroup)
        </div>
    @endif
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    @error($name)
    <div class="invalid-feedback">
        {{ $message }}
    </div>
    @enderror
</div>
