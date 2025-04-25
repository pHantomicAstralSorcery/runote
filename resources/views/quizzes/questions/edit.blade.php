@extends('welcome')
@section('title', 'Редактирование вопроса')
@section('content')
<div class="container">
  <a href="{{ route('questions.index', $quiz->id) }}" class="btn btn-sm btn-secondary my-2">← Вернуться к списку вопросов</a>
  <h1 class="text-center my-4">Редактировать вопрос "{{ $question->question_text }}"</h1>
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
      <form action="{{ route('questions.update', [$quiz->id, $question->id]) }}" method="POST" enctype="multipart/form-data" id="question-form">
        @csrf
        @method('PUT')

        <div id="image-preview" class="my-3 border rounded d-flex align-items-center justify-content-center position-relative bg-light"
             style="width: 100%; height: 300px;">
          @if($question->question_image)
          <img src="{{ asset('storage/' . $question->question_image) }}" alt="Question Image" style="max-width: 100%; max-height: 100%;">
          @else
          <span class="text-muted">Выберите изображение (600x300)</span>
          @endif
          <button id="remove-image" type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 {{ !$question->question_image ? 'd-none' : '' }}">
            ⨉
          </button>
 
        </div>
         <input type="hidden" name="remove_image" id="remove_image" value="0">

        @component('components.input', [
          'type' => 'file',
          'id' => 'question_image',
          'name' => 'question_image',
          'label' => 'Баннер (необязательно)',
          'class' => 'form-control'
        ])@endcomponent

        @component('components.input', [
          'type' => 'text',
          'id' => 'question_text',
          'name' => 'question_text',
          'label' => 'Вопрос',
          'value' => old('question_text', $question->question_text),
          'placeholder' => '',
        ])@endcomponent

        @component('components.textarea', [
          'id' => 'question_description',
          'name' => 'question_description',
          'label' => 'Описание вопроса (необязательно)',
          'value' => old('question_description', $question->question_description),
          'placeholder' => 'Введите описание вопроса',
        ])@endcomponent

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
          'selected' => old('question_type', $question->question_type),
