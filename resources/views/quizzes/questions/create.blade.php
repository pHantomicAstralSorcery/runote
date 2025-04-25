@extends('welcome')
@section('title', 'Создание вопроса')
@section('content')
<div class="container">
    <a href="{{ route('questions.index', $quiz->id) }}" class="btn btn-sm btn-secondary my-2">← Вернуться к списку вопросов</a>
    <h1 class="text-center my-4">Добавить вопрос к "{{ $quiz->title }}"</h1>
    <div class="row">
        <div class="col"></div>
        <div class="col col-md-6 col-lg-10">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('questions.store', $quiz->id) }}" method="POST" enctype="multipart/form-data" id="question-form">
                @csrf

<div id="image-preview" class="my-3 border rounded d-flex align-items-center justify-content-center position-relative bg-light"
     style="width: 100%; height: 300px;">
    <span class="text-muted">Выберите изображение (600x300)</span>
    <button id="remove-image" type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 d-none">
        ⨉
    </button>
</div>

@component('components.input', [
    'type' => 'file',
    'id' => 'question_image',
    'name' => 'question_image',
    'label' => 'Баннер (необязательно)',
    'class' => 'form-control'
]) @endcomponent

@component('components.input', [
    'type' => 'text',
    'id' => 'question_text',
    'name' => 'question_text',
    'label' => 'Вопрос',
    'placeholder' => '',
]) @endcomponent

@component('components.textarea', [
    'id' => 'question_description',
    'name' => 'question_description',
    'label' => 'Описание вопроса (необязательно)',
    'placeholder' => 'Введите описание вопроса',
]) @endcomponent

@component('components.select', [
    'id' => 'question_type',
    'name' => 'question_type',
    'label' => 'Тип вопроса',
    'placeholder' => 'Выберите тип вопроса',
    'options' => [
        'single' => 'С одним вариантом ответа',
        'multiple' => 'С несколькими вариантами ответа',
        'text' => 'Самостоятельный ввод ответа',
    ],
    'selected' => old('question_type'),
'placeholderDisabled' => true
]) @endcomponent

<div id="options-section" style="display: none;">
    @component('components.input', [
        'type' => 'number',
        'id' => 'question_points',
        'name' => 'question_points',
        'label' => 'Баллы',
        'value' => 1,
        'min' => 1,
    ]) @endcomponent
    <h5>Варианты ответов</h5>
    <div id="options-container"></div>
    <div class="d-flex justify-content-between mt-3">
        <button type="button" class="btn btn-outline-success" id="add-option">+ Добавить вариант</button>
        <button type="button" class="btn btn-success" id="save-answers" disabled>✓ Сохранить ответы</button>
        <button type="button" class="btn btn-primary" id="edit-answers" disabled>↺ Изменить ответы</button>
        <button type="submit" class="btn btn-outline-primary" id="submit-btn" disabled>+ Добавить вопрос</button>
    </div>
</div>

