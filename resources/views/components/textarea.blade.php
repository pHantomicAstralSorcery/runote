<div class="mb-3 form-group">
    <textarea
        id="{{ $id }}"
        class="form-control @error($name) is-invalid @enderror"
        placeholder="{{ $placeholder ?? '' }}"
        name="{{ $name }}"
        rows="{{ $rows ?? 3 }}"
    >{{ old($name, $value ?? '') }}</textarea>
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    @error($name)
    <div class="invalid-feedback">
        {{ $message }}
    </div>
    @enderror
</div>
