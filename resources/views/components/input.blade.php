@if($type === 'number' && !isset($min))
    @php
        throw new Exception("Для поля типа number нужно передавать параметр min.");
    @endphp
@endif

<div class="mb-3 form-group">
    <input
        type="{{ $type ?? 'text' }}"
        id="{{ $id }}"
        class="form-control @error($name) is-invalid @enderror"
        placeholder="{{ $placeholder ?? ' ' }}"
        name="{{ $name }}"
        value="{{ old($name, $value ?? '') }}"
        @if($type === 'number') min="{{ $min }}" @endif
        @if($type === 'number' && isset($max)) max="{{ $max }}" @endif
    >
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    @error($name)
    <div class="invalid-feedback">
        {{ $message }}
    </div>
    @enderror
</div>
