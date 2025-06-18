@extends('welcome')
@section('title', 'Копия ' . $notebook->title . ' для ' . $link->title)

@section('content')
<style>
    /* Базовые стили для контейнера уведомлений */
    #notificationContainer {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 1050; /* Above Bootstrap modals */
        display: flex;
        flex-direction: column-reverse; /* New notifications at the bottom */
        align-items: flex-end;
    }
    /* Styles for individual notifications */
    .notification {
        padding: 0.75rem 1rem;
        margin-top: 0.5rem;
        border-radius: 0.375rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        opacity: 0;
        transform: translateY(1.25rem);
        transition: opacity 0.3s ease-out, transform 0.3s ease-out;
        max-width: 300px;
        text-align: right;
    }
    .notification.show {
        opacity: 1;
        transform: translateY(0);
    }
    /* Notification color schemes */
    .notification.success { background-color: #d4edda; border-color: #28a745; color: #155724; border: 1px solid; }
    .notification.error { background-color: #f8d7da; border-color: #dc3545; color: #721c24; border: 1px solid; }
    .notification.info { background-color: #d1ecf1; border-color: #17a2b8; color: #0c5460; border: 1px solid; }

    /* Additional styles for response fields to look good inline */
    .inline-field-container {
        display: inline-flex;
        align-items: center;
        margin: 0 0.25rem;
        vertical-align: middle;
        position: relative; /* For positioning the icon inside */
    }

    /* Styles for status icon inside the field */
    .field-status-icon-inside {
        position: absolute;
        right: 8px; /* Offset from the right edge of the field */
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.1em; /* Icon size */
        line-height: 1;
        pointer-events: none; /* So it doesn't interfere with field clicks */
    }

    /* Pulse animation for unanswered fields */
    @keyframes pulse-highlight {
        0% { border-color: #fca5a5; box-shadow: 0 0 0 0 rgba(252, 165, 165, 0.4); }
        50% { border-color: #fcd34d; box-shadow: 0 0 0 10px rgba(252, 211, 77, 0); }
        100% { border-color: #fca5a5; box-shadow: 0 0 0 0 rgba(252, 165, 165, 0); }
    }
    .animate-pulse {
        animation: pulse-highlight 2s infinite;
    }

    /* General container styles */
    .container-custom {
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
        padding: 1rem; /* p-4 */
    }
    @media (min-width: 768px) { /* md: */
        .container-custom {
            padding-left: 2rem; /* md:px-8 */
            padding-right: 2rem; /* md:px-8 */
        }
    }
    @media (min-width: 1024px) { /* lg: */
        .container-custom {
            padding-left: 3rem; /* lg:px-12 */
            padding-right: 3rem; /* lg:px-12 */
        }
    }

    /* Flexbox layout for main content and sidebar */
    .main-layout {
        display: flex;
        flex-direction: column; /* Stack on small screens */
        gap: 2rem; /* Space between columns */
    }

    @media (min-width: 992px) { /* Example breakpoint for sidebar */
        .main-layout {
            flex-direction: row; /* Side-by-side on larger screens */
            align-items: flex-start; /* Align content to the top */
        }
        .main-content-area {
            flex: 3; /* Main content takes more space */
            min-width: 0; /* Ensures content can shrink */
        }
        .sidebar-area {
            flex: 1; /* Sidebar takes less space */
            min-width: 280px; /* Minimum width for readability */
            position: sticky; /* Make sidebar sticky */
            top: 1rem; /* Distance from the top when sticky */
            align-self: flex-start; /* Align sidebar to its own start */
        }
    }


    /* Sizing for headings and text */
    h1 {
        font-size: 2.25rem; /* text-3xl */
        font-weight: 700; /* font-bold */
        color: #1f2937; /* text-gray-800 */
        margin-bottom: 0.5rem; /* mb-2 */
    }
    p {
        color: #4b5563; /* text-gray-600 */
        margin-bottom: 1rem; /* mb-4 */
    }
    em {
        font-style: italic;
    }

    /* Styles for content blocks */
    .content-block, .summary-block, .toc-block {
        background-color: #fff; /* bg-white */
        padding: 1.5rem; /* p-6 */
        border-radius: 0.5rem; /* rounded-lg */
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); /* shadow-md */
        margin-bottom: 1.5rem; /* mb-6 */
    }
    .prose { /* Imitating Tailwind prose typography */
        /* max-width: none; */ /* max-w-none */
    }
    .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
        margin-top: 1.5em;
        margin-bottom: 0.5em;
        font-weight: bold;
    }
    .prose h2 { font-size: 1.875rem; } /* text-2xl */
    .prose h3 { font-size: 1.5rem; } /* text-xl */

    hr {
        margin-top: 1.5rem; /* my-6 */
        margin-bottom: 1.5rem; /* my-6 */
        border-top: 1px solid #e5e7eb; /* border-gray-200 */
    }

    /* Styles for answer summary */
    .summary-block h4 {
        font-size: 1.25rem; /* text-xl */
        font-weight: 600; /* font-semibold */
        color: #374151; /* text-gray-700 */
        margin-bottom: 0.75rem; /* mb-3 */
    }
    .grid-summary {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.75rem; /* gap-3 */
        color: #374151; /* text-gray-700 */
    }
    @media (min-width: 768px) { /* md: */
        .grid-summary {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (min-width: 1024px) { /* lg: */
        .grid-template-columns: repeat(3, 1fr);
    }
    .grid-summary span {
        font-weight: 500; /* font-medium */
    }
    .text-blue-600 { color: #2563eb; }
    .text-yellow-600 { color: #eab308; }
    .text-green-600 { color: #16a34a; }
    .text-red-600 { color: #dc2626; }

    /* Styles for buttons */
    .btn-s {
        font-weight: 700; /* font-bold */
        padding: 0.5rem 1rem; /* py-2 px-4 */
        border-radius: 0.375rem; /* rounded-md */
        transition-property: all; /* transition */
        transition-duration: 300ms; /* duration-300 */
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); /* ease-in-out */
        transform: scale(1); /* transform */
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-s:hover {
        transform: scale(1.05); /* hover:scale-105 */
    }
    .btn-s:focus {
        outline: none; /* focus:outline-none */
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.5); /* focus:ring-4 focus:ring-blue-300 */
    }

    .btn-s-blue {
        background-color: #3b82f6; /* bg-blue-500 */
        color: #fff; /* text-white */
    }
    .btn-s-blue:hover {
        background-color: #2563eb; /* hover:bg-blue-600 */
    }
    .btn-s-green {
        background-color: #16a34a; /* bg-green-600 */
        color: #fff; /* text-white */
        padding: 0.5rem 1.5rem; /* py-2 px-6 */
        border-radius: 0.5rem; /* rounded-lg */
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); /* shadow-lg */
    }
    .btn-s-green:hover {
        background-color: #15803d; /* hover:bg-green-700 */
    }
    .btn-s-green:focus {
        box-shadow: 0 0 0 4px rgba(74, 222, 128, 0.5); /* focus:ring-4 focus:ring-green-300 */
    }

    /* Styles for Table of Contents */
    .toc-block h5 {
        font-size: 1.25rem; /* text-xl */
        font-weight: 600; /* font-semibold */
        color: #374151; /* text-gray-700 */
        margin-bottom: 0.75rem; /* mb-3 */
    }
    .list-unstyled {
        list-style: none;
        padding-left: 0;
    }
    .toc-list li {
        margin-bottom: 0.25rem;
    }
    .toc-list a {
        color: #2563eb; /* text-blue-600 */
        text-decoration: none;
        transition: color 200ms ease-in-out; /* transition-colors duration-200 */
    }
    .toc-list a:hover {
        text-decoration: underline;
        color: #1e40af; /* hover:text-blue-800 */
    }
    .ml-4 { margin-left: 1rem; } /* For nested TOC items */
    .ml-8 { margin-left: 2rem; } /* For even deeper nested TOC items */

    /* Styles for form elements */
    input[type="text"], select {
        display: inline-block;
        width: auto;
        min-width: 150px;
        /* margin-right: 0.5rem; /* mr-2 */ /* Removed, as icon will be inside */
        padding: 0.25rem 0.5rem; /* px-2 py-1 */
        padding-right: 30px; /* Padding for icon */
        border-radius: 0.375rem; /* rounded-md */
        border: 2px solid;
        vertical-align: middle;
        outline: none; /* focus:outline-none */
        transition: border-color 0.2s ease;
    }
    input[type="text"]:focus, select:focus, input[type="file"]:focus {
        border-color: #60a5fa; /* focus:ring-2 focus:ring-blue-400 */
        box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.5);
    }

    /* Specific styles for "file" type field */
    .file-input-main-container {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap; /* Allows elements to wrap to a new line if needed */
        gap: 0.5rem; /* Space between elements */
    }

    .file-input-wrapper {
        display: inline-flex;
        align-items: center;
        vertical-align: middle;
        position: relative;
        border: 2px solid; /* Border for the whole file block */
        border-radius: 0.375rem;
        padding: 0.25rem 0.5rem;
        padding-right: 30px; /* Added space for icon */
        background-color: #f9fafb; /* Light gray background */
        transition: border-color 0.2s ease;
    }
    .file-input-wrapper input[type="file"] {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        opacity: 0; /* Always hidden, but captures clicks */
        cursor: pointer;
    }
    .file-input-wrapper .file-label {
        flex-grow: 1;
        padding-right: 0.5rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .file-input-wrapper .file-upload-button {
        background-color: #e2e8f0; /* bg-gray-200 */
        padding: 0.1rem 0.5rem;
        border-radius: 0.25rem;
        cursor: pointer;
        font-weight: 600;
        border: 1px solid #cbd5e1; /* border-gray-300 */
    }
    .file-input-wrapper .file-upload-button:hover {
        background-color: #cbd5e1; /* hover:bg-gray-300 */
    }
    .file-input-wrapper.border-green-500 { border-color: #22c55e; }
    .file-input-wrapper.border-red-500 { border-color: #ef4444; }
    .file-input-wrapper.border-yellow-500 { border-color: #f59e0b; }
    .file-input-wrapper.animate-pulse {
        animation: pulse-highlight 2s infinite;
    }

    .file-status-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        /* margin-left: 0.5rem; /* Offset from file-input-wrapper */ /* Removed, as parent flexbox handles gap */
    }
    .file-status-group .btn-s-trash {
        background: none;
        border: none;
        color: #dc2626; /* text-red-600 */
        cursor: pointer;
        font-size: 1.2rem;
        padding: 0;
        line-height: 1;
    }
    .file-status-group .btn-s-trash:hover {
        color: #b91c1c; /* text-red-700 */
    }
    .file-status-group .file-download-link {
        color: #2563eb;
        text-decoration: none;
        font-size: 0.9rem;
        /* margin-top: 0.2rem; */ /* Removed, as align-items: center */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 180px; /* Limit width for long filenames */
    }
    .file-status-group .file-download-link:hover {
        text-decoration: underline;
    }


    input[type="range"] {
        -webkit-appearance: none;
        width: 100%;
        height: 8px;
        background: #d1d5db; /* bg-gray-300 */
        border-radius: 5px;
        outline: none;
        margin-right: 0.5rem; /* mr-2 */
        cursor: pointer;
    }
    input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        background: #3b82f6; /* bg-blue-500 */
        border-radius: 50%;
        cursor: pointer;
    }
    input[type="range"]::-moz-range-thumb {
        width: 20px;
        height: 20px;
        background: #3b82f6;
        border-radius: 50%;
        cursor: pointer;
    }
    .inline-flex {
        display: inline-flex;
        align-items: center;
    }
    .text-sm { font-size: 0.875rem; }
    .ml-2 { margin-left: 0.5rem; }

    /* Border colors for fields */
    .border-gray-300 { border-color: #d1d5db; }
    .border-green-500 { color: #22c55e; border-color: #22c55e; } /* Added color for icon */
    .border-red-500 { color: #ef4444; border-color: #ef4444; }   /* Added color for icon */
    .border-yellow-500 { color: #f59e0b; border-color: #f59e0b; } /* Added color for icon */

    /* Flexbox for bottom button */
    .flex-center-md-start {
        display: flex;
        justify-content: center; /* justify-center */
        align-items: center; /* items-center */
        margin-top: 1.5rem; /* mt-6 */
    }
    @media (min-width: 768px) { /* md: */
        .flex-center-md-start {
            justify-content: flex-start; /* md:justify-start */
        }
    }

    /* Error message */
    .alert-error {
        background-color: #fef2f2; /* bg-red-100 */
        border: 1px solid #ef4444; /* border-red-400 */
        color: #be123c; /* text-red-700 */
        padding: 0.75rem; /* p-3 */
        border-radius: 0.25rem; /* rounded */
        margin-top: 1rem; /* mt-4 */
    }

    /* --- Custom Confirmation Modal Styles --- */
    .custom-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black overlay */
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1060; /* Higher than notifications */
        visibility: hidden; /* Hidden by default */
        opacity: 0;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    .custom-modal-overlay.show {
        visibility: visible;
        opacity: 1;
    }
    .custom-modal-content {
        background-color: #fff;
        padding: 2rem;
        border-radius: 0.5rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        max-width: 400px;
        text-align: center;
        transform: translateY(-20px); /* Slightly move up on show */
        transition: transform 0.3s ease;
    }
    .custom-modal-overlay.show .custom-modal-content {
        transform: translateY(0);
    }
    .custom-modal-content h4 {
        margin-top: 0;
        margin-bottom: 1rem;
        color: #1f2937;
        font-size: 1.25rem;
    }
    .custom-modal-content p {
        margin-bottom: 1.5rem;
        color: #4b5563;
    }
    .custom-modal-actions {
        display: flex;
        justify-content: center;
        gap: 1rem;
    }
    .custom-modal-btn {
        padding: 0.6rem 1.2rem;
        border-radius: 0.375rem;
        cursor: pointer;
        font-weight: 600;
        transition: background-color 0.2s ease;
    }
    .custom-modal-btn.confirm {
        background-color: #dc2626; /* Red for destructive action */
        color: #fff;
    }
    .custom-modal-btn.confirm:hover {
        background-color: #b91c1c;
    }
    .custom-modal-btn.cancel {
        background-color: #e5e7eb; /* Light gray */
        color: #374151;
    }
    .custom-modal-btn.cancel:hover {
        background-color: #d1d5db;
    }

</style>

<div class="container-custom">
    <h1>{{ $notebook->title }} — {{ $link->title }}</h1>
    <p><em>Автор оригинала: {{ $notebook->user->login ?? 'Не указан' }}</em></p>

    <div class="main-layout">
        <div class="main-content-area">
            {{-- Container for displaying notebook content with inline fields --}}
            <div id="notebook-content-display" class="content-block prose">
                {{-- This block will be populated by JavaScript after HTML processing --}}
            </div>

            <hr>

            <div id="error-message" class="alert-error" style="display: none;"></div>
        </div>

        <div class="sidebar-area">
            {{-- Answer summary block --}}
            <div class="summary-block">
                <h4>Прогресс ответов:</h4>
                <div class="grid-summary">
                    <p>Всего полей: <span id="totalFieldsCount">0</span></p>
                    <p>Отвечено: <span id="answeredFieldsCount" class="text-blue-600">0</span></p>
                    <p>Неотвечено: <span id="unansweredFieldsCount" class="text-yellow-600">0</span></p>
                    <p>Правильных: <span id="correctFieldsCount" class="text-green-600">0</span></p>
                    <p>Неправильных: <span id="incorrectFieldsCount" class="text-red-600">0</span></p>
                </div>
                <button id="next-unanswered-btn" class="btn-s btn-s-blue" style="margin-top: 1rem; display: none;">
                    <i class="bi bi-chevron-down" style="margin-right: 0.5rem;"></i> Перейти к следующему неотвеченному
                </button>
            </div>

            {{-- Table of Contents block --}}
            <div id="table-of-contents" class="toc-block" style="display: none;">
                <h5>Оглавление</h5>
                <ul id="toc-list" class="list-unstyled toc-list">
                    {{-- Table of Contents will be generated here by JS --}}
                </ul>
            </div>

            <div class="flex-center-md-start">
                <button id="submit-responses" class="btn-s btn-s-green">
                    Отправить ответы
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Custom Confirmation Modal --}}
<div id="confirmationModalOverlay" class="custom-modal-overlay">
    <div class="custom-modal-content">
        <h4>Подтвердите удаление</h4>
        <p>Вы уверены, что хотите удалить этот ответ? Это действие нельзя будет отменить.</p>
        <div class="custom-modal-actions">
            <button id="confirmDeleteBtn" class="custom-modal-btn confirm">Удалить</button>
            <button id="cancelDeleteBtn" class="custom-modal-btn cancel">Отмена</button>
        </div>
    </div>
</div>

<script>
    // === Global state and helper functions ===
    // These maps will store the state of the page
    const responseFieldsMap = new Map();
    const studentResponsesMap = new Map();

    /**
     * Escapes HTML entities in a string.
     */
    function escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return unsafe;
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    /**
     * Displays a temporary notification.
     */
    function showNotification(message, type = 'info', duration = 3000) {
        let container = document.getElementById('notificationContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notificationContainer';
            document.body.appendChild(container);
        }
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        container.appendChild(notification);
        void notification.offsetWidth; // Trigger reflow for animation
        setTimeout(() => { notification.classList.add('show'); }, 10);
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => { if (notification.parentNode) notification.remove(); }, 300);
        }, duration);
    }

    // === Confirmation Modal Logic ===
    const confirmationModalOverlay = document.getElementById('confirmationModalOverlay');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    let onConfirmCallback = null; // Store the function to call on confirmation

    function showConfirmationModal(onConfirm) {
        onConfirmCallback = onConfirm; // Set the callback
        confirmationModalOverlay.classList.add('show');
    }

    function hideConfirmationModal() {
        confirmationModalOverlay.classList.remove('show');
        onConfirmCallback = null; // Clear the callback
    }

    confirmDeleteBtn.addEventListener('click', () => {
        if (typeof onConfirmCallback === 'function') {
            onConfirmCallback(); // Execute the stored callback
        }
        hideConfirmationModal();
    });

    cancelDeleteBtn.addEventListener('click', hideConfirmationModal);


    // === Main DOMContentLoaded Script ===
    document.addEventListener('DOMContentLoaded', () => {
        
        // Parse data passed from Blade
        const studentInstanceData = JSON.parse('{!! addslashes(json_encode($studentInstance->load(['snapshot.responseFields', 'studentResponses']))) !!}');
        const linkToken = '{{ $link->token }}';
        const notebookContentHtmlRaw = `{!! addslashes($studentInstance->snapshot->content_html ?? '') !!}`;

        if (studentInstanceData.snapshot && studentInstanceData.snapshot.response_fields) {
            studentInstanceData.snapshot.response_fields.forEach(field => {
                responseFieldsMap.set(field.uuid, field);
            });
        }

        if (studentInstanceData.student_responses) {
            studentInstanceData.student_responses.forEach(response => {
                studentResponsesMap.set(response.response_field_uuid, response);
            });
        }

        /**
         * Makes a request to the server to delete a file response.
         * @param {string} fieldUuid - The UUID of the field whose response is to be deleted.
         */
        function deleteFileResponseOnServer(fieldUuid) {

            const url = `/named-links/${linkToken}/delete-response/${fieldUuid}`;

            fetch(url, {
                method: 'POST', // Using POST to avoid issues, can be changed to DELETE
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw new Error(err.message || 'Ошибка сервера при удалении'); });
                }
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    // Perform UI update AFTER successful server response
                    updateFileFieldUIAfterDeletion(fieldUuid);
                    showNotification('Ответ успешно удален.', 'success');
                } else {
                    throw new Error(data.message || 'Не удалось удалить ответ.');
                }
            })
            .catch(error => {
                showNotification(error.message, 'error');
            });
        }
        
        /**
         * Handles the UI changes after a file has been successfully deleted.
         * @param {string} fieldUuid
         */
        function updateFileFieldUIAfterDeletion(fieldUuid) {
            const fileInputWrapper = document.querySelector(`.file-input-wrapper[data-field-uuid-ref="${fieldUuid}"]`);
            const fileStatusGroup = document.querySelector(`.file-status-group[data-field-uuid-ref="${fieldUuid}"]`);
            
            // Re-enable the file input. Cloning is a robust way to reset it.
            let fileInput = document.querySelector(`#field-${fieldUuid}[type="file"]`);
            if(fileInput) {
                const newFileInput = fileInput.cloneNode(true);
                newFileInput.value = ''; // Ensure it's empty
                newFileInput.removeAttribute('disabled');
                newFileInput.addEventListener('change', fileInputChangeHandler); // Re-attach listener
                fileInput.parentNode.replaceChild(newFileInput, fileInput);
            }

            if (fileInputWrapper) {
                 // Update visual state of the wrapper
                fileInputWrapper.classList.remove('border-green-500', 'border-red-500', 'border-yellow-500');
                fileInputWrapper.classList.add('border-gray-300', 'animate-pulse'); // Back to unanswered state
                fileInputWrapper.querySelector('.file-label').textContent = 'Файл не выбран';
                
                const uploadButton = fileInputWrapper.querySelector('.file-upload-button');
                uploadButton.textContent = 'Выбрать файл';
                uploadButton.removeAttribute('disabled');
                
                const statusIconInside = fileInputWrapper.querySelector('.field-status-icon-inside');
                if (statusIconInside) {
                    statusIconInside.className = `field-status-icon-inside bi bi-question-circle-fill text-yellow-600`;
                }
            }

            // Hide the download/trash button group
            if (fileStatusGroup) {
                fileStatusGroup.style.display = 'none';
            }

            // Remove response from client-side map and update summary
            studentResponsesMap.delete(fieldUuid);
            updateSummaryCounts();
        }

        /**
         * Handles the change event for file inputs.
         */
        function fileInputChangeHandler() {
            const fieldUuid = this.dataset.fieldId;
            const fileInputWrapper = document.querySelector(`.file-input-wrapper[data-field-uuid-ref="${fieldUuid}"]`);
            const fileLabel = fileInputWrapper.querySelector('.file-label');
            const statusIconInside = fileInputWrapper.querySelector('.field-status-icon-inside');

            if (this.files.length > 0) {
                fileLabel.textContent = this.files[0].name;
                fileInputWrapper.classList.remove('border-green-500', 'border-red-500', 'animate-pulse');
                fileInputWrapper.classList.add('border-yellow-500');
                if (statusIconInside) {
                    statusIconInside.className = `field-status-icon-inside bi bi-question-circle-fill text-yellow-600`;
                }
                // Mark as temporarily answered for summary counts
                studentResponsesMap.set(fieldUuid, { user_input: 'temp', is_correct: null });
            } else {
                // If selection was cancelled
                fileLabel.textContent = 'Файл не выбран';
            }
            updateSummaryCounts();
        }

        /**
         * Updates the summary counters in the sidebar.
         */
        function updateSummaryCounts() {
            let total = responseFieldsMap.size;
            let answered = 0;
            let correct = 0;
            let incorrect = 0;

            responseFieldsMap.forEach((fieldDef, uuid) => {
                const response = studentResponsesMap.get(uuid);
                if (response && (response.user_input || response.user_input === 'temp')) {
                    answered++;
                    if (response.is_correct === true) correct++;
                    else if (response.is_correct === false) incorrect++;
                }
            });

            document.getElementById('totalFieldsCount').textContent = total;
            document.getElementById('answeredFieldsCount').textContent = answered;
            document.getElementById('unansweredFieldsCount').textContent = total - answered;
            document.getElementById('correctFieldsCount').textContent = correct;
            document.getElementById('incorrectFieldsCount').textContent = incorrect;
            
            const nextUnansweredBtn = document.getElementById('next-unanswered-btn');
            if ((total - answered) > 0) {
                nextUnansweredBtn.style.display = 'inline-flex';
            } else {
                nextUnansweredBtn.style.display = 'none';
            }
        }
        
        /**
         * Renders the notebook content with all interactive fields.
         */
        function renderContentWithInlineFields(htmlContent) {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = htmlContent;

            tempDiv.querySelectorAll('.response-field-wrapper').forEach(wrapper => {
                const field = wrapper.querySelector('.response-field');
                if (!field) { wrapper.remove(); return; }

                const uuid = field.dataset.uuid;
                const fieldType = field.dataset.type;
                const fieldLabel = field.dataset.label || `[${fieldType} field]`;
                const fieldDefinition = responseFieldsMap.get(uuid);
                let studentResponse = studentResponsesMap.get(uuid);

                let fieldHtml = '';
                const fieldId = `field-${uuid}`;
                let borderColorClass = 'border-gray-300 animate-pulse';
                let statusIconClass = 'bi-question-circle-fill';
                let statusIconColorClass = 'text-yellow-600';

                if (studentResponse && studentResponse.user_input) {
                    borderColorClass = 'border-yellow-500'; // Default for answered
                    if (studentResponse.is_correct === true) {
                        borderColorClass = 'border-green-500';
                        statusIconClass = 'bi-check-circle-fill';
                        statusIconColorClass = 'text-green-600';
                    } else if (studentResponse.is_correct === false) {
                        borderColorClass = 'border-red-500';
                        statusIconClass = 'bi-x-circle-fill';
                        statusIconColorClass = 'text-red-600';
                    }
                }
                
                // Build the HTML for each field type
                if (fieldType === 'file') {
                    const validationRules = fieldDefinition?.validation_rules || {};
                    const acceptValue = validationRules.accept || '*/*';
                    
                    let initialFileName = 'Файл не выбран';
                    let showTrashButton = false;
                    let downloadLinkHtml = '';

                    if (studentResponse && studentResponse.user_input) {
                        try {
                            const fileData = JSON.parse(studentResponse.user_input);
                            if (fileData && fileData.url) {
                                initialFileName = fileData.name || 'Загруженный файл';
                                showTrashButton = true;
                                downloadLinkHtml = `<a href="${escapeHtml(fileData.url)}" target="_blank" class="file-download-link" title="${escapeHtml(initialFileName)}"><i class="bi bi-download"></i> <span class="file-name-display">${escapeHtml(initialFileName)}</span></a>`;
                            }
                        } catch(e) { /* was not json, likely old format */ }
                    }

                    fieldHtml = `
                        <div class="file-input-main-container">
                            <div class="file-input-wrapper ${borderColorClass}" data-field-uuid-ref="${uuid}">
                                <span class="file-label">${escapeHtml(initialFileName)}</span>
                                <button type="button" class="file-upload-button">Выбрать файл</button>
                                <input type="file" id="${fieldId}" data-field-id="${uuid}" accept="${escapeHtml(acceptValue)}">
                                <i class="field-status-icon-inside bi ${statusIconClass} ${statusIconColorClass}"></i>
                            </div>
                            <div class="file-status-group" data-field-uuid-ref="${uuid}" style="${showTrashButton ? '' : 'display:none;'}">
                                ${downloadLinkHtml}
                                <button type="button" class="btn-s-trash" data-uuid="${uuid}" title="Удалить файл">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    wrapper.outerHTML = `<span class="inline-field-container">${fieldHtml}</span>`;

                } else {
                    // Handle other field types like text, select, etc.
                    if (fieldType === 'text') {
                        fieldHtml = `<input type="text" id="${fieldId}" data-field-id="${uuid}" class="${borderColorClass}" placeholder="${escapeHtml(fieldLabel)}" value="${escapeHtml(studentResponse?.user_input || '')}">`;
                    } else if (fieldType === 'select') {
                        const options = fieldDefinition?.correct_answers?.options || [];
                        let optionsHtml = options.map(opt => `<option value="${escapeHtml(opt)}" ${studentResponse?.user_input === opt ? 'selected' : ''}>${escapeHtml(opt)}</option>`).join('');
                        fieldHtml = `<select id="${fieldId}" data-field-id="${uuid}" class="${borderColorClass}"><option value="">Выберите...</option>${optionsHtml}</select>`;
                    }
                    // Wrap other fields
                    wrapper.outerHTML = `
                        <span class="inline-field-container">
                            ${fieldHtml}
                            <i class="field-status-icon-inside bi ${statusIconClass} ${statusIconColorClass}"></i>
                        </span>`;
                }
            });
            
            // Set final HTML and attach event listeners
            const displayContainer = document.getElementById('notebook-content-display');
            displayContainer.innerHTML = tempDiv.innerHTML;

            // Attach listeners after rendering
            displayContainer.querySelectorAll('.btn-s-trash').forEach(button => {
                button.addEventListener('click', function() {
                    const uuid = this.dataset.uuid;
                    // Show confirmation modal and pass the deletion function as a callback
                    showConfirmationModal(() => deleteFileResponseOnServer(uuid));
                });
            });

            displayContainer.querySelectorAll('.file-upload-button').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.file-input-wrapper').querySelector('input[type="file"]').click();
                });
            });

            displayContainer.querySelectorAll('input[type="file"]').forEach(fileInput => {
                fileInput.addEventListener('change', fileInputChangeHandler);
            });
            
            // Also attach change listeners to other input types to update state
            displayContainer.querySelectorAll('input[type="text"], select').forEach(input => {
                input.addEventListener('change', function() {
                    const uuid = this.dataset.fieldId;
                    studentResponsesMap.set(uuid, { user_input: this.value, is_correct: null });
                    updateSummaryCounts();
                    // Update field border/icon
                    this.classList.remove('animate-pulse', 'border-green-500', 'border-red-500');
                    this.classList.add('border-yellow-500');
                    const icon = this.nextElementSibling;
                    if(icon && icon.classList.contains('field-status-icon-inside')) {
                        icon.className = 'field-status-icon-inside bi bi-question-circle-fill text-yellow-600';
                    }
                });
            });
        }
        
        /**
         * Submits all responses to the server.
         */
        function submitResponses() {
            const formData = new FormData();
            
            const inputs = document.querySelectorAll('#notebook-content-display input[data-field-id], #notebook-content-display select[data-field-id]');

            inputs.forEach((el, index) => {
                const uuid = el.dataset.fieldId;
                const fieldType = responseFieldsMap.get(uuid)?.field_type;
                
                formData.append(`responses[${index}][uuid]`, uuid);
                
                if (fieldType === 'file') {
                    if (el.files && el.files.length > 0) {
                        formData.append(`responses[${index}][input]`, el.files[0]);
                    } else {
                        const studentResponse = studentResponsesMap.get(uuid);
                        formData.append(`responses[${index}][input]`, studentResponse?.user_input || '');
                    }
                } else {
                    formData.append(`responses[${index}][input]`, el.value);
                }
            });

            // === FIX START: More robust fetch handling ===
            fetch(`/named-links/${linkToken}/submit`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => {
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json().then(data => {
                        if (!response.ok) {
                            throw new Error(data.message || `Ошибка сервера: ${response.status}`);
                        }
                        return data;
                    });
                } else {
                    return response.text().then(text => {
                        throw new Error('Критическая ошибка сервера. Ответ не в формате JSON.');
                    });
                }
            })
            .then(data => {
                if (data.status !== 'success') {
                    throw new Error(data.message || 'Сервер вернул ответ, но со статусом ошибки.');
                }
                showNotification('Ответы успешно сохранены!', 'success');
                setTimeout(() => window.location.reload(), 1500);
            })
            .catch((error) => {
                showNotification(error.message, 'error');
            });
            // === FIX END ===
        }

        // === Page Initialization ===
        renderContentWithInlineFields(notebookContentHtmlRaw);
        updateSummaryCounts();
        document.getElementById('submit-responses').addEventListener('click', submitResponses);
        document.getElementById('next-unanswered-btn').onclick = () => {
            const firstUnansweredInput = document.querySelector('#notebook-content-display .animate-pulse');
            if (firstUnansweredInput) {
                firstUnansweredInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstUnansweredInput.focus();
            }
        };
    });
</script>
@endsection
