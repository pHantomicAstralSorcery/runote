{{-- resources/views/notebooks/partials/_toolbar.blade.php --}}
<div class="btn-toolbar mb-2" role="toolbar">
  <div class="btn-group me-2" role="group">
    <button type="button" class="btn btn-outline-secondary" data-cmd="bold">
      <i class="bi bi-type-bold"></i>
    </button>
    <button type="button" class="btn btn-outline-secondary" data-cmd="italic">
      <i class="bi bi-type-italic"></i>
    </button>
    <button type="button" class="btn btn-outline-secondary" data-cmd="underline">
      <i class="bi bi-underline"></i>
    </button>
  </div>
  <div class="btn-group me-2" role="group">
    <button type="button" class="btn btn-outline-secondary" data-cmd="insertUnorderedList">
      <i class="bi bi-list-ul"></i>
    </button>
    <button type="button" class="btn btn-outline-secondary" data-cmd="insertOrderedList">
      <i class="bi bi-list-ol"></i>
    </button>
  </div>
  <div class="btn-group" role="group">
    <button type="button" class="btn btn-outline-secondary" data-cmd="justifyLeft">
      <i class="bi bi-text-start"></i>
    </button>
    <button type="button" class="btn btn-outline-secondary" data-cmd="justifyCenter">
      <i class="bi bi-text-center"></i>
    </button>
    <button type="button" class="btn btn-outline-secondary" data-cmd="justifyRight">
      <i class="bi bi-text-end"></i>
    </button>
  </div>
</div>
