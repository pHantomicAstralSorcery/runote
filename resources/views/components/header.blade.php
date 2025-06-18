<header>
    <nav class="navbar navbar-expand-lg">
        <div class="container container-fluid d-flex justify-content-between align-items-center">
            <!-- Логотип -->
            <a class="navbar-brand" href="{{ route('/') }}">
                <img src="{{ asset('assets/img/logo/runote_logo.png') }}" alt="Runote_logo" width="100">
            </a>

            <!-- Аватар и имя пользователя для мобильных -->
            @auth()
                <div class="d-flex align-items-center d-lg-none me-3 ms-auto">
                    <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('assets/img/static_img/static_avatar.jpg')}}" alt="User Avatar" class="rounded-circle avatar">
                    <span class="username">{{ Auth::user()->login }}</span>
                </div>
            @endauth
            @guest()
                 <div class="ms-auto d-flex flex-md-row order-lg-last d-none d-md-block">
                    <a href="{{ route('auth') }}" class="btn btn-outline-success mb-2 mb-md-0 me-md-2">Войти</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-secondary">Зарегистрироваться</a>
                </div>
            @endguest

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @if(session('admin_mode'))
                        <!-- НАВИГАЦИЯ В АДМИН-ПАНЕЛИ -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Управление пользователями</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.notebooks.index') ? 'active' : '' }}" href="{{ route('admin.notebooks.index') }}">Управление тетрадями</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.quizzes.index') ? 'active' : '' }}" href="{{ route('admin.quizzes.index') }}">Управление тестами</a>
                        </li>
                    @else
                        <!-- ОБЫЧНАЯ НАВИГАЦИЯ -->
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('/') }}">Главная</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('notebooks.index') }}"> Моя тетрадь</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('quizzes.index') }}"><span class="badge bg-success">NEW!</span> Тесты</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('about_us') }}">О нас</a>
                        </li>
                    @endif

                    @auth()
                        <li class="nav-item w-100 d-lg-none"><hr></li>
                        @if(session('admin_mode'))
                            <li class="nav-item d-lg-none">
                                <form action="{{ route('admin.toggle-mode') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="nav-link admin_panel btn btn-link">∅ Обычный режим</button>
                                </form>
                            </li>
                        @else
                            <li class="nav-item d-lg-none"><a class="nav-link" href="{{ route('quizzes.myQuizzes') }}"> Мои тесты</a></li>
                            <li class="nav-item d-lg-none"><a class="nav-link" href="{{ route('quizzes.completedQuizzes') }}"> Пройденные тесты</a></li>
                            @if(Auth::user()->isAdmin())
                                {{-- Changed admin.dashboard to admin.users.index --}}
                                <li class="nav-item d-lg-none"><a class="nav-link admin_panel" href="{{ route('admin.users.index') }}">※ Панель админа</a></li>
                            @endif
                        @endif
                        <hr>
                        <li class="nav-item d-lg-none"><a class="btn btn-outline-danger d-flex justify-content-center align-items-center w-100" href="{{ route('logout') }}">↳ Выйти</a></li>
                    @endauth
                </ul>

            @guest()
                 <div class="ms-auto d-flex flex-column d-lg-none">
                    <a href="{{ route('auth') }}" class="btn btn-outline-success mb-2 mb-md-0 me-md-2">Войти</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-secondary">Зарегистрироваться</a>
                </div>
            @endguest
                @auth()
                    <div class="dropdown profile-dropdown d-none d-lg-flex align-items-center ms-auto">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('assets/img/static_img/static_avatar.jpg')}}" alt="User Avatar" class="rounded-circle avatar">
                            <span class="username">{{ Auth::user()->login }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if(session('admin_mode'))
                                <li>
                                    <form action="{{ route('admin.toggle-mode') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item admin_panel">∅ Обычный режим</button>
                                    </form>
                                </li>
                            @else
                                <li><a class="dropdown-item" href="{{ route('notebooks.index') }}">⇱ Моя тетрадь</a></li>
                                <li><a class="dropdown-item" href="{{ route('quizzes.completedQuizzes') }}"> Пройденные тесты</a></li>
                                <li><a class="dropdown-item" href="{{ route('quizzes.myQuizzes') }}"> Мои тесты</a></li>
                                @if(Auth::user()->isAdmin())
                                    {{-- Changed admin.dashboard to admin.users.index --}}
                                    <li><a class="dropdown-item admin_panel" href="{{ route('admin.users.index') }}">※ Панель админа</a></li>
                                @endif
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item logout" href="{{ route('logout') }}">↳ Выйти</a></li>
                        </ul>
                    </div>
                @endauth
            </div>
        </div>
    </nav>
</header>
