@extends('welcome')
@section('title', 'Копия $link->notebook->title для $link->title')
@section('content')
<div class="container">
  <h1>{{ $link->notebook->title }} — {{ $link->title }}</h1>
  <p><em>Автор оригинала: {{ $link->notebook->author->name }}</em></p>

  {{-- Рендерим только поля для ответов --}}
  @foreach($notebook->pages as $page)
    <h2>{{ $page->title }}</h2>
    @foreach($page->blocks as $block)
      <div class="mb-4">
        {!! $block->data['html'] !!}
        @foreach($block->responseFields as $field)
          @include('named_links.partials.field_'.$field->field_type, ['field' => $field])
        @endforeach
      </div>
    @endforeach
  @endforeach

  <button id="submit-btn" class="btn btn-success">Отправить ответы</button>
</div>
<script>
document.getElementById('submit-btn').addEventListener('click', () => {
  const responses = {};
  document.querySelectorAll('[data-field-id]').forEach(el => {
    responses[el.dataset.fieldId] = el.value;
  });
  fetch('{{ route("named_links.submit", $link->token) }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify({ responses })
  }).then(() => {
    alert('Ответы отправлены.');
    location.reload();
  });
});
</script>

@endsection