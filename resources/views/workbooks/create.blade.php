@extends('welcome')

@section('title', 'Создать тетрадь')

@section('content')
<div class="container py-4">
  <h1>Новая тетрадь</h1>

  <form method="POST" action="{{ route('workbooks.store') }}">
    @csrf

    <div class="mb-3">
      <label class="form-label">Название тетради</label>
      <input name="title" class="form-control" value="{{ old('title') }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Настройки (JSON)</label>
      <textarea name="settings" class="form-control" placeholder='{"open_at":null,"close_at":null}'>{{ old('settings','{}') }}</textarea>
    </div>

    <button type="submit" class="btn btn-success">Создать</button>
  </form>
</div>
@endsection
