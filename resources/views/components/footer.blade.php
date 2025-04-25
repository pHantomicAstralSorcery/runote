<footer>
  <nav class="navbar bg-body-secondary">
    <div class="container d-flex align-items-center justify-content-between">
      <!-- Логотип -->
      <a class="navbar-brand" href="{{ route('/') }}">
        <img src="{{ asset('assets/img/logo/runote_logo.png') }}" alt="Runote_logo" width="100">
      </a>
      <!-- Текст копирайта -->
      <p class="mb-0">&copy; 2024-2025 Runote. Все права защищены.</p>
      <!-- Контактная информация -->
      <div class="footer-contacts d-flex align-items-center">
        <!-- Соц.сети -->
        <div class="me-3">
          <strong>Соц.сети:</strong>
          <div class="d-flex align-items-center">
            <a href="https://t.me/runote_test" target="_blank" rel="noopener noreferrer" aria-label="Telegram" class="me-2">
              <img src="{{ asset('assets/img/icons/tg.svg') }}" alt="Telegram Icon" class="icon">
            </a>
            <a href="https://vk.com/runote_test" target="_blank" rel="noopener noreferrer" aria-label="VK" class="me-2">
              <img src="{{ asset('assets/img/icons/vk.svg') }}" alt="VK Icon" class="icon">
            </a>
          </div>
        </div>
        <!-- Почта -->
        <div class="me-3">
          <strong>Почта:</strong>
          <p class="mb-0">inforunote@gmail.com</p>
        </div>
        <!-- Адрес -->
        <div>
          <strong>Адрес:</strong>
          <address class="mb-0">ул. Энтузиастов, 17, Челябинск, Россия</address>
        </div>
      </div>
    </div>
  </nav>
</footer>
