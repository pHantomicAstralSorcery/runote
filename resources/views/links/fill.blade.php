@extends('welcome')

@section('title', 'Заполнение тетради')

@section('content')
<div class="container py-4">
  <h1>Тетрадь: {{ $link->label }}</h1>

  @if(session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif

  <form method="POST" action="{{ route('links.submit', $link->slug) }}" enctype="multipart/form-data">
    @csrf

    {{-- Отображаем HTML контент с полями --}}
    <div id="workbookContent">
      {!! renderWorkbookWithFields($link->workbook->content, $link->workbook->fields) !!}
    </div>

    <button type="submit" class="btn btn-primary mt-3">Отправить ответы</button>
  </form>
</div>
@endsection
