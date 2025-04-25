<div class="mb-3 form-group">
    <select id="{{ $id }}" class="form-select @error($name) is-invalid @enderror" name="{{ $name }}" {{ isset($disabled) && $disabled ? 'disabled' : '' }}>
        <option value="" 
            @if(isset($placeholderDisabled) && $placeholderDisabled)
                disabled
            @endif
            {{ old($name, $selected ?? '') === '' ? 'selected' : '' }}>
            {{ $placeholder }}
        </option>
        @foreach($options as $key => $option)
            <option value="{{ is_array($options) ? $key : $option }}"
                {{ old($name, $selected ?? '') == (is_array($options) ? $key : $option) ? 'selected' : '' }}>
                {{ $option }}
            </option>
        @endforeach
    </select>
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    @error($name)
    <div class="invalid-feedback">
        {{ $message }}
    </div>
    @enderror
</div>
