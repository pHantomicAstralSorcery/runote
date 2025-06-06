<!-- Модальное окно для текстового поля -->
<div class="modal fade" id="textFieldModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Настройка текстового поля</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="form-floating mb-3">
          <input type="text" class="form-control" id="textFieldAnswer" placeholder=" ">
          <label>Правильный ответ</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
        <button type="button" class="btn btn-primary" id="saveTextField">Сохранить</button>
      </div>
    </div>
  </div>
</div>

<!-- Модальное окно для поля-списка -->
<div class="modal fade" id="selectFieldModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Настройка поля-списка</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="selectOptions">
          <div class="input-group mb-2">
            <div class="input-group-text">
              <input type="radio" name="correctOption" value="0">
            </div>
            <input type="text" class="form-control" placeholder="Вариант ответа">
            <button class="btn btn-outline-danger" type="button" onclick="this.closest('.input-group').remove()">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
        </div>
        <button type="button" class="btn btn-outline-primary" id="addSelectOption">
          <i class="bi bi-plus"></i> Добавить вариант
        </button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
        <button type="button" class="btn btn-primary" id="saveSelectField">Сохранить</button>
      </div>
    </div>
  </div>
</div>

<!-- Модальное окно для поля-файла -->
<div class="modal fade" id="fileFieldModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Настройка поля-файла</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="form-floating mb-3">
          <select class="form-select" id="fileTypes">
            <option value="*">Любые файлы</option>
            <option value="image/*">Только изображения</option>
            <option value=".pdf">Только PDF</option>
            <option value=".doc,.docx">Только Word документы</option>
          </select>
          <label>Тип файлов</label>
        </div>
        <div class="form-floating mb-3">
          <input type="number" class="form-control" id="maxFileSize" value="5" min="1" max="100">
          <label>Максимальный размер (МБ)</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
        <button type="button" class="btn btn-primary" id="saveFileField">Сохранить</button>
      </div>
    </div>
  </div>
</div>
