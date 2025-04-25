@extends('welcome')

@section('title', 'Мои тетради')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Мои рабочие тетради</h1>
    <a href="{{ route('workbooks.create') }}" class="btn btn-primary">Создать тетрадь</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($workbooks->isEmpty())
    <p>У вас ещё нет тетрадей. <a href="{{ route('workbooks.create') }}">Создайте первую</a>.</p>
  @else
    <table class="table table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Название</th>
          <th>Создана</th>
          <th>Обновлена</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody>
        @foreach($workbooks as $wb)
          <tr>
            <td>{{ $wb->id }}</td>
            <td>{{ $wb->title }}</td>
            <td>{{ $wb->created_at->format('d.m.Y H:i') }}</td>
            <td>{{ $wb->updated_at->format('d.m.Y H:i') }}</td>
            <td>
              <a href="{{ route('workbooks.show', $wb) }}" class="btn btn-sm btn-info">Просмотр</a>
              <a href="{{ route('workbooks.edit', $wb) }}" class="btn btn-sm btn-secondary">Ред.</a>
              <form action="{{ route('workbooks.destroy', $wb) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger" onclick="return confirm('Удалить тетрадь?')">Удалить</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>
@endsection
