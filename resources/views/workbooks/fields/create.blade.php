@extends('welcome')

@section('title', 'Добавить поле')

@section('content')
<div class="container">
  <h2>Новое поле для: {{ $workbook->title }}</h2>

  <form method="POST" action="{{ route('workbooks.fields.store', $workbook) }}">
    @csrf

    <div class="mb-3">
      <label class="form-label">Label</label>
      <input name="label" class="form-control" value="{{ old('label') }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Type</label>
      <select name="type" class="form-select" required>
        <option value="text">text</option>
        <option value="select">select</option>
        <option value="scale">scale</option>
        <option value="file">file</option>
        <option value="photo">photo</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Options (JSON)</label>
      <textarea name="options" class="form-control">{{ old('options') }}</textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Validation Rules (JSON)</label>
      <textarea name="validation_rules" class="form-control">{{ old('validation_rules') }}</textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Key</label>
      <input name="key" class="form-control" value="{{ old('key') }}" required>
    </div>

<div class="mb-3">
  <label class="form-label">Правильный ответ</label>
  <input name="correct_answer" class="form-control" value="{{ old('correct_answer') }}">
</div>


    <button type="submit" class="btn btn-primary">Сохранить</button>
  </form>
</div>
@endsection
