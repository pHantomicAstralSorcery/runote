import Quill from 'quill';
import 'quill/dist/quill.snow.css'; // Используем стиль snow

document.addEventListener('DOMContentLoaded', () => {
    const editorElement = document.getElementById('editor');
    if (!editorElement) return;

    // Инициализация Quill
    const quill = new Quill(editorElement, {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, false] }], // Заголовки H1, H2
                ['bold', 'italic', 'underline'], // Форматирование текста
                ['link', 'image'], // Вставка ссылок и изображений
                [{ list: 'ordered' }, { list: 'bullet' }], // Списки
                ['clean'] // Очистка форматирования
            ]
        }
    });

    // Автосохранение
    let lastContent = '';
    setInterval(() => {
        const currentContent = quill.root.innerHTML; // Текущее содержимое редактора
        if (currentContent !== lastContent) {
            lastContent = currentContent;

            // Отправка данных на сервер
            fetch(`{{ route('documents.update', $document->id) }}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ content: currentContent }),
            })
            .then(response => {
                if (!response.ok) {
                    console.error('Ошибка при сохранении документа');
                } else {
                    console.log('Документ успешно сохранен');
                }
            })
            .catch(error => {
                console.error('Произошла ошибка:', error);
            });
        }
    }, 60000); // Каждые 60 секунд

    // Обработка кнопки "Сохранить"
    document.getElementById('saveDocument')?.addEventListener('click', () => {
        const content = quill.root.innerHTML;
        fetch(`{{ route('documents.update', $document->id) }}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ content: content }),
        })
        .then(response => response.json())
        .then(data => alert('Документ сохранён'))
        .catch(err => console.error('Ошибка при сохранении документа:', err));
    });

    // Вставка изображений
    const toolbar = quill.getModule('toolbar');
    toolbar.addHandler('image', () => {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.click();

        input.onchange = async () => {
            const file = input.files[0];
            const formData = new FormData();
            formData.append('upload', file);

            const response = await fetch(`{{ route('documents.uploadImage') }}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const result = await response.json();
            const range = quill.getSelection(true);
            quill.insertEmbed(range.index, 'image', result.default);
        };
    });
});