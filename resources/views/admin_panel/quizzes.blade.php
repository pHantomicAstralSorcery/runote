@extends('welcome')

@section('title', 'Управление тестами')

@section('content')
<div class="container-fluid mt-4">
    <h1 class="h3 mb-4 text-gray-800">Тесты</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Список всех тестов</h6>
        </div>
        <div class="card-body">
            <!-- Search Form -->
             <form method="GET" action="{{ route('admin.quizzes.index') }}" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Поиск по названию или автору..." value="{{ request('search') }}">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Автор</th>
                            <th>Доступ</th>
                            <th>Опубликован</th>
                            <th>Дата создания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($quizzes as $quiz)
                        <tr>
                            <td>{{ $quiz->id }}</td>
                            <td>{{ $quiz->title }}</td>
                            <td>{{ $quiz->user->login ?? 'N/A' }}</td>
                            <td>{{ $quiz->access_type }}</td>
                            <td>
                                @if ($quiz->is_published)
                                    <span class="badge bg-success">Да</span>
                                @else
                                    <span class="badge bg-danger">Нет</span>
                                @endif
                            </td>
                            <td>{{ $quiz->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-info" title="Просмотр"><i class="bi bi-eye-fill"></i></a>
                                <a href="#" class="btn btn-sm btn-outline-primary" title="Редактировать"><i class="bi bi-pencil-square"></i></a>
                                <a href="#" class="btn btn-sm btn-outline-danger" title="Удалить"><i class="bi bi-trash3-fill"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Тесты не найдены.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             <!-- Пагинация с вашим компонентом -->
            <div class="d-flex justify-content-center mt-4">
                {{ $quizzes->links('components.pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection