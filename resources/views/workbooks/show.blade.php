@extends('welcome')

@section('title', 'Тетрадь: ' . $workbook->title)

@section('content')
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Тетрадь: {{ $workbook->title }}</h1>
    <div>
      <a href="{{ route('workbooks.edit', $workbook) }}" class="btn btn-sm btn-secondary">Редактировать</a>
      <a href="{{ route('workbooks.fields.index', $workbook) }}" class="btn btn-sm btn-primary">Поля</a>
      <a href="{{ route('workbooks.links.index', $workbook) }}" class="btn btn-sm btn-primary">Ссылки</a>
    </div>
  </div>

  {{-- Отображение содержимого тетради --}}
  <div class="border p-3 mb-4">
    {!! $workbook->content !!}
  </div>

  {{-- История версий --}}
  <h3>Версии</h3>
  @if($workbook->versions->isEmpty())
    <p>Нет сохранённых версий.</p>
  @else
    <table class="table table-sm">
      <thead>
        <tr>
          <th>#</th>
          <th>Когда</th>
          <th>Действие</th>
        </tr>
      </thead>
      <tbody>
        @foreach($workbook->versions()->latest()->get() as $version)
          <tr>
            <td>{{ $version->id }}</td>
            <td>{{ $version->created_at->format('d.m.Y H:i:s') }}</td>
            <td>
              <form method="POST" action="{{ route('workbooks.revert', [$workbook, $version]) }}" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-warning" onclick="return confirm('Откатить на эту версию?')">
                  Откатить
                </button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif

</div>
@endsection
