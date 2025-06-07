@extends('welcome')
@section('title', 'Мои тетради')

@section('content')
<div class="container">
  <h1 class="text-center">Мои тетради</h1>
  <a href="{{ route('notebooks.create') }}" class="btn btn-primary mb-3">Создать новую тетрадь</a>
  @if($notebooks->count())
    <ul class="list-group">
      @foreach($notebooks as $notebook)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          {{ $notebook->title }}
          <div>
            <a href="{{ route('notebooks.edit', $notebook) }}" class="btn btn-secondary btn-sm">Редактировать</a>
            <form action="{{ route('notebooks.destroy', $notebook) }}" method="POST" class="d-inline">
              @csrf
              @method('DELETE')
              <button class="btn btn-danger btn-sm" onclick="return confirm('Удалить тетрадь?')">Удалить</button>
            </form>
          </div>
        </li>
      @endforeach
    </ul>
    <!-- Пагинация -->
    <div class="d-flex justify-content-center mt-4">
        {{ $notebooks->links('components.pagination') }}
    </div>
  @else
    <p>Тетради не найдены.</p>
  @endif
</div>
@endsection
