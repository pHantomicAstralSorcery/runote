<div class="card mb-4">
    <div class="card-header">
        <h4 class="mb-0">Управление ссылками на экземпляры тетрадей</h4>
    </div>
    <div class="card-body">
        <button class="btn btn-success mb-3" id="createNamedLinkBtn">
            <i class="bi bi-plus-circle me-2"></i>Создать новую ссылку
        </button>

        {{-- === ИЗМЕНЕНИЯ ЗДЕСЬ === --}}
        <div class="border rounded p-3 mb-3" id="massActionsBlock" style="display: none;">
            <h5 class="mb-3">Массовые операции</h5>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <button class="btn btn-warning" id="toggleAllLinksBtn">
                    <i class="bi bi-check2-circle"></i> Активировать/Деактивировать все
                </button>
                <button class="btn btn-danger" id="deleteAllLinksBtn">
                    <i class="bi bi-trash3"></i> Удалить все ссылки
                </button>
                {{-- Заменяем текстовый прогресс на кнопку --}}
                <a href="{{ route('notebooks.overall_statistics', $notebook) }}" class="btn btn-info ms-md-auto">
                    <i class="bi bi-bar-chart-line"></i> Статистика по ссылкам
                </a>
            </div>
        </div>
        {{-- КОНЕЦ БЛОКА ИЗМЕНЕНИЙ --}}

        <div id="namedLinksList">
            <p class="text-muted">Загрузка ссылок...</p>
        </div>
    </div>
</div>

{{-- Modal for creating/editing Named Links --}}
<div class="modal fade" id="namedLinkModal" tabindex="-1" aria-labelledby="namedLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="namedLinkModalLabel">Создать/Редактировать ссылку</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="namedLinkForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="_method" id="namedLinkMethod" value="POST">
                    <input type="hidden" name="named_link_id" id="namedLinkId">
                    @include('components.input', [
                        'id' => 'namedLinkTitle',
                        'name' => 'title',
                        'label' => 'Имя ученика / Название ссылки',
                        'type' => 'text',
                        'required' => true
                    ])
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="namedLinkIsActive" name="is_active" checked>
                        <label class="form-check-label" for="namedLinkIsActive">Активна</label>
                    </div>
                    <div class="mb-3" id="namedLinkTokenDisplay" style="display:none;">
                        @include('components.input', [
                            'id' => 'namedLinkToken',
                            'name' => 'token',
                            'label' => '',
                            'type' => 'text',
                            'readonly' => true,
                            'inputGroup' => true,
                            'button' => [
                                'id' => 'copyNamedLinkBtn',
                                'text' => '<i class="bi bi-clipboard"></i> Копировать',
                                'class' => 'btn-outline-secondary'
                            ]
                        ])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>
