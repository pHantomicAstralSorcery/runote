@extends('welcome')

@section('title', 'Управление пользователями')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Пользователи</h1>
        <a href="#" class="btn btn-primary">
            <i class="bi bi-plus-circle-fill me-1"></i>
            Добавить пользователя
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Список всех пользователей</h6>
        </div>
        <div class="card-body">
            <!-- Search Form -->
            <form method="GET" action="{{ route('admin.users.index') }}" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Поиск по логину или email..." value="{{ request('search') }}">
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
                            <th>Аватар</th>
                            <th>Логин</th>
                            <th>Email</th>
                            <th>Админ</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <img src="{{ $user->avatar ?? 'https://placehold.co/40x40/6c757d/white?text=A' }}" alt="avatar" class="rounded-circle" width="40" height="40">
                            </td>
                            <td>{{ $user->login }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if ($user->isAdmin)
                                    <span class="badge bg-success">Да</span>
                                @else
                                    <span class="badge bg-secondary">Нет</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at ? $user->created_at->format('d.m.Y H:i') : 'N/A' }}</td> {{-- Safely format created_at --}}
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary" title="Редактировать"><i class="bi bi-pencil-square"></i></a>
                                <a href="#" class="btn btn-sm btn-outline-danger" title="Удалить"><i class="bi bi-trash3-fill"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Пользователи не найдены.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             <!-- Пагинация с вашим компонентом -->
            <div class="d-flex justify-content-center mt-4">
                {{ $users->links('components.pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection
