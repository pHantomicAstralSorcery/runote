{{-- resources/views/notebooks/partials/_modal-block.blade.php --}}
<div class="modal fade" id="blockModal" tabindex="-1" aria-labelledby="blockModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="blockModalLabel">Добавить/Редактировать блок</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <div class="modal-body">
        <form id="block-form">
          {{-- Выбор типа блока --}}
          <div class="mb-3">
            <label for="blockType" class="form-label">Тип блока</label>
            <select id="blockType" class="form-select">
              <option value="paragraph">Параграф</option>
              <option value="image">Изображение</option>
              <option value="table">Таблица</option>
            </select>
          </div>

          {{-- Настройки параграфа --}}
          <div class="mb-3" id="paragraphSettings">
            <label for="paragraphText" class="form-label">Текст параграфа</label>
            <textarea id="paragraphText" class="form-control" rows="4"></textarea>
            <label class="form-label mt-2">Выравнивание</label>
            <select id="paragraphAlign" class="form-select">
              <option value="left">Слева</option>
              <option value="center">По центру</option>
              <option value="right">Справа</option>
              <option value="justify">По ширине</option>
            </select>
          </div>

          {{-- Настройки изображения --}}
          <div class="mb-3" id="imageSettings" style="display:none;">
            <label for="imageFile" class="form-label">Изображение</label>
            <input class="form-control" type="file" id="imageFile" accept="image/*">
            <label for="imageCaption" class="form-label mt-2">Подпись</label>
            <input type="text" class="form-control" id="imageCaption">
            <label for="imageWidth" class="form-label mt-2">Ширина (px или %)</label>
            <input type="text" class="form-control" id="imageWidth" placeholder="например, 300px или 50%">
          </div>

          {{-- Настройки таблицы --}}
          <div class="mb-3" id="tableSettings" style="display:none;">
            <label for="tableRows" class="form-label">Число строк</label>
            <input type="number" id="tableRows" class="form-control" min="1" value="2">
            <label for="tableCols" class="form-label mt-2">Число столбцов</label>
            <input type="number" id="tableCols" class="form-control" min="1" value="2">
            <label for="tableBorder" class="form-label mt-2">Граница (px)</label>
            <input type="number" id="tableBorder" class="form-control" min="0" value="1">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="save-block-btn" class="btn btn-primary">Вставить блок</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
      </div>
    </div>
  </div>
</div>
