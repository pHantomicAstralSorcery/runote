<footer>
  <nav class="navbar bg-body-secondary py-4 shadow-sm"> <!-- Добавлен py-4 для большего вертикального отступа и shadow-sm для легкой тени -->
    <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between text-center text-md-start">
      <!-- Логотип -->
      <a class="navbar-brand mb-3 mb-md-0 me-md-4" href="{{ route('/') }}"> <!-- Добавлен отступ справа на md+ экранах -->
        <img src="{{ asset('assets/img/logo/runote_logo.png') }}" alt="Runote_logo" width="100">
      </a>
      <!-- Текст копирайта -->
      <p class="mb-3 mb-md-0 text-muted">© 2024-2025 Runote. Все права защищены.</p> <!-- text-muted для более мягкого цвета -->
      <!-- Контактная информация -->
      <div class="footer-contacts d-flex flex-wrap justify-content-center align-items-center mt-3 mt-md-0 mx-md-auto"> 
        <!-- Соц.сети -->
        <div class="me-md-4 mb-2 mb-md-0 text-center"> <!-- Увеличен отступ справа -->
          <strong class="d-block mb-1">Соц.сети:</strong> <!-- d-block и mb-1 для лучшего расположения заголовка -->
          <div class="d-flex align-items-center justify-content-center">
            <a href="https://t.me/runote_test" target="_blank" rel="noopener noreferrer" aria-label="Telegram" class="me-2 footer-icon-link">
              <img src="{{ asset('assets/img/icons/tg.svg') }}" alt="Telegram Icon" class="icon" width="28" height="28"> <!-- Уменьшен размер иконок -->
            </a>
            <a href="https://vk.com/runote_test" target="_blank" rel="noopener noreferrer" aria-label="VK" class="me-2 footer-icon-link">
              <img src="{{ asset('assets/img/icons/vk.svg') }}" alt="VK Icon" class="icon" width="28" height="28">
            </a>
          </div>
        </div>
        <!-- Почта -->
        <div class="me-md-4 mb-2 mb-md-0 text-center"> <!-- Увеличен отступ справа -->
          <strong class="d-block mb-1">Почта:</strong>
          <p class="mb-0 text-muted">inforunote@gmail.com</p>
        </div>
        <!-- Адрес -->
        <div class="text-center">
          <strong class="d-block mb-1">Адрес:</strong>
          <address class="mb-0 text-muted">ул. Энтузиастов, 17, Челябинск, Россия</address>
        </div>
      </div>
    </div>
  </nav>
</footer>
