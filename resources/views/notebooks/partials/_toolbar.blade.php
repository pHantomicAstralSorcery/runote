<div class="btn-toolbar mb-2" role="toolbar" id="editorToolbar">
<div class="d-flex justify-content-start mb-2 w-100 btn-exc">
  <button class="btn btn-outline-info btn-sm me-2" id="togglePreview" type="button">
    <i class="bi bi-eye"></i> 
  <span id="togglePreviewText">Предварительный просмотр</span>
  </button>
    <button id="save-btn" class="btn btn-outline-success btn-sm me-2" type="button">
      <i class="bi bi-save"></i> Сохранить
    </button>
</div>
<div class="btn-tgl">
 {{-- **Undo/Redo** --}}
        <button id="undo-btn" class="btn btn-outline-secondary btn-sm me-1" title="Отменить (Ctrl+Z)"><i class="bi bi-arrow-counterclockwise"></i></button>
        <button id="redo-btn" class="btn btn-outline-secondary btn-sm me-2" title="Повторить (Ctrl+Y)"><i class="bi bi-arrow-clockwise"></i></button>

  {{-- Стиль текста --}}
  <div class="btn-group me-2">
    <select id="fontFamily" class="form-select form-select-sm me-2">
      <option value="Arial">Arial</option>
      <option value="Times New Roman">Times New Roman</option>
      <option value="Courier New">Courier New</option>
    </select>
    <select id="fontSize" class="form-select form-select-sm me-2">
      <option value="1">8pt</option>
      <option value="2">10pt</option>
      <option value="3" selected>12pt</option>
      <option value="4">14pt</option>
      <option value="5">18pt</option>
      <option value="6">24pt</option>
      <option value="7">36pt</option>
    </select>
    <input type="color" id="fontColor" class="form-control form-control-color form-control-sm w-25" title="Цвет текста">
  </div>

  {{-- Форматирование --}}
  <div class="btn-group me-2">
    <button class="btn btn-outline-secondary btn-sm" data-cmd="bold"><i class="bi bi-type-bold"></i></button>
    <button class="btn btn-outline-secondary btn-sm" data-cmd="italic"><i class="bi bi-type-italic"></i></button>
    <button class="btn btn-outline-secondary btn-sm" data-cmd="underline"><i class="bi bi-type-underline"></i></button>
  </div>

  {{-- Выравнивание и отступы --}}
  <div class="btn-group me-2">
    <button class="btn btn-outline-secondary btn-sm" data-cmd="justifyLeft"><i class="bi bi-text-left"></i></button>
    <button class="btn btn-outline-secondary btn-sm" data-cmd="justifyCenter"><i class="bi bi-text-center"></i></button>
    <button class="btn btn-outline-secondary btn-sm" data-cmd="justifyRight"><i class="bi bi-text-right"></i></button>
<button class="btn btn-outline-secondary btn-sm" data-cmd="justifyFull" title="По ширине"><i class="bi bi-justify"></i></button>
    <button class="btn btn-outline-secondary btn-sm" data-cmd="indent"><i class="bi bi-text-indent-left"></i></button>
    <button class="btn btn-outline-secondary btn-sm" data-cmd="outdent"><i class="bi bi-text-indent-right"></i></button>
  </div>

  {{-- Списки и ссылки --}}
  <div class="btn-group me-2">
    <button class="btn btn-outline-secondary btn-sm" data-cmd="insertUnorderedList"><i class="bi bi-list-ul"></i></button>
    <button class="btn btn-outline-secondary btn-sm" data-cmd="insertOrderedList"><i class="bi bi-list-ol"></i></button>
 <button id="openTableModal" class="btn btn-outline-secondary btn-sm"><i class="bi bi-table"></i></button>
    <button class="btn btn-outline-secondary btn-sm" id="createLink"><i class="bi bi-link-45deg"></i></button>
<button class="btn btn-outline-secondary btn-sm" id="insertImage"><i class="bi bi-image"></i></button>
  </div>

  {{-- Вставка поля-ответа --}}
  <div class="btn-group">
    <button class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
      <i class="bi bi-pencil-square"></i> Поле-ответ
    </button>
    <ul class="dropdown-menu">
      <li><a class="dropdown-item insert-field" data-type="text">Текстовое поле</a></li>
      <li><a class="dropdown-item insert-field" data-type="select">Поле-список</a></li>
      <li><a class="dropdown-item insert-field" data-type="file">Поле-файл</a></li>
    </ul>
  </div>
</div>
</div>
