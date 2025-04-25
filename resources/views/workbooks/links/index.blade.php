@extends('welcome')

@section('title', 'Именные ссылки')

@section('content')
<div class="container">
  <h2>Ссылки для: {{ $workbook->title }}</h2>

  <ul class="list-group mb-3">
    @foreach($links as $l)
      <li class="list-group-item d-flex justify-content-between">
        <span>{{ $l->name }} — <a href="{{ route('links.show',$l->slug) }}">{{ $l->slug }}</a></span>
        <form method="POST" action="{{ route('workbooks.links.destroy',[$workbook,$l]) }}">
          @csrf @method('DELETE')
          <button class="btn btn-sm btn-danger">Удалить</button>
        </form>
      </li>
    @endforeach
  </ul>

  <form method="POST" action="{{ route('workbooks.links.store', $workbook) }}" class="d-flex gap-2">
    @csrf
    <input name="name" class="form-control" placeholder="Имя ссылки" required>
    <button class="btn btn-primary">Создать</button>
  </form>
</div>
@endsection
