@extends('welcome')
@section('title', 'Создание тетради')

@section('content')
<div class="container">
  <h1 class="text-center my-4">Создание новой тетради</h1>
<div class="row">
<div class="col"></div>
<div class="col">
  <form action="{{ route('notebooks.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        @component('components.input', [
          'type' => 'text',
          'id' => 'title',
          'name' => 'title',
          'label' => 'Название',
          'class' => 'form-control'
        ])@endcomponent
    </div>
    <div class="mb-3">
        @component('components.select', [
          'id' => 'access',
          'name' => 'access',
          'label' => 'Доступ',
          'placeholder' => 'Выберите тип доступа',
          'options' => [
            'open' => 'Открытый',
            'closed' => 'Закрытый',
          ],
          'selected' => old('access'),
          'placeholderDisabled' => true,
          'disabled' => false,
        ])@endcomponent
    </div>
    <button class="form-control btn btn-primary">Создать</button>
  </form>
</div>
<div class="col"></div>
</div>
</div>
@endsection