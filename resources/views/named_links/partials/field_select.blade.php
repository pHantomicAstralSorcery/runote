<div class="mb-3">
  <label class="form-label">Выберите ответ:</label>
  @foreach(json_decode($field->correct_answers, true) as $option)
    <div class="form-check">
      <input class="form-check-input" type="radio"
             name="responses[{{ $field->id }}]"
             data-field-id="{{ $field->id }}"
             value="{{ $option }}">
      <label class="form-check-label">{{ $option }}</label>
    </div>
  @endforeach
</div>
