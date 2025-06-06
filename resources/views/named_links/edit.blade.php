@extends('welcome')
@section('title', 'Редактирование ссылки')

@section('content')
<div class="container">
  <h1>Редактировать ссылку для «{{ $notebook->title }}»</h1>
  <form action="{{ route('named_links.update', [$notebook, $namedLink]) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="mb-3">
      <label for="title" class="form-label">Название копии</label>
      <input type="text" name="title" id="title" class="form-control" value="{{ $namedLink->title }}" required>
    </div>
    <button class="btn btn-primary">Сохранить</button>
  </form>
</div>
@endsection
