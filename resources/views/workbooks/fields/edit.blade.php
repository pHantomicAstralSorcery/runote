@extends('welcome')

@section('title', 'Редактировать поле')

@section('content')
<div class="container">
  <h2>Редактировать поле {{ $field->label }} ({{ $workbook->title }})</h2>

  <form method="POST" action="{{ route('workbooks.fields.update', [$workbook, $field]) }}">
    @csrf @method('PUT')

    <div class="mb-3">
      <label class="form-label">Label</label>
      <input name="label" class="form-control" value="{{ old('label', $field->label) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Type</label>
      <select name="type" class="form-select" required>
        @foreach(['text','select','scale','file','photo'] as $t)
          <option value="{{ $t }}" @if($field->type=== $t) selected @endif>{{ $t }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Options (JSON)</label>
      <textarea name="options" class="form-control">{{ old('options', json_encode($field->options)) }}</textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Validation Rules (JSON)</label>
      <textarea name="validation_rules" class="form-control">{{ old('validation_rules', json_encode($field->validation_rules)) }}</textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Key</label>
      <input name="key" class="form-control" value="{{ old('key', $field->key) }}" required>
    </div>

<div class="mb-3">
  <label class="form-label">Правильный ответ</label>
  <input name="correct_answer" class="form-control"
         value="{{ old('correct_answer', $field->correct_answer) }}">
</div>

    <button type="submit" class="btn btn-success">Обновить</button>
  </form>
</div>
@endsection
