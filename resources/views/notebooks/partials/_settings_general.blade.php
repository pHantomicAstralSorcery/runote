<div class="card mb-4">
    <div class="card-header">
        <h4 class="mb-0">Общие настройки тетради</h4>
    </div>
    <div class="card-body">
        <form id="notebookGeneralSettingsForm" data-update-url="{{ route('notebooks.updateGeneralSettings', $notebook) }}">
            @csrf
            @method('PUT')
            @include('components.input', [
                'id' => 'notebookTitle',
                'name' => 'title',
                'label' => 'Название тетради',
                'value' => $notebook->title,
                'required' => true
            ])
            @include('components.select', [
                'id' => 'notebookAccess',
                'name' => 'access',
                'label' => 'Доступ',
                'options' => [
                    'open' => 'Открытый',
                    'closed' => 'Закрытый'
                ],
                'selected' => $notebook->access,
                'placeholder' => 'Выберите доступ' {{-- Added placeholder --}}
            ])
            <button type="submit" class="btn btn-primary">Сохранить общие настройки</button>
        </form>
    </div>
</div>

