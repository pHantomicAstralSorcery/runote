<div class="modal fade" id="tableSettingsModal" tabindex="-1" aria-labelledby="tableSettingsLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tableSettingsLabel">Настройки таблицы</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="table-settings-form">
          <div class="form-floating mb-3">
            <input type="number" id="ts-rows" class="form-control" min="1" max="30" value="2" placeholder=" ">
            <label for="ts-rows">Число строк</label>
          </div>
          <div class="form-floating mb-3">
            <input type="number" id="ts-cols" class="form-control" min="1" max="30" value="2" placeholder=" ">
            <label for="ts-cols">Число столбцов</label>
          </div>
          <div class="form-floating mb-3">
            <input type="number" id="ts-border" class="form-control" min="0" value="1" placeholder=" ">
            <label for="ts-border">Толщина границы (px)</label>
          </div>
          <div class="form-floating mb-3">
            <select class="form-select" id="ts-style">
              <option value="">Без стиля</option>
              <option value="table-striped">Полосатая</option>
              <option value="table-hover">С hover эффектом</option>
              <option value="table-dark">Тёмная</option>
            </select>
            <label for="ts-style">Стиль таблицы</label>
          </div>
          <hr>
          <p class="mb-2">Параметры столбцов (опционально):</p>
          <div class="form-floating mb-3">
            <input type="text" id="ts-col-width" class="form-control" placeholder=" ">
            <label for="ts-col-width">Фикс. ширина столбцов (например, 100px или 25%)</label>
            <div class="form-text">Оставьте пустым для автоматической ширины. Применится ко всем столбцам.</div>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" value="" id="ts-word-wrap" checked>
            <label class="form-check-label" for="ts-word-wrap">
              Переносить текст в ячейках
            </label>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="apply-table-settings" class="btn btn-primary">Вставить таблицу</button>
      </div>
    </div>
  </div>
</div>
