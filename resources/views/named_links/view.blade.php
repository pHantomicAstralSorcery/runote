@extends('welcome')
@section('title', 'Копия $link->notebook->title для $link->title')
@section('content')
<div class="container">
  <h1>{{ $link->notebook->title }} — {{ $link->title }}</h1>
  <p><em>Автор оригинала: {{ $link->notebook->author->name }}</em></p>

  {{-- Рендерим только поля для ответов --}}
@foreach($notebook->pages as $page)
  <h3>{{ $page->title }}</h3>
  @foreach($page->blocks as $block)
    {!! $block->data['html'] ?? '' !!}
    @foreach($block->responseFields as $field)
      @include('named_links.partials.field_'.$field->field_type, ['field' => $field])
    @endforeach
  @endforeach
@endforeach

<button id="submit-responses" class="btn btn-primary">Отправить ответы</button>
</div>
<script>
document.getElementById('submit-responses').addEventListener('click', () => {
  const linkToken = '{{ $link->token }}';
  const inputs = document.querySelectorAll('[data-field-id]');
  const responses = {};
  inputs.forEach(el => {
    const id = el.dataset.fieldId;
    let val = el.value;
    if (el.type === 'range') val = el.value;
    responses[id] = val;
  });

  fetch(`/named-links/${linkToken}/submit`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ responses })
  })
  .then(res => res.json())
  .then(data => {
    // data.results: { field_id: true/false }
    Object.entries(data.results).forEach(([id, ok]) => {
      const el = document.querySelector(`[data-field-id="${id}"]`);
      el.style.borderColor = ok ? 'green' : 'red';
    });
  })
  .catch(() => alert('Ошибка отправки'));
});

</script>

@endsection