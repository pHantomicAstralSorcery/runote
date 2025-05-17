@extends('welcome')
@section('title', 'Создание ссылки')
@section('content')
<div class="container">
  <h1>Новая именная ссылка для «{{ $notebook->title }}»</h1>
  <form action="{{ route('named_links.store', $notebook) }}" method="POST">
    @csrf
    <div class="mb-3">
      <label for="title" class="form-label">Название копии</label>
      <input type="text" name="title" id="title" class="form-control" required>
    </div>
    <button class="btn btn-primary">Создать ссылку</button>
  </form>
</div>
@endsection