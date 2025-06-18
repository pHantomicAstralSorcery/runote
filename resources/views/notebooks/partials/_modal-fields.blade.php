{{-- resources/views/notebooks/partials/_modal-fields.blade.php --}}

{{-- Text Field Modal --}}
<div class="modal fade" id="textFieldModal" tabindex="-1" aria-labelledby="textFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textFieldModalLabel">Настройки текстового поля</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('components.input', [
                    'id' => 'textFieldLabel',
                    'name' => 'textFieldLabel', // Добавил name, так как компонент input его ожидает
                    'label' => 'Метка поля', // Добавил label для наглядности
                    'placeholder' => 'Введите метку для поля'
                ])
                @include('components.input', [
                    'id' => 'textFieldCorrectAnswer',
                    'name' => 'textFieldCorrectAnswer', // Добавил name
                    'label' => 'Правильный ответ', // Добавил label
                    'placeholder' => 'Введите правильный ответ'
                ])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="saveTextField">Сохранить</button>
            </div>
        </div>
    </div>
</div>


{{-- Select Field Modal --}}
<div class="modal fade" id="selectFieldModal" tabindex="-1" aria-labelledby="selectFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="selectFieldModalLabel">Настройки поля-списка</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('components.input', [
                    'id' => 'selectFieldLabel',
                    'name' => 'selectFieldLabel', // Добавил name
                    'label' => 'Метка поля', // Добавил label
                    'placeholder' => 'Введите метку для поля'
                ])
                <hr>
                <h6>Варианты ответов <small class="text-muted">(Отметьте правильный)</small></h6>
                <div id="selectOptions">
                    {{-- Options will be appended here by JS --}}
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addSelectOption">
                    <i class="bi bi-plus-lg"></i> Добавить вариант
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="saveSelectField">Сохранить</button>
            </div>
        </div>
    </div>
</div>

{{-- File Field Modal --}}
<div class="modal fade" id="fileFieldModal" tabindex="-1" aria-labelledby="fileFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileFieldModalLabel">Настройки поля-файла</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('components.input', [
                    'id' => 'fileFieldLabel',
                    'name' => 'fileFieldLabel', // Добавил name
                    'label' => 'Метка поля',
                    'placeholder' => 'Введите метку для поля'
                ])
                @include('components.select', [
                    'id' => 'fileFieldAcceptPreset',
                    'name' => 'fileFieldAcceptPreset', // Добавил name
                    'label' => 'Типы файлов',
                    'options' => [
                        '*' => 'Все файлы (*.*)',
                        'image/*' => 'Только изображения (jpg, png, gif...)',
                        '.doc,.docx,.pdf,.xls,.xlsx,.ppt,.pptx' => 'Документы (doc, pdf, xls...)',
                        'application/zip,application/x-rar-compressed,.7z' => 'Архивы (zip, rar, 7z)',
                    ],
                    'placeholder' => 'Выберите тип файлов'
                ])
                @include('components.input', [
                    'id' => 'fileFieldAcceptManual',
                    'name' => 'fileFieldAcceptManual', // Добавил name
                    'label' => '', // Оставляем пустым, так как есть form-text
                    'placeholder' => 'Или введите вручную (e.g. .pdf,.jpg)'
                ])
                <div class="form-text mb-3">Выбор из списка автоматически заполнит поле ниже. Для сохранения используется значение из текстового поля.</div>

                @include('components.input', [
                    'id' => 'fileFieldMaxSize',
                    'name' => 'fileFieldMaxSize', // Добавил name
                    'type' => 'number',
                    'label' => 'Максимальный размер файла (КБ)',
                    'value' => 2048,
                    'min' => 1
                ])
                <div class="form-text">Например, 2048 для 2 МБ.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="saveFileField">Сохранить</button>
            </div>
        </div>
    </div>
</div>

