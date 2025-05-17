@extends('welcome')

@section('title', 'Редактирование тетради: ' . $notebook->title)

@section('content')
<div class="container my-4">
  <h1>Редактирование тетради: {{ $notebook->title }}</h1>

  {{-- Панель инструментов --}}
  @include('notebooks.partials._toolbar')

  {{-- Редактируемая область --}}
  <div id="editor"
       class="border p-3"
       contenteditable="true"
       style="min-height:400px;">
    {!! $notebook->content_html !!}
  </div>

  <div class="mt-3">
    <button id="save-btn" class="btn btn-success">Сохранить</button>
    <button id="edit-blocks-btn"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#blockModal">
      Редактировать блок
    </button>
  </div>
</div>

{{-- Модальное окно для редактирования блока --}}
@include('notebooks.partials._modal-block')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const editor = document.getElementById('editor');

  // 1. Форматирование текста
  document.querySelectorAll('[data-cmd]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.execCommand(btn.dataset.cmd, false, null);
      editor.focus();
    });
  });

  // 2. Сохранение содержимого
  document.getElementById('save-btn').addEventListener('click', () => {
    fetch(window.location.pathname + '/save', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ content: editor.innerHTML })
    })
    .then(r => r.json())
    .then(() => alert('Содержание сохранено'))
    .catch(() => alert('Ошибка при сохранении'));
  });

  // 3. Показ соответствующих настроек блока
  const blockType = document.getElementById('blockType');
  const settings = {
    paragraph: document.getElementById('paragraphSettings'),
    image: document.getElementById('imageSettings'),
    table: document.getElementById('tableSettings')
  };
  function showSettings(type) {
    Object.keys(settings).forEach(k => {
      settings[k].style.display = (k === type ? 'block' : 'none');
    });
  }
  blockType.addEventListener('change', () => showSettings(blockType.value));
  showSettings(blockType.value);

  // 4. Вставка блока по нажатию «Вставить блок»
  document.getElementById('save-block-btn').addEventListener('click', () => {
    let html = '';
    const type = blockType.value;

    if (type === 'paragraph') {
      const text = document.getElementById('paragraphText').value;
      const align = document.getElementById('paragraphAlign').value;
      html = `<p style="text-align:${align};">${text}</p>`;
    }

    if (type === 'image') {
      const fileInput = document.getElementById('imageFile');
      if (!fileInput.files.length) {
        alert('Выберите файл изображения');
        return;
      }
      const file = fileInput.files[0];
      const caption = document.getElementById('imageCaption').value;
      const width = document.getElementById('imageWidth').value || 'auto';
      // Загружаем на сервер
      const form = new FormData();
      form.append('image', file);
      fetch('/blocks/upload-image', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: form
      })
      .then(res => res.json())
      .then(json => {
        html = `<figure><img src="${json.url}" style="width:${width};" /><figcaption>${caption}</figcaption></figure>`;
        editor.insertAdjacentHTML('beforeend', html);
        bootstrap.Modal.getInstance(document.getElementById('blockModal')).hide();
      })
      .catch(() => alert('Ошибка загрузки изображения'));
      return;
    }

    if (type === 'table') {
      const rows = parseInt(document.getElementById('tableRows').value);
      const cols = parseInt(document.getElementById('tableCols').value);
      const border = parseInt(document.getElementById('tableBorder').value);
      html = `<table class="table" border="${border}">`;
      for (let r = 0; r < rows; r++) {
        html += '<tr>';
        for (let c = 0; c < cols; c++) {
          html += '<td>&nbsp;</td>';
        }
        html += '</tr>';
      }
      html += '</table>';
    }

    // Вставка HTML блока
    editor.insertAdjacentHTML('beforeend', html);
    bootstrap.Modal.getInstance(document.getElementById('blockModal')).hide();
  });
});
</script>
@endsection