</form>
        </div>
        <div class="col"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const questionTypeSelect = document.getElementById('question_type');
    const optionsContainer = document.getElementById('options-container');
    const addOptionButton = document.getElementById('add-option');
    const saveAnswersButton = document.getElementById('save-answers');
    const editAnswersButton = document.getElementById('edit-answers');
    const submitButton = document.getElementById('submit-btn');
    const optionsSection = document.getElementById('options-section');
    const form = document.getElementById('question-form');
    const questionImageInput = document.getElementById('question_image');
    const imagePreview = document.getElementById('image-preview');
    const removeImageButton = document.getElementById('remove-image');

    // Функция для обновления вариантов в зависимости от типа вопроса
    function updateOptions() {
        const type = questionTypeSelect.value;
        optionsContainer.innerHTML = '';
        if (type === 'single') {
            optionsSection.style.display = 'block';
            for (let i = 0; i < 2; i++) {
                addOptionField(i, type, false);
            }
            addOptionButton.style.display = 'inline-block';
            saveAnswersButton.style.display = 'inline-block';
            submitButton.style.display = 'inline-block';
            editAnswersButton.style.display = 'inline-block';
            submitButton.disabled = true;
        } else if (type === 'multiple') {
            optionsSection.style.display = 'block';
            for (let i = 0; i < 3; i++) {
                addOptionField(i, type, false);
            }
            addOptionButton.style.display = 'inline-block';
            saveAnswersButton.style.display = 'inline-block';
            submitButton.style.display = 'inline-block';
            editAnswersButton.style.display = 'inline-block';
            submitButton.disabled = true;
        } else if (type === 'text') {
            optionsSection.style.display = 'block';
            const optionWrapper = document.createElement('div');
            optionWrapper.classList.add('form-group', 'option-group', 'd-flex', 'align-items-center', 'mt-3', 'p-2', 'border', 'rounded', 'bg-light');

            const textArea = document.createElement('textarea');
            textArea.name = 'options[0][option_text]';
            textArea.classList.add('form-control', 'ms-2');
            textArea.placeholder = 'Введите ваш ответ';
            textArea.required = true;

            textArea.addEventListener('input', checkIfSaveEnabled);

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'options[0][is_correct]';
            hiddenInput.value = true;

            optionWrapper.appendChild(textArea);
            optionsContainer.appendChild(optionWrapper);

            addOptionButton.style.display = 'none';
            saveAnswersButton.style.display = 'inline-block';
            submitButton.style.display = 'inline-block';
            submitButton.disabled = true;
            editAnswersButton.style.display = 'inline-block';
        } else {
            optionsSection.style.display = 'none';
            addOptionButton.style.display = 'none';
            saveAnswersButton.style.display = 'none';
            editAnswersButton.style.display = 'none';
            submitButton.style.display = 'none';
        }
    }

    // Проверка на включение кнопки сохранения
    function checkIfSaveEnabled() {
        const checkedAnswers = document.querySelectorAll('.answer-choice:checked');
        const textInputs = document.querySelectorAll('.option-group input[type="text"]');
        const textAreas = document.querySelectorAll('.option-group textarea');
        const type = questionTypeSelect.value;

        const allOptionsFilled = Array.from(textInputs).every(input => input.value.trim() !== '') &&
                                  Array.from(textAreas).every(textarea => textarea.value.trim() !== '');
        const hasCheckedAnswer = checkedAnswers.length > 0;

        let isValid = allOptionsFilled;

        if (type === 'single') {
            const hasCorrectAnswerSelected = document.querySelector('.answer-choice:checked') !== null;
            isValid = hasCorrectAnswerSelected && allOptionsFilled;
        }

        if (type === 'multiple') {
            const correctAnswers = document.querySelectorAll('.answer-choice[type="checkbox"]:checked').length;
            const incorrectAnswers = document.querySelectorAll('.answer-choice[type="checkbox"]:not(:checked)').length;

            if (correctAnswers < 2) {
                isValid = false;
            }
        }

        if (type === 'text') {
            isValid = allOptionsFilled;
        }

        saveAnswersButton.disabled = !isValid;
    }

    // Функция для удаления вариантов ответа
    function addRemoveButton(optionWrapper) {
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.classList.add('btn', 'btn-outline-danger', 'btn-sm', 'ms-2', 'remove-option');
        removeButton.innerHTML = '⨉';

        removeButton.addEventListener('click', function() {
            optionWrapper.remove();
            checkIfSaveEnabled();
        });

        optionWrapper.appendChild(removeButton);
    }

    // Функция для добавления нового варианта ответа
    function addOptionField(index, type, removable = true) {
        const optionWrapper = document.createElement('div');
        optionWrapper.classList.add('form-group', 'option-group', 'd-flex', 'align-items-center', 'mt-3', 'p-2', 'border', 'rounded', 'bg-light');

        if (type === 'multiple' || type === 'single') {
            const inputAnswer = document.createElement('input');
            inputAnswer.classList.add('answer-choice', 'me-2');
            inputAnswer.type = type === 'multiple' ? 'checkbox' : 'radio';
            inputAnswer.value = true;
            inputAnswer.name = type === 'multiple' ? `options[${index}][is_correct]` : 'correct_option';

            const inputText = document.createElement('input');
            inputText.type = 'text';
            inputText.name = `options[${index}][option_text]`;
            inputText.classList.add('form-control', 'ms-2');
            inputText.placeholder = 'Введите вариант ответа';
            inputText.required = true;

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `options[${index}][is_correct]`;
            hiddenInput.value = false;

            inputAnswer.addEventListener('change', () => {
                if (type === 'multiple') {
                    hiddenInput.value = inputAnswer.checked ? true : false;
                } else {
                    document.querySelectorAll('.answer-choice[type="radio"]').forEach(radio => {
                        radio.closest('.option-group').querySelector('input[type="hidden"]').value = false;
                    });
                    hiddenInput.value = true;
                }
                checkIfSaveEnabled();
            });

            inputText.addEventListener('input', checkIfSaveEnabled);

            optionWrapper.appendChild(inputAnswer);
            optionWrapper.appendChild(inputText);
            optionWrapper.appendChild(hiddenInput);

            if (removable) {
                addRemoveButton(optionWrapper);
            }

            optionsContainer.appendChild(optionWrapper);
        }

        checkIfSaveEnabled();
    }

    // Обработчик выбора изображения
    questionImageInput.addEventListener('change', function(event) {
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                imagePreview.style.backgroundImage = `url(${e.target.result})`;
                imagePreview.style.backgroundSize = 'cover';
                imagePreview.style.backgroundPosition = 'center';
                imagePreview.classList.add('border', 'border-secondary');
                imagePreview.innerHTML = ''; // Убираем текст
                imagePreview.appendChild(removeImageButton); // Добавляем кнопку удаления
                removeImageButton.classList.remove('d-none');
            };

            reader.readAsDataURL(file);
        }
    });

    // Обработчик удаления изображения
    removeImageButton.addEventListener('click', function() {
        imagePreview.style.backgroundImage = '';
        imagePreview.innerHTML = '<span class="text-muted">Выберите изображение (600x300)</span>';
        imagePreview.classList.remove('border', 'border-secondary');
        questionImageInput.value = ''; // Сбрасываем input file
        removeImageButton.classList.add('d-none'); // Скрываем кнопку удаления
    });

    questionTypeSelect.addEventListener('change', function() {
        updateOptions();
    });

    addOptionButton.addEventListener('click', function () {
        const index = optionsContainer.children.length;
        const type = questionTypeSelect.value;
        addOptionField(index, type);
    });

    saveAnswersButton.addEventListener('click', function() {
        let questionTypeHidden = document.querySelector('input[name="question_type"]');
        if (!questionTypeHidden) {
            questionTypeHidden = document.createElement('input');
            questionTypeHidden.type = 'hidden';
            questionTypeHidden.name = 'question_type';
            form.appendChild(questionTypeHidden);
        }
        questionTypeHidden.value = questionTypeSelect.value;

        saveAnswersButton.disabled = true;
        submitButton.disabled = false;
        editAnswersButton.disabled = false;

        document.querySelectorAll('.option-group input').forEach(input => {
            input.disabled = true;
        });
        document.querySelectorAll('.option-group textarea').forEach(textarea => {
            textarea.disabled = true;
        });
        document.querySelectorAll('.remove-option').forEach(button => {
            button.disabled = true;
        });
        addOptionButton.disabled = true;
        questionTypeSelect.disabled = true;
    });

    editAnswersButton.addEventListener('click', function() {
        saveAnswersButton.disabled = false;
        submitButton.disabled = true;
        editAnswersButton.disabled = true;

        document.querySelectorAll('.option-group input').forEach(input => {
            input.disabled = false;
        });
        document.querySelectorAll('.option-group textarea').forEach(textarea => {
            textarea.disabled = false;
        });
        document.querySelectorAll('.remove-option').forEach(button => {
            button.disabled = false;
        });
        addOptionButton.disabled = false;
        questionTypeSelect.disabled = false;
    });

    form.addEventListener('submit', function(event) {
        const options = [];

        document.querySelectorAll('.option-group').forEach((group, index) => {
            const optionText = group.querySelector('input[type="text"], textarea').value.trim();
            const isCorrect = group.querySelector('input[type="hidden"]') ? group.querySelector('input[type="hidden"]').value : true;

            options.push({
                [`options[${index}][option_text]`]: optionText,
                [`options[${index}][is_correct]`]: isCorrect,
            });
        });

        options.forEach(option => {
            Object.entries(option).forEach(([key, value]) => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = key;
                hiddenInput.value = value;
                form.appendChild(hiddenInput);
            });
        });
    });

    updateOptions();
});

</script>

@endsection
