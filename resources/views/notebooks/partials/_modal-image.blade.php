<div class="modal fade" id="imageInsertModal" tabindex="-1" aria-labelledby="imageInsertModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="imageInsertForm" onsubmit="return false;">
      <div class="modal-header">
        <h5 class="modal-title" id="imageInsertModalLabel">Вставить изображение</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <div class="modal-body">
        <!-- Вставка по URL -->
        <div class="mb-3">
          <label for="imageUrlInput" class="form-label">URL изображения</label>
          <input type="url" class="form-control" id="imageUrlInput" placeholder="https://example.com/image.png">
        </div>
        <div class="text-center my-3">
          <span>или</span>
        </div>
        <!-- Вставка с устройства -->
        <div class="mb-3">
          <label for="imageFileInput" class="form-label">Загрузить с устройства</label>
          <div id="imageDropZone" class="border rounded p-4 text-center" style="cursor:pointer;">
            <div id="dropZoneText">Перетащите файл сюда или нажмите для выбора</div>
            <input type="file" id="imageFileInput" accept="image/*" style="display:none;">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
        <button type="submit" class="btn btn-primary" id="insertImageConfirmBtn">Вставить</button>
      </div>
    </form>
  </div>
</div>
