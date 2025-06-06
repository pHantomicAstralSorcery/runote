@php
  $rules = json_decode($field->validation_rules, true);
  $min = $rules['min'] ?? 1;
  $max = $rules['max'] ?? 5;
@endphp
<div class="mb-3">
  <label class="form-label">Шкала ({{ $min }}–{{ $max }}):</label>
  <input type="range" class="form-range"
         data-field-id="{{ $field->id }}"
         min="{{ $min }}" max="{{ $max }}"
         value="{{ ($min+$max)/2 }}">
  <output class="fw-bold">{{ ($min+$max)/2 }}</output>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const slider = document.querySelector('[data-field-id="{{ $field->id }}"]');
  const output = slider.nextElementSibling;
  output.textContent = slider.value;
  slider.addEventListener('input', () => {
    output.textContent = slider.value;
  });
});
</script>
