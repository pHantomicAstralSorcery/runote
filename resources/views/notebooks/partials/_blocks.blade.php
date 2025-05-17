<ul id="blocks-list" class="list-unstyled">
  @foreach($page->blocks as $block)
    <li class="block-item mb-3 p-2 border"
        draggable="true"
        data-id="{{ $block->id }}">
      {!! $block->data['html'] !!}
    </li>
  @endforeach
</ul>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('blocks-list');
  let dragSrcEl = null;

  // Функция для применения CSS‑класса при dragover
  function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    return false;
  }

  // Событие dragstart: сохраняем исходный элемент
  function handleDragStart(e) {
    dragSrcEl = this;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
    this.classList.add('dragging');
  }

  // Событие drop: меняем местами содержание и удаляем класс
  function handleDrop(e) {
    e.stopPropagation();
    if (dragSrcEl !== this) {
      // Меняем в DOM
      const temp = dragSrcEl.innerHTML;
      dragSrcEl.innerHTML = this.innerHTML;
      this.innerHTML = temp;
      // После визуальной перестановки отправляем новый порядок на сервер
      updateOrderOnServer();
    }
    return false;
  }

  // Событие dragend: убираем класс
  function handleDragEnd() {
    this.classList.remove('dragging');
  }

  // Навешиваем обработчики на каждый элемент
  function addDnDHandlers(item) {
    item.addEventListener('dragstart', handleDragStart);
    item.addEventListener('dragover', handleDragOver);
    item.addEventListener('drop', handleDrop);
    item.addEventListener('dragend', handleDragEnd);
  }

  // Инициализация: находим все .block-item
  Array.from(list.querySelectorAll('.block-item')).forEach(addDnDHandlers);

  // Функция сбора порядка и отправки AJAX
  function updateOrderOnServer() {
    const order = Array.from(list.children).map(li => li.dataset.id);
    fetch(window.location.pathname + '/blocks/reorder', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ order })
    })
    .then(res => {
      if (!res.ok) throw new Error('Network error');
    })
    .catch(err => console.error('Reorder failed:', err));
  }
});
</script>