'placeholderDisabled' => true,
          'disabled' => true,
        ])@endcomponent

        <div id="options-section">
          @component('components.input', [
            'type' => 'number',
            'id' => 'question_points',
            'name' => 'question_points',
            'label' => 'Баллы',
            'value' => old('question_points', $question->question_points),
            'min' => 1,
          ])@endcomponent
          <h5>Варианты ответов</h5>
          <div id="options-container">
            @foreach($question->options as $index => $option)
            <div class="form-group option-group d-flex align-items-center mt-3 p-2 border rounded bg-light">
              @if($question->question_type === 'single' || $question->question_type === 'multiple')
              <input class="answer-choice me-2"
                     type="{{ $question->question_type === 'multiple' ? 'checkbox' : 'radio' }}"
                     name="{{ $question->question_type === 'multiple' ? "options[{$index}][is_correct]" : 'correct_option' }}"
                     value="true"
                     {{ $option->is_correct ? 'checked' : '' }}>
              <input type="text"
                     name="options[{{ $index }}][option_text]"
                     class="form-control ms-2"
                     placeholder="Введите вариант ответа"
                     required
                     value="{{ old("options.$index.option_text", $option->option_text) }}">
              <input type="hidden"
                     name="options[{{ $index }}][is_correct]"
                     value="{{ $option->is_correct ? 'true' : 'false' }}">
              @if( ($question->question_type === 'single' && $index > 1) || ($question->question_type === 'multiple' && $index > 2) )
              <button type="button" class="btn btn-outline-danger btn-sm ms-2 remove-option">⨉</button>
              @endif
              @elseif($question->question_type === 'text')
              <textarea name="options[0][option_text]"
                        class="form-control ms-2"
                        placeholder="Введите ваш ответ"
                        required>{{ old('options.0.option_text', $option->option_text) }}</textarea>
              <input type="hidden"
                     name="options[0][is_correct]"
                     value="true">
              @endif
            </div>
            @endforeach
          </div>
          <div class="d-flex justify-content-between mt-3">
            <button type="button" class="btn btn-outline-success" id="add-option">+ Добавить вариант</button>
            <button type="button" class="btn btn-success" id="save-answers" disabled>✓ Сохранить ответы</button>
            <button type="button" class="btn btn-primary" id="edit-answers" disabled>↺ Изменить ответы</button>
            <button type="submit" class="btn btn-outline-primary" id="submit-btn" disabled>↺ Обновить вопрос</button>
          </div>
        </div>

      </form>
    </div>
    <div class="col"></div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
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
    const removeImageFlag = document.getElementById('remove_image');

    // Функция для обновления вариантов в зависимости от типа вопроса
    function updateOptions() {
        const type = questionTypeSelect.value;

        let index = 0;
        document.querySelectorAll('.option-group').forEach(group => {
            group.querySelector('input[type="text"], textarea').setAttribute('name', `options[${index}][option_text]`);
            group.querySelector('input[type="hidden"]').setAttribute('name', `options[${index}][is_correct]`);

            if (type === 'multiple') {
                group.querySelector('input.answer-choice').type = 'checkbox';
                group.querySelector('input.answer-choice').name = `options[${index}][is_correct]`;
            } else if (type === 'single') {
                group.querySelector('input.answer-choice').type = 'radio';
                group.querySelector('input.answer-choice').name = 'correct_option';
            }

            // Обработка изменений флажков/радио-кнопок
            const answerChoice = group.querySelector('.answer-choice');
            const hiddenInput = group.querySelector('input[type="hidden"]');
            if (answerChoice && hiddenInput) {
                answerChoice.addEventListener('change', () => {
                    hiddenInput.value = answerChoice.checked ? 'true' : 'false';
                    checkIfSaveEnabled();
                });
            }

            index++;
        });

        if (type === 'single' || type === 'multiple') {
            optionsSection.style.display = 'block';
            addOptionButton.style.display = 'inline-block';
            saveAnswersButton.style.display = 'inline-block';
            submitButton.style.display = 'inline-block';
            editAnswersButton.style.display = 'inline-block';
            submitButton.disabled = true;
        } else if (type === 'text') {
            optionsSection.style.display = 'block';
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

        checkIfSaveEnabled();
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

        removeButton.addEventListener('click', function () {
            optionWrapper.remove();
            checkIfSaveEnabled();
        });

        optionWrapper.appendChild(removeButton);
    }

// Обработка существующих кнопок удаления
    document.querySelectorAll('.remove-option').forEach(button => {
        button.addEventListener('click', function () {
            const optionWrapper = this.closest('.option-group');
            if (optionWrapper) {
                optionWrapper.remove();
                checkIfSaveEnabled(); // Перепроверяем состояние сохранения
            }
        });
    });

    // Функция для добавления нового варианта ответа
    function addOptionField(index, type, removable = true) {
        const optionWrapper = document.createElement('div');
        optionWrapper.classList.add(
            'form-group',
            'option-group',
            'd-flex',
            'align-items-center',
            'mt-3',
            'p-2',
            'border',
            'rounded',
            'bg-light'
        );

        if (type === 'multiple' || type === 'single') {
            const inputAnswer = document.createElement('input');
            inputAnswer.classList.add('answer-choice', 'me-2');
            inputAnswer.type = type === 'multiple' ? 'checkbox' : 'radio';
            inputAnswer.value = 'true';
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
            hiddenInput.value = 'false';

            inputAnswer.addEventListener('change', () => {
                hiddenInput.value = inputAnswer.checked ? 'true' : 'false';
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
    questionImageInput.addEventListener('change', function (event) {
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                imagePreview.style.backgroundImage = `url(${e.target.result})`;
                imagePreview.style.backgroundSize = 'cover';
                imagePreview.style.backgroundPosition = 'center';
                imagePreview.classList.add('border', 'border-secondary');
                imagePreview.innerHTML = ''; // Убираем текст
                imagePreview.appendChild(removeImageButton); // Добавляем кнопку удаления
                removeImageButton.classList.remove('d-none');
                removeImageFlag.value = '0'; // Сбрасываем флаг удаления изображения
            };

            reader.readAsDataURL(file);
        }
    });

    // Обработчик удаления изображения
    removeImageButton.addEventListener('click', function () {
        imagePreview.style.backgroundImage = '';
        imagePreview.innerHTML = '<span class="text-muted">Выберите изображение (600x300)</span>';
        imagePreview.classList.remove('border', 'border-secondary');
        questionImageInput.value = ''; // Сбрасываем input file
        removeImageButton.classList.add('d-none'); // Скрываем кнопку удаления
        removeImageFlag.value = '1'; // Устанавливаем флаг удаления изображения

        console.log('remove_image value after click:', removeImageFlag.value); // Добавлено для отладки
    });

    // Отключаем обработчик изменения типа вопроса
    // questionTypeSelect.addEventListener('change', function() {
    //     updateOptions();
    // });

    addOptionButton.addEventListener('click', function () {
        const index = optionsContainer.children.length;
        const type = questionTypeSelect.value;
        addOptionField(index, type);
    });

    saveAnswersButton.addEventListener('click', function () {
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

        document.querySelectorAll('.option-group input').forEach((input) => {
            input.disabled = true;
        });
        document.querySelectorAll('.option-group textarea').forEach((textarea) => {
            textarea.disabled = true;
        });
        document.querySelectorAll('.remove-option').forEach((button) => {
            button.disabled = true;
        });
        addOptionButton.disabled = true;
        questionTypeSelect.disabled = true;
    });

    editAnswersButton.addEventListener('click', function () {
        saveAnswersButton.disabled = false;
        submitButton.disabled = true;
        editAnswersButton.disabled = true;

        document.querySelectorAll('.option-group input').forEach((input) => {
            input.disabled = false;
        });
        document.querySelectorAll('.option-group textarea').forEach((textarea) => {
            textarea.disabled = false;
        });
        document.querySelectorAll('.remove-option').forEach((button) => {
            button.disabled = false;
        });
        addOptionButton.disabled = false;
        questionTypeSelect.disabled = false;
    });

    form.addEventListener('submit', function (event) {
        const options = [];
        const removeImageFlag = document.getElementById('remove_image');
        const fileInput = document.getElementById('question_image');

        // Если файл загружен, сбрасываем флаг удаления
        if (fileInput.files.length > 0) {
            removeImageFlag.value = '0';
        }

        document.querySelectorAll('.option-group').forEach((group, index) => {
            const optionText = group.querySelector('input[type="text"], textarea').value.trim();
            const isCorrect = group.querySelector('input[type="hidden"]')
                ? group.querySelector('input[type="hidden"]').value
                : 'false';

            options.push({
                [`options[${index}][option_text]`]: optionText,
                [`options[${index}][is_correct]`]: isCorrect,
            });
        });

        options.forEach((option) => {
            Object.entries(option).forEach(([key, value]) => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = key;
                hiddenInput.value = value;
                form.appendChild(hiddenInput);
            });
        });
    });

    // Запуск функции для обновления вариантов при загрузке страницы
    updateOptions();

    // Дополнительная инициализация для загруженных данных
    document.querySelectorAll('.answer-choice').forEach((input) => {
        const hiddenInput = input.closest('.option-group').querySelector('input[type="hidden"]');
        if (hiddenInput) {
            input.addEventListener('change', () => {
                hiddenInput.value = input.checked ? 'true' : 'false';
                checkIfSaveEnabled();
            });
        }
    });

    checkIfSaveEnabled();
});
</script>
@endsection
