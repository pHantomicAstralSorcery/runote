@extends('welcome')

@section('title', 'Редактировать тетрадь')

@section('content')
<div class="container py-4">
  <h1>Редактирование: {{ $workbook->title }}</h1>

  <form method="POST" action="{{ route('workbooks.update', $workbook) }}">
    @csrf @method('PUT')

    <div class="mb-3">
      <label class="form-label">Название</label>
      <input name="title" class="form-control" value="{{ old('title', $workbook->title) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Настройки (JSON)</label>
      <textarea name="settings" class="form-control">{{ old('settings', json_encode($workbook->settings)) }}</textarea>
    </div>

    {{-- Скрытое поле для контента --}}
    <textarea name="content" id="contentField" hidden>{{ old('content', $workbook->content) }}</textarea>

    {{-- Редактор --}}
    @include('workbooks.editor')

    <div class="mt-3">
      <button type="submit" class="btn btn-primary">Сохранить</button>
    </div>
  </form>
</div>

<script>
  document.querySelector('form').addEventListener('submit', function(){
    document.getElementById('contentField').value =
      document.getElementById('editor').innerHTML;
  });
</script>
@endsection
