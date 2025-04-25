@extends('welcome')

@section('title', 'Поля тетради')

@section('content')
<div class="container">
  <h2>Поля тетради: {{ $workbook->title }}</h2>

  <a href="{{ route('workbooks.fields.create', $workbook) }}" class="btn btn-sm btn-primary mb-3">
    Добавить поле
  </a>

  <table class="table">
    <thead>
      <tr><th>ID</th><th>Label</th><th>Type</th><th>Key</th><th>Действия</th></tr>
    </thead>
    <tbody>
      @foreach($fields as $f)
        <tr>
          <td>{{ $f->id }}</td>
          <td>{{ $f->label }}</td>
          <td>{{ $f->type }}</td>
          <td>{{ $f->key }}</td>
          <td>
            <a href="{{ route('workbooks.fields.edit', [$workbook, $f]) }}" class="btn btn-sm btn-secondary">Ред.</a>
            <form method="POST" action="{{ route('workbooks.fields.destroy', [$workbook, $f]) }}" class="d-inline">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-danger">Удалить</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
