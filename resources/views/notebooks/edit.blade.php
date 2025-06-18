@extends('welcome')

@section('title', 'Редактирование тетради: ' . $notebook->title)

@section('content')

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <a href="{{ route('notebooks.index') }}" class="btn btn-outline-secondary mb-2 mb-md-0">
            <i class="bi bi-arrow-left"></i> К списку тетрадей
        </a>
        <h1 class="text-center my-0 mx-auto" style="flex-grow: 1;">
            <span class="fw-light">Редактирование тетради:</span> <span id="notebook-title-header">{{ $notebook->title }}</span>
        </h1>
        <div style="min-width: 135px;"></div> <!-- Spacer -->
    </div>

    <ul class="nav nav-tabs mb-3" id="notebookEditorTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="editor-tab" data-bs-toggle="tab" data-bs-target="#editor-pane" type="button" role="tab" aria-controls="editor-pane" aria-selected="true">
                <i class="bi bi-pencil-square me-2"></i>Редактор
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings-pane" type="button" role="tab" aria-controls="settings-pane" aria-selected="false">
                <i class="bi bi-gear-fill me-2"></i>Настройки и ссылки
            </button>
        </li>
         <li class="nav-item" role="presentation">
            <button class="nav-link" id="versions-tab" data-bs-toggle="tab" data-bs-target="#versions-pane" type="button" role="tab" aria-controls="versions-pane" aria-selected="false">
                <i class="bi bi-clock-history me-2"></i>Версии
            </button>
        </li>
    </ul>

    <div class="tab-content" id="notebookEditorTabsContent">
        <div class="tab-pane fade show active" id="editor-pane" role="tabpanel" aria-labelledby="editor-tab">
            @include('notebooks.partials._toolbar')
            @include('notebooks.partials._modal-table')
            @include('notebooks.partials._modal-fields')
            @include('notebooks.partials._modal-image')
            @include('notebooks.partials._modal-link')

            <div id="editor"
                 contenteditable="true"
                 data-save-url="{{ route('notebooks.saveSnapshot', $notebook) }}"
                 class="border p-3"
                 style="min-height:600px;">
                 {{-- Content will be dynamically loaded by JS using loadInitialContent() --}}
            </div>
        </div>
        <div class="tab-pane fade" id="settings-pane" role="tabpanel" aria-labelledby="settings-tab">
            @include('notebooks.partials._settings_general', ['notebook' => $notebook])
            @include('notebooks.partials._settings_named_links', ['notebook' => $notebook])
        </div>
        <div class="tab-pane fade" id="versions-pane" role="tabpanel" aria-labelledby="versions-tab">
            @include('notebooks.partials._settings_versions', ['notebook' => $notebook])
        </div>
    </div>
</div>

{{-- Inline CSS for notifications and editor elements --}}
<style>
    .notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1050; /* Above Bootstrap modals */
        display: flex;
        flex-direction: column-reverse; /* Newest at bottom */
        align-items: flex-end;
    }
    .notification {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 10px 15px;
        margin-top: 10px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.3s ease-out, transform 0.3s ease-out;
        max-width: 300px;
        text-align: right;
    }
    .notification.show {
        opacity: 1;
        transform: translateY(0);
    }
    .notification.success { border-color: #28a745; background-color: #d4edda; color: #155724; }
    .notification.error { border-color: #dc3545; background-color: #f8d7da; color: #721c24; }
    .notification.info { border-color: #17a2b8; background-color: #d1ecf1; color: #0c5460; }
    .notification-actions {
        margin-top: 10px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .notification .btn-sm {
        padding: .25rem .5rem;
        font-size: .875rem;
        line-height: 1.5;
        border-radius: .2rem;
    }

    /* Styles for contenteditable=false wrappers */
    .img-resize-wrapper, .response-field-wrapper, .table-drag-wrapper {
        border: 1px dashed #ccc; /* Visual cue for editable blocks */
        padding: 5px;
        margin: 5px 0;
        display: inline-block; /* default for image wrapper */
        position: relative; /* For handles */
        vertical-align: top; /* Align inline-block elements nicely */
        cursor: move;
        min-height: 20px; /* Ensure they have some height even when empty */
        box-sizing: border-box; /* Include padding/border in element's total width/height */
    }
    /* Specific overrides for response fields */
    .response-field-wrapper {
        display: inline-flex; /* Use flex to align icon and text */
        align-items: center;
        gap: 5px;
        background-color: #e9ecef; /* Light gray background */
        border-color: #ced4da;
        border-radius: .25rem;
        padding: .375rem .75rem; /* Bootstrap form-control padding */
        color: #495057;
        font-family: 'Inter', sans-serif; /* Use Inter font */
        box-sizing: border-box;
    }
    .response-field-wrapper .response-field {
        display: flex; /* Make the inner field also flex to align icon and text */
        align-items: center;
        gap: 5px;
        cursor: pointer;
    }
    .response-field-wrapper .response-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px; /* Limit text width for long labels */
    }
    .response-field-wrapper.selected-for-deletion {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }
    .response-field-wrapper .remove-btn {
        margin-left: 10px;
        cursor: pointer;
        color: #dc3545;
        font-weight: bold;
        font-size: 1.2em;
        line-height: 1;
        border: none;
        background: none;
        padding: 0;
        opacity: 0.7;
        transition: opacity 0.2s ease;
    }
    .response-field-wrapper .remove-btn:hover {
        opacity: 1;
    }

    /* Table wrapper styling */
    .table-drag-wrapper {
        display: block; /* Tables are block level */
        border-color: #adb5bd;
    }
    .table-drag-wrapper.handle-active {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .table-drag-handle {
        position: absolute;
        top: -15px; /* Adjust as needed */
        left: 50%;
        transform: translateX(-50%);
        background-color: #0d6efd;
        color: white;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 0.8em;
        z-index: 10;
        opacity: 0; /* Hidden by default */
        transition: opacity 0.2s ease-in-out;
    }
    .table-drag-wrapper:hover .table-drag-handle {
        opacity: 1; /* Show on hover */
    }
    .table-drag-wrapper.handle-active .table-drag-handle {
        opacity: 1; /* Always show when active */
    }

    /* Image wrapper specific styling */
    .img-resize-wrapper {
        border-color: #adb5bd;
        /* Default alignment is flex-start, so inline-block might be better for images */
        display: inline-block;
    }
    .img-resize-wrapper.selected {
        outline: 2px solid #0d6efd; /* Blue border when selected */
        outline-offset: 2px;
    }
    .img-resize-handle {
        position: absolute;
        width: 10px;
        height: 10px;
        background: #0d6efd;
        border: 1px solid #fff;
        border-radius: 50%;
        z-index: 1;
        opacity: 0; /* Hidden by default */
        transition: opacity 0.2s ease-in-out;
    }
    .img-resize-wrapper.selected .img-resize-handle {
        opacity: 1; /* Show when selected */
    }

    .img-resize-handle.nw { top: -5px; left: -5px; cursor: nw-resize; }
    .img-resize-handle.ne { top: -5px; right: -5px; cursor: ne-resize; }
    .img-resize-handle.sw { bottom: -5px; left: -5px; cursor: sw-resize; }
    .img-resize-handle.se { bottom: -5px; right: -5px; cursor: se-resize; }
    .img-resize-handle.n { top: -5px; left: 50%; transform: translateX(-50%); cursor: n-resize; }
    .img-resize-handle.s { bottom: -5px; left: 50%; transform: translateX(-50%); cursor: s-resize; }
    .img-resize-handle.w { left: -5px; top: 50%; transform: translateY(-50%); cursor: w-resize; }
    .img-resize-handle.e { right: -5px; top: 50%; transform: translateY(-50%); cursor: e-resize; }

    /* Alignment styles for image/field wrappers */
    .img-resize-wrapper[data-align="left"],
    .response-field-wrapper[data-align="left"] {
        float: left;
        margin-right: 10px;
    }

    .img-resize-wrapper[data-align="right"],
    .response-field-wrapper[data-align="right"] {
        float: right;
        margin-left: 10px;
    }

    .img-resize-wrapper[data-align="center"],
    .response-field-wrapper[data-align="center"] {
        display: block; /* Make it block to allow auto margins */
        margin-left: auto;
        margin-right: auto;
    }

    /* Editor specific styles */
    #editor:focus {
        outline: none;
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    #editor.preview-mode {
        border: none;
        box-shadow: none;
    }
    #editor.preview-mode .img-resize-wrapper,
    #editor.preview-mode .response-field-wrapper,
    #editor.preview-mode .table-drag-wrapper {
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        cursor: default !important;
    }

    /* Preview mode for response fields */
    .preview-field-render-wrapper {
        display: inline-block; /* Keep it inline with text */
        margin: 0 5px;
        vertical-align: middle;
        /* Ensure inputs fit within the line height */
    }
    .preview-response-input, .preview-response-select, .preview-response-file {
        display: inline-block;
        width: auto; /* Allow input to size naturally */
        min-width: 150px; /* Minimum width for visibility */
        vertical-align: middle;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
  class EditorCore {
    constructor() {
      this.editor = document.getElementById('editor');
      this.toolbar = document.getElementById('editorToolbar');
      this.lastRange = null;
      this.currentField = null;
      this.draggedElement = null;
      this.contentBeforePreview = null;
      this.lastActiveElement = null;

      this.undoStack = [];
      this.redoStack = [];
      this.maxHistoryStates = 50;
      this.isTyping = false;
      this.typingTimer = null;
      this.ignoreNextInputEvent = false;
      this.activeConfirmationNotification = null; // Track active confirmation

      // Unique prefixes for selection markers to avoid ID collisions
      this.MARKER_START_ID_PREFIX = 'editor-selection-marker-start-';
      this.MARKER_END_ID_PREFIX = 'editor-selection-marker-end-';

      this.handleKey = this.handleKey.bind(this);
      this.updateSelection = this.updateSelection.bind(this);
      this.recordState = this.recordState.bind(this);

      this.initializeEventListeners();
      this.reinitializeDynamicElements();
      this.initializeModals();

      // Load initial content from currentSnapshot if available
      this.loadInitialContent();

      if (!this.isPreviewMode()) {
        this.recordState(true, false);
      }
      this.updateUndoRedoButtons();
      this.editor.focus();
      this.updateSelection();

      // Bootstrap tabs initialization
      const triggerTabList = document.querySelectorAll('#notebookEditorTabs button')
      triggerTabList.forEach(triggerEl => {
          const tabTrigger = new bootstrap.Tab(triggerEl)
          triggerEl.addEventListener('click', event => {
              event.preventDefault()
              tabTrigger.show()
              // If switching to editor tab, ensure it's re-editable and has focus
              if (triggerEl.id === 'editor-tab' && this.editor.contentEditable === 'false') {
                  this.togglePreview(); // Exit preview mode
              }
          })
      })
    }

    // New method to load content
    loadInitialContent() {
        // Использование оригинального синтаксиса Blade для вставки HTML-контента.
        // Убедимся, что $notebook->currentSnapshot->content_html корректно преобразуется в JS-строку.
        const notebookContent = `{!! $notebook->currentSnapshot ? $notebook->currentSnapshot->content_html : '' !!}`;
        if (notebookContent && notebookContent.trim() !== '') {
            this.editor.innerHTML = notebookContent;
        } else {
            // Default content if no snapshot exists or it's empty
            this.editor.innerHTML = `<p>Добро пожаловать в <strong>редактор</strong>!</p>
                                    <p>Это <em>начальное</em> содержимое. Вы можете <del>изменить</del> его.</p>
                                    <ul><li>Пункт 1</li><li>Пункт 2</li></ul>
                                    <p style="text-align: center;"><img src="https://placehold.co/400x200/cccccc/333333?text=Пример+Изображения" alt="Пример Картинки"></p>
                                    <p>Еще немного текста для примера.</p>`;
        }
        this.reinitializeDynamicElements(); // Ensure all elements are interactive after loading
        this.setCursorToEnd(); // Place cursor at the end after loading
    }


    _generateMarkerId(prefix) {
      return prefix + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    }

    // --- START: Undo/Redo Logic with Marker-Based Selection ---
    recordState(initialState = false, isSignificantChange = true) {
        if (this.isPreviewMode()) return;

        let htmlToStore = this.editor.innerHTML;
        let hasMarkers = false;
        let startMarkerId = null;
        let endMarkerId = null;

        const sel = window.getSelection();
        if (sel.rangeCount > 0) {
            const range = sel.getRangeAt(0);
            if (this.editor.contains(range.commonAncestorContainer) || range.commonAncestorContainer === this.editor) {
                // Temporarily insert markers into the live DOM to capture their positions in innerHTML
                startMarkerId = this._generateMarkerId(this.MARKER_START_ID_PREFIX);
                endMarkerId = this._generateMarkerId(this.MARKER_END_ID_PREFIX);

                const startMarker = document.createElement('span');
                startMarker.id = startMarkerId;
                startMarker.className = 'editor-selection-marker'; // For potential CSS hiding if needed

                const endMarker = document.createElement('span');
                endMarker.id = endMarkerId;
                endMarker.className = 'editor-selection-marker';

                const rangeStartClone = range.cloneRange();
                rangeStartClone.collapse(true); // Collapse to the start of the selection
                rangeStartClone.insertNode(startMarker);

                const rangeEndClone = range.cloneRange();
                rangeEndClone.collapse(false); // Collapse to the end of the selection
                rangeEndClone.insertNode(endMarker);

                htmlToStore = this.editor.innerHTML; // Get HTML with markers
                hasMarkers = true;

                // Immediately remove markers from the live DOM
                startMarker.remove();
                endMarker.remove();

                // Restore selection to what it was before marker insertion (which might have changed it)
                sel.removeAllRanges();
                sel.addRange(range);
                this.lastRange = range.cloneRange(); // Update lastRange after potential modification
            }
        }

        const currentStateEntry = {
            html: htmlToStore,
            hasMarkers: hasMarkers,
            startMarkerId: startMarkerId,
            endMarkerId: endMarkerId
        };

        // Avoid duplicate states if only HTML is considered (markers make it unique though)
        if (!initialState && this.undoStack.length > 0) {
             const lastState = this.undoStack[this.undoStack.length - 1];
             // A more robust check might be needed if markers always change HTML
             if (lastState.html === currentStateEntry.html && !currentStateEntry.hasMarkers && !lastState.hasMarkers) {
                // console.log('recordState: Skipping, state identical (no markers).');
                return;
             }
        }

        this.undoStack.push(currentStateEntry);
        if (this.undoStack.length > this.maxHistoryStates) {
            this.undoStack.shift();
        }
        if (!initialState && isSignificantChange) {
            this.redoStack = [];
        }
        this.updateUndoRedoButtons();
    }

    _applyState(stateToRestore) {
        if (!stateToRestore) return;
        this.editor.innerHTML = stateToRestore.html;

        if (stateToRestore.hasMarkers && stateToRestore.startMarkerId && stateToRestore.endMarkerId) {
            const startMarker = this.editor.querySelector('#' + stateToRestore.startMarkerId);
            const endMarker = this.editor.querySelector('#' + stateToRestore.endMarkerId);

            if (startMarker && endMarker && startMarker.parentNode && endMarker.parentNode) { // Ensure markers are in DOM
                const newRange = document.createRange();
                // Position range boundaries carefully relative to markers
                try {
                    if (startMarker.nextSibling) {
                         newRange.setStartBefore(startMarker.nextSibling);
                    } else {
                         newRange.setStartAfter(startMarker);
                    }
                    newRange.setEndBefore(endMarker);

                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(newRange);
                    this.lastRange = newRange.cloneRange();
                } catch(e) {
                    // console.warn("Error setting range from markers:", e);
                    this.setCursorToEnd(); // Fallback if markers cause issues
                } finally {
                     // Ensure markers are removed even if range setting fails partially
                    if (startMarker.parentNode) startMarker.remove();
                    if (endMarker.parentNode) endMarker.remove();
                }
            } else {
                // console.warn("Markers not found after setting innerHTML.");
                this.setCursorToEnd(); // Fallback if markers are lost
            }
        } else {
            this.setCursorToEnd(); // Fallback for states without markers
        }

        this.reinitializeDynamicElements();
        this.updateUndoRedoButtons();
        this.editor.focus(); // Ensure editor has focus after state change
    }

    undo() {
        if (this.isPreviewMode() || this.undoStack.length <= 1) return;
        // console.log('Undo initiated.');

        const currentStateForRedo = this.undoStack.pop();
        this.redoStack.push(currentStateForRedo);

        const prevStateToRestore = this.undoStack[this.undoStack.length - 1];
        this._applyState(prevStateToRestore);
        // console.log('Undo finished.');
    }

    redo() {
        if (this.isPreviewMode() || this.redoStack.length === 0) return;
        // console.log('Redo initiated.');

        const nextStateToRestore = this.redoStack.pop();
        this.undoStack.push(nextStateToRestore);
        if (this.undoStack.length > this.maxHistoryStates) {
            this.undoStack.shift();
        }
        this._applyState(nextStateToRestore);
        // console.log('Redo finished.');
    }

    reinitializeDynamicElements() {
        // console.log('reinitializeDynamicElements: START');
        // This function is CRITICAL for undo/redo. It must meticulously
        // re-apply all JavaScript behaviors to elements recreated by innerHTML.
        this.initializeTableDragMechanics(); // Finds all tables, wraps if needed, adds handles
        this.initializeImageHandlers();    // Finds all <img>, wraps if needed, sets up for resize
        this.initializeFieldEvents();      // Finds all .response-field-wrapper, adds remove buttons, sets up for config
        this.enableDragAndDrop();          // Makes image and field wrappers draggable
        // Ensure all clickable/interactive elements within re-created content get their listeners re-attached.
        // console.log('reinitializeDynamicElements: END');
    }

    setCursorToEnd() {
        this.editor.focus();
        const range = document.createRange();
        const sel = window.getSelection();
        range.selectNodeContents(this.editor);
        range.collapse(false); // false to collapse to the end
        sel.removeAllRanges();
        sel.addRange(range);
        this.lastRange = range.cloneRange(); // Update lastRange
        // console.log('setCursorToEnd: Cursor set to the end of the editor.');
    }

    updateUndoRedoButtons() {
        const undoBtn = document.getElementById('undo-btn');
        const redoBtn = document.getElementById('redo-btn');
        if (undoBtn) undoBtn.disabled = this.undoStack.length <= 1; // Initial state is not undoable
        if (redoBtn) redoBtn.disabled = this.redoStack.length === 0;
    }
    // --- END: Undo/Redo Logic ---

    // --- START: Table Drag Handle Logic (Unchanged from original code.txt, except comments) ---
    initializeTableDragMechanics() {
        // console.log('initializeTableDragMechanics');
        this.editor.querySelectorAll('table.ed-tb').forEach(table => {
            // Ensure table is not inside a contenteditable=false element that ISN'T its own wrapper
            if (table.closest('[contenteditable="false"]:not(.table-drag-wrapper)')) return;

            let wrapper = table.closest('.table-drag-wrapper');
            if (!wrapper) {
                // console.log('initializeTableDragMechanics: Wrapping table');
                wrapper = this.wrapTableForDragging(table);
            }
            if (wrapper) {
                 this.addDragHandleToTableWrapper(wrapper, table);
            }
        });
    }

    addDragHandleToTableWrapper(wrapper, tableElement){
        // console.log('addDragHandleToTableWrapper for table in wrapper:', wrapper.id);
        if (!wrapper || !tableElement) return;
        let handle = wrapper.querySelector('.table-drag-handle');

        if (!handle) {
            handle = document.createElement('div');
            handle.className = 'table-drag-handle';
            handle.innerHTML = '<i class="bi bi-arrows-move"></i>';
            handle.setAttribute('contenteditable', 'false');
            handle.style.cursor = 'grab';
            wrapper.insertBefore(handle, wrapper.firstChild);
        }

        handle.draggable = !this.isPreviewMode();

        wrapper._tableElement = tableElement;
        tableElement._dragWrapper = wrapper;

        const oldDragStart = getattr(handle, '_tableDragStartHandler');
        if(oldDragStart) handle.removeEventListener('dragstart', oldDragStart);
        const oldDragEnd = getattr(handle, '_tableDragEndHandler');
        if(oldDragEnd) handle.removeEventListener('dragend', oldDragEnd);

        const dragStartHandler = (e) => {
            if (this.isPreviewMode()) { e.preventDefault(); return false; }
            e.stopPropagation();
            this.draggedElement = wrapper;
            wrapper.classList.add('dragging');
            e.dataTransfer.setData('text/plain', wrapper.id || `table-wrapper-${Date.now()}`);
            e.dataTransfer.effectAllowed = 'move';
            handle.style.cursor = 'grabbing';
        };
        const dragEndHandler = (e) => {
            if (wrapper) wrapper.classList.remove('dragging');
            handle.style.cursor = 'grab';
            this.draggedElement = null;
            // recordState is called in handleDrop
        };

        handle.addEventListener('dragstart', dragStartHandler);
        handle.addEventListener('dragend', dragEndHandler);
        setattr(handle, '_tableDragStartHandler', dragStartHandler);
        setattr(handle, '_tableDragEndHandler', dragEndHandler);
    }

    wrapTableForDragging(tableElement) {
        if (!tableElement || !tableElement.parentNode || tableElement.closest('.table-drag-wrapper')) {
            return null;
        }
        const wrapper = document.createElement('div');
        wrapper.className = 'table-drag-wrapper';
        wrapper.id = `table-wrapper-${Date.now()}-${Math.random().toString(36).substr(2,5)}`;
        wrapper.setAttribute('contenteditable', 'false');

        tableElement.parentNode.insertBefore(wrapper, tableElement);
        wrapper.appendChild(tableElement);
        return wrapper;
    }
    // --- END: Table Drag Handle Logic ---

    initializeEventListeners() {
      this.editor.addEventListener('click', this.handleClick.bind(this));

      this.editor.addEventListener('keydown', (e) => {
        // Standard Ctrl+A, Z, Y handlers
        if (e.ctrlKey && (e.key === 'a' || e.key === 'ф')) { this.selectAllContent(); e.preventDefault(); }
        if (!e.shiftKey && e.ctrlKey && e.key.toLowerCase() === 'z') { e.preventDefault(); this.undo(); }
        // Handling both Ctrl+Y and Ctrl+Shift+Z for Redo
        if ((!e.shiftKey && e.ctrlKey && e.key.toLowerCase() === 'y') || (e.shiftKey && e.ctrlKey && e.key.toLowerCase() === 'z')) { e.preventDefault(); this.redo(); }

        if (this.isPreviewMode()) return;
        if (e.key === 'Delete' || e.key === 'Backspace') {
            if(this.handleDeletionKeys(e)) return;
            // If not handled, standard deletion will trigger 'input' event, which records state.
        }
        this.handleKey(e);
      });

      this.editor.addEventListener('input', (e) => {
          if (this.isPreviewMode() || this.ignoreNextInputEvent) {
              this.ignoreNextInputEvent = false;
              return;
          }
          clearTimeout(this.typingTimer);
          this.isTyping = true;
          this.typingTimer = setTimeout(() => {
              this.isTyping = false;
              // console.log('Input event - recording state after typing.');
              this.recordState(false, true);
          }, 700);
      });

      const undoButton = document.getElementById('undo-btn');
      if (undoButton) undoButton.addEventListener('click', () => this.undo());
      const redoButton = document.getElementById('redo-btn');
      if (redoButton) redoButton.addEventListener('click', () => this.redo());

      // Toolbar buttons handling, including new justifyFull
      document.querySelectorAll('#editorToolbar [data-cmd]').forEach(btn => {
        const cmd = btn.dataset.cmd;
        if (!['undo', 'redo'].includes(cmd) && !btn.closest('.dropdown-menu')) { // Avoid re-binding undo/redo, ignore dropdown items for now
            if (cmd.startsWith('justify')) { // Handle alignment buttons (justifyLeft, justifyCenter, justifyRight, justifyFull)
                btn.addEventListener('click', () => {
                    const alignment = cmd.replace('justify', '').toLowerCase();
                    // Check if an image or field is selected for alignment
                    let selectedItem = this.editor.querySelector('.img-resize-wrapper.selected, .response-field-wrapper.selected-for-deletion');
                    if (selectedItem) {
                        this.alignSelected(alignment, selectedItem); // Pass the item to alignSelected
                    } else {
                        this.execCommand(cmd); // Fallback to standard text alignment
                    }
                });
            } else if (['fontName', 'fontSize', 'foreColor'].includes(cmd)) {
                // These are handled by change listeners on select/input elements below
            } else if (btn.id === 'createLink') {
            btn.addEventListener('click', () => {
                console.log('Кнопка "Вставить ссылку" нажата.'); // DEBUG: Link button clicked
                this.updateSelection(); // Сохраняем выделение перед открытием модального окна

                const sel = window.getSelection();
                const linkTextInput = document.getElementById('linkTextInput');
                const linkUrlInput = document.getElementById('linkUrlInput');
                linkUrlInput.value = ''; // Очищаем URL

                if (sel.rangeCount > 0) {
                    const selectedText = sel.toString().trim();
                    // Если выделен текст, подставляем его в поле "Текст для отображения"
                    if (selectedText) {
                        linkTextInput.value = selectedText;
                    } else {
                        linkTextInput.value = ''; // Очищаем, если ничего не выделено
                    }
                }
                // Открываем модальное окно для вставки ссылки
                this.openModal('linkInsertModal');
            });
        } else if (!['openTableModal', 'insertImage', 'uploadImageBtn', 'togglePreview', 'save-btn'].includes(btn.id) &&
                       !btn.closest('.dropdown')) { // Avoid dropdown toggles, and buttons handled separately
                btn.addEventListener('click', () => this.execCommand(cmd));
            }
        }
      });

      ['fontFamily', 'fontSize', 'fontColor'].forEach(id => {
          const el = document.getElementById(id);
          if (el) {
              el.addEventListener('change', () => {
                  const cmd = id === 'fontFamily' ? 'fontName' : (id === 'fontSize' ? 'fontSize' : 'foreColor');
                  let value = el.value;
                  // execCommand for fontSize expects 1-7, ensure value is correct or map it if needed
                  this.execCommand(cmd, value);
              });
          }
      });

      this.editor.addEventListener('dblclick', this.handleDoubleClick.bind(this));

      const previewBtn = document.getElementById('togglePreview');
      if (previewBtn) previewBtn.addEventListener('click', () => this.togglePreview());
      const saveBtn = document.getElementById('save-btn');
      if (saveBtn) saveBtn.addEventListener('click', () => this.saveContent()); // Renamed this method call to reflect saving snapshot

      const tableBtn = document.getElementById('openTableModal');
      if (tableBtn) tableBtn.addEventListener('click', () => this.openModal('tableSettingsModal'));

      const imgBtn = document.getElementById('insertImage');
      if (imgBtn) imgBtn.addEventListener('click', () => this.openModal('imageInsertModal'));

      const uploadImageToolbarBtn = document.getElementById('uploadImageBtn');
      if (uploadImageToolbarBtn) {
        uploadImageToolbarBtn.addEventListener('click', () => {
          const input = document.createElement('input');
          input.type = 'file'; input.accept = 'image/*';
          input.onchange = (e) => {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                 this.uploadImageToServer(file); // Calls recordState via insertImage -> insertHTML
            }
            input.remove();
          };
          input.click();
        });
      }

      this.editor.addEventListener('dragover', e => {
          if (this.isPreviewMode()) return;
          e.preventDefault(); e.dataTransfer.dropEffect = 'move';
      });
      this.editor.addEventListener('drop', e => this.handleDrop(e)); // Calls recordState internally on successful drop

      this.editor.addEventListener('keyup', this.updateSelection);
      this.editor.addEventListener('mouseup', this.updateSelection);
      this.editor.addEventListener('focus', this.updateSelection);
      this.editor.addEventListener('mousedown', this.updateSelection); // Added mousedown for immediate selection update

      document.querySelectorAll('.insert-field').forEach(btn => {
        btn.addEventListener('click', (e) => { e.preventDefault(); this.insertField(btn.dataset.type); });
      });
    }

    selectAllContent() {
        this.editor.focus();
        const range = document.createRange();
        range.selectNodeContents(this.editor);
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
        this.lastRange = range.cloneRange();
    }

    handleDeletionKeys(e) {
        // Record state before deletion confirmed to allow undo of the "attempted deletion" UI change
        // this.recordState(false, false); // No, record state after action is final.

        const selectedImageWrapper = this.editor.querySelector('.img-resize-wrapper.selected');
        if (selectedImageWrapper) {
            e.preventDefault();
            this.showNotificationWithActions('Удалить изображение?', [
                { text: 'Да', class: 'btn-danger', action: () => {
                    selectedImageWrapper.remove();
                    this.removeAllImageHandles();
                    this.recordState(false,true); // Record state after actual removal
                    this.updateSelection();
                  }
                },
                { text: 'Нет', class: 'btn-secondary', action: () => {} }
            ], selectedImageWrapper); // Pass element to track notification
            return true;
        }

        const selectedFieldWrapper = this.editor.querySelector('.response-field-wrapper.selected-for-deletion');
        if (selectedFieldWrapper) {
            e.preventDefault();
            this.showNotificationWithActions('Удалить поле?', [
                { text: 'Да', class: 'btn-danger', action: () => {
                    selectedFieldWrapper.remove();
                    if (this.currentField && this.currentField.closest('.response-field-wrapper') === selectedFieldWrapper) {
                        this.currentField = null;
                    }
                    this.recordState(false,true);
                    this.updateSelection();
                  }
                },
                { text: 'Нет', class: 'btn-secondary', action: () => selectedFieldWrapper.classList.remove('selected-for-deletion') }
            ], selectedFieldWrapper);
            return true;
        }

        const activeTableWrapper = this.editor.querySelector('.table-drag-wrapper.handle-active');
        if (activeTableWrapper) {
            e.preventDefault();
            this.showNotificationWithActions('Удалить таблицу?', [
                { text: 'Да', class: 'btn-danger', action: () => {
                    activeTableWrapper.remove();
                    this.recordState(false,true);
                    this.updateSelection();
                  }
                },
                { text: 'Нет', class: 'btn-secondary', action: () => { activeTableWrapper.classList.remove('handle-active');}}
            ], activeTableWrapper);
            return true;
        }
        return false;
    }

    deselectAll() {
        this.editor.querySelectorAll('.img-resize-wrapper.selected').forEach(wrapper => {
            wrapper.classList.remove('selected');
            wrapper.querySelectorAll('.img-resize-handle').forEach(h => h.remove());
        });
        this.editor.querySelectorAll('.response-field-wrapper.selected-for-deletion').forEach(w => {
            w.classList.remove('selected-for-deletion');
        });
        if (this.currentField && !this.currentField.closest('.response-field-wrapper.selected-for-deletion')) {
             this.currentField = null;
        }
        this.editor.querySelectorAll('td.cell-active, th.cell-active').forEach(cell => cell.classList.remove('cell-active'));
        this.editor.querySelectorAll('.table-drag-wrapper.handle-active').forEach(tw => {
            tw.classList.remove('handle-active');
        });
    }

    updateSelection() {
      const sel = window.getSelection();
      if (sel.rangeCount > 0) {
          const currentRange = sel.getRangeAt(0);
          // Only update if the selection is actually within the editor or is the editor itself
          if (this.editor.contains(currentRange.commonAncestorContainer) || currentRange.commonAncestorContainer === this.editor) {
            this.lastRange = currentRange.cloneRange();
            // console.log('updateSelection: lastRange updated.');
            return;
          }
      }
      // If selection is not in editor, but editor is focused, we might want to preserve last valid range
      // or set it to end. For now, if not in editor, lastRange isn't updated.
      // console.log('updateSelection: Selection not in editor or no range.');
    }

    // Helper to ensure a valid range exists and is set to the current selection
    _ensureValidRange() {
        this.editor.focus(); // Always ensure editor is focused first

        let currentRange = null;
        const sel = window.getSelection();

        if (sel.rangeCount > 0) {
            const tempRange = sel.getRangeAt(0);
            if (this.editor.contains(tempRange.commonAncestorContainer) || tempRange.commonAncestorContainer === this.editor) {
                currentRange = tempRange.cloneRange();
            }
        }

        if (!currentRange && this.lastRange) {
            // If no live selection in editor, but we have a last saved range, try to use it.
            // But first, verify it's still valid/attachable.
            try {
                const testRange = this.lastRange.cloneRange();
                // Test if it can be added to selection. This implicitly checks validity.
                sel.removeAllRanges(); // Clear current
                sel.addRange(testRange); // Attempt to add
                currentRange = sel.getRangeAt(0).cloneRange(); // If successful, use it.
            } catch (e) {
                // console.warn("Could not restore lastRange during _ensureValidRange:", e);
                currentRange = null; // Mark as invalid if unable to restore
            }
        }

        if (!currentRange) {
            // Fallback: set cursor to end of editor if no valid range found or restored
            const range = document.createRange();
            range.selectNodeContents(this.editor);
            range.collapse(false); // Collapse to the end
            sel.removeAllRanges();
            sel.addRange(range);
            currentRange = range.cloneRange();
            // console.log('_ensureValidRange: Fallback to set cursor to end.');
        }

        this.lastRange = currentRange.cloneRange(); // Always update lastRange with the *current* valid range
        sel.removeAllRanges();
        sel.addRange(this.lastRange); // Ensure the live selection reflects the chosen range
        return this.lastRange;
    }

    execCommand(cmd, value = null) {
      const activeRange = this._ensureValidRange(); // Use the helper to get a valid range
      if (!activeRange) {
          // console.error("execCommand: Failed to get a valid range for command.");
          return;
      }

      // Добавим проверку на изображение перед выполнением createLink
      if (cmd === 'createLink') {
          const selectedImageWrapper = activeRange.commonAncestorContainer.closest('.img-resize-wrapper');
          if (selectedImageWrapper) {
              this.showNotification('Нельзя вставить ссылку на изображение напрямую. Используйте HTML-редактирование для этого.', 'info');
              return; // Предотвращаем вставку ссылки на изображение через execCommand
          }
      }

      try {
        this.ignoreNextInputEvent = true; // Prevent input event from firing immediately
        document.execCommand(cmd, false, value);
      } catch (ex) {
        this.showNotification(`Команда ${cmd} не может быть выполнена.`, 'error');
        // console.error(`execCommand failed for ${cmd}:`, ex);
      } finally {
        // Schedule ignoreNextInputEvent to be reset. timeout 0 pushes to end of event queue.
        setTimeout(() => { this.ignoreNextInputEvent = false; }, 0);
      }

      this.updateSelection(); // Update selection AFTER command execution
      this.recordState(false, true); // Record state after command
    }


    // --- START: Insertion Logic ---
    ensureBlockInsertionPoint() {
        const range = this._ensureValidRange(); // Use the helper to get and set a valid range
        if (!range) { return; } // Should not happen if _ensureValidRange works as intended

        let startNode = range.startContainer;
        let startOffset = range.startOffset;

        let parentBlock = startNode.nodeType === Node.TEXT_NODE ? startNode.parentNode : startNode;
        while(parentBlock && parentBlock !== this.editor && !['P', 'LI', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'DIV', 'BLOCKQUOTE', 'PRE', 'TD', 'TH'].includes(parentBlock.nodeName)) {
            parentBlock = parentBlock.parentNode;
        }
        if (!parentBlock || !this.editor.contains(parentBlock) || parentBlock.closest('[contenteditable="false"]')) {
             parentBlock = this.editor; // Default to editor if in uneditable or no suitable block
        }


        const createAndSetRangeToNewParagraph = (referenceNodeForPlacement, insertBefore = false) => {
            const p = document.createElement('p');
            p.innerHTML = '<br>'; // Use <br> for an empty paragraph that takes up space
            let actualReferenceNode = referenceNodeForPlacement;

            // If referenceNode is inside contenteditable=false, find the wrapper and insert after it.
            const uneditableWrapper = actualReferenceNode ? actualReferenceNode.closest('[contenteditable="false"]') : null;
            if (uneditableWrapper && this.editor.contains(uneditableWrapper) && uneditableWrapper !== this.editor) {
                actualReferenceNode = uneditableWrapper;
                insertBefore = false; // Always insert after uneditable wrappers for simplicity
            }


            if (actualReferenceNode && actualReferenceNode.parentNode && this.editor.contains(actualReferenceNode.parentNode)) {
                 if (insertBefore && actualReferenceNode !== this.editor) { // Cannot insertBefore editor itself
                    actualReferenceNode.parentNode.insertBefore(p, actualReferenceNode);
                } else if (actualReferenceNode !== this.editor){ // Insert after or if actualReferenceNode is editor, append
                    actualReferenceNode.parentNode.insertBefore(p, actualReferenceNode.nextSibling);
                } else { // actualReferenceNode is editor
                    this.editor.appendChild(p);
                }
            } else {
                this.editor.appendChild(p);
            }
            range.selectNodeContents(p);
            range.collapse(true);
            this.lastRange = range.cloneRange();
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(this.lastRange);
            return true;
        };

        // If cursor is inside a text node and not at the very start/end, or inside a non-block element.
        if ( (startNode.nodeType === Node.TEXT_NODE && (parentBlock.nodeName === 'P' || parentBlock.nodeName === 'DIV' || parentBlock.nodeName.match(/^H[1-6]$/)) ) ||
             (parentBlock && parentBlock !== this.editor && !['P', 'LI', 'DIV', 'TD', 'TH', 'BLOCKQUOTE', 'PRE'].includes(parentBlock.nodeName.toUpperCase()))
           ) {
            // Try to split the current paragraph or move to insert after it.
            if (startNode.nodeType === Node.TEXT_NODE && startOffset > 0 && startOffset < startNode.textContent.length && parentBlock.nodeName === 'P') {
                 // Split paragraph
                 const textAfter = startNode.textContent.substring(startOffset);
                 startNode.textContent = startNode.textContent.substring(0, startOffset);
                 const nextP = document.createElement('p');
                 nextP.textContent = textAfter || '\uFEFF'; // Use non-breaking space if empty
                 parentBlock.parentNode.insertBefore(nextP, parentBlock.nextSibling);
                 // After splitting, we want the new block element (table, image) to go *between* the split paragraphs.
                 // So, the new paragraph `p` created by createAndSetRangeToNewParagraph should be placed before `nextP`.
                 return createAndSetRangeToNewParagraph(nextP, true); // Insert new P *before* the second part of the split
            } else if (parentBlock && parentBlock !== this.editor) {
                // If cursor is at start/end of text in a P, or in something like a SPAN, insert new P after parent P
                const blockToInsertAfter = parentBlock.closest('p, div, li, h1, h2, h3, h4, h5, h6, blockquote, pre') || parentBlock;
                 if (blockToInsertAfter && blockToInsertAfter !== this.editor) {
                    return createAndSetRangeToNewParagraph(blockToInsertAfter, false);
                 }
            }
        }

        // If editor is empty or current parentBlock is empty
        if ((parentBlock === this.editor && this.editor.innerHTML.trim() === '') ||
            (parentBlock && parentBlock !== this.editor && parentBlock.textContent.trim() === '' && parentBlock.innerHTML.toLowerCase().includes('<br>'))
           ) {
            if (parentBlock === this.editor) {
                this.editor.innerHTML = ''; // Clear if it was just whitespace
                return createAndSetRangeToNewParagraph(this.editor, false); // Appends to editor
            } else { // parentBlock is an empty P or similar
                parentBlock.innerHTML = '<br>'; // Ensure it's not totally empty
                range.selectNodeContents(parentBlock);
                range.collapse(true);
                this.lastRange = range.cloneRange();
                const sel = window.getSelection(); sel.removeAllRanges(); sel.addRange(this.lastRange);
                return true;
            }
        }

        // If the range is at the boundary of a block, create a new paragraph there
        if (parentBlock && parentBlock !== this.editor && (startOffset === 0 || (startNode.nodeType === Node.TEXT_NODE && startOffset === startNode.textContent.length) || (startNode.nodeType === Node.ELEMENT_NODE && startOffset === parentBlock.childNodes.length) ) ) {
            const atStartOfBlock = startOffset === 0 && (startNode === parentBlock || (startNode.nodeType === Node.ELEMENT_NODE && startNode === parentBlock.firstChild) || (startNode.nodeType === Node.TEXT_NODE && startNode.parentNode === parentBlock && startNode === parentBlock.firstChild));
            return createAndSetRangeToNewParagraph(parentBlock, atStartOfBlock);
        }

        // Fallback: If no specific condition met, the range is already valid from _ensureValidRange, just ensure it's still active.
        const sel = window.getSelection();
        if (sel.rangeCount === 0 && this.lastRange) {
            try { sel.addRange(this.lastRange); } catch(e) { /* ignore */ }
        } else if (sel.rangeCount > 0) {
            this.lastRange = sel.getRangeAt(0).cloneRange();
        }
    }


    insertField(type) {
      this._ensureValidRange(); // Ensure lastRange is current and valid
      this.ensureBlockInsertionPoint(); // Prepare insertion point based on the now valid range

      // Generate a stable UUID for the field
      const fieldUUID = crypto.randomUUID();
      let iconClass = '', defaultLabel = '';

      if (type === 'text') { iconClass = 'bi bi-pencil'; defaultLabel = 'Текстовое поле'; }
      else if (type === 'select') { iconClass = 'bi bi-list'; defaultLabel = 'Поле-список'; }
      else if (type === 'file') { iconClass = 'bi bi-paperclip'; defaultLabel = 'Поле-файл'; }
      else if (type === 'scale') { iconClass = 'bi bi-sliders'; defaultLabel = 'Поле-шкала'; } // Added scale type

      const fieldHTML = `
          <span class="response-field"
                data-type="${type}"
                data-uuid="${fieldUUID}"
                data-label="${this.escapeHtml(defaultLabel)}"
                contenteditable="false"
                draggable="false">
            <i class="${iconClass}"></i> <span class="response-text">${this.escapeHtml(defaultLabel)}</span>
            <span class="remove-btn" title="Удалить поле">×</span>
          </span>`;

      const wrapperHtml = `<div class="response-field-wrapper" contenteditable="false" data-field-marker-id="${fieldUUID}">` + fieldHTML + `</div>`;

      this.insertHTML(wrapperHtml); // Handles actual insertion and recordState
      const newWrapper = this.editor.querySelector(`.response-field-wrapper[data-field-marker-id="${fieldUUID}"]`);
      if (newWrapper) {
          this.initializeFieldEventsForElement(newWrapper);
          // recordState is called by insertHTML
      }
    }

    initializeFieldEvents() {
        // console.log('initializeFieldEvents');
        this.editor.querySelectorAll('.response-field-wrapper').forEach(wrapper => {
            // Ensure field is not inside a contenteditable=false element that ISN'T its own wrapper
            if (wrapper.parentNode.closest('[contenteditable="false"]:not(.response-field-wrapper)')) return;
            this.initializeFieldEventsForElement(wrapper);
        });
    }

    initializeFieldEventsForElement(fieldWrapper) {
        // console.log('initializeFieldEventsForElement for wrapper with field UUID:', fieldWrapper.querySelector('.response-field')?.dataset.uuid);
        const field = fieldWrapper.querySelector('.response-field');
        if (!field) return;

        this.makeElementDraggable(fieldWrapper);

        const oldFieldClickHandler = getattr(field, '_clickHandler', null);
        if (oldFieldClickHandler) field.removeEventListener('click', oldFieldClickHandler);
        const newFieldClickHandler = (e) => {
            if (this.isPreviewMode()) return;
            if (e.target.classList.contains('remove-btn') || e.target.closest('.remove-btn')) return;
        };
        field.addEventListener('click', newFieldClickHandler);
        setattr(field, '_clickHandler', newFieldClickHandler);

        let removeBtn = field.querySelector('.remove-btn');
        if (removeBtn) {
            const oldRemoveBtnClickHandler = getattr(removeBtn, '_removeClickHandler', null);
            if (oldRemoveBtnClickHandler) removeBtn.removeEventListener('click', oldRemoveBtnClickHandler);

            const newRemoveBtnClickHandler = (e) => {
                e.preventDefault(); e.stopPropagation();
                if (this.isPreviewMode()) return;
                this.showNotificationWithActions('Удалить это поле?', [
                    { text: 'Да', class: 'btn-danger', action: () => {
                        fieldWrapper.remove();
                        if (this.currentField === field) this.currentField = null;
                        this.recordState(false,true);
                        this.updateSelection();
                      }
                    },
                    { text: 'Нет', class: 'btn-secondary', action: () => {} }
                ], fieldWrapper);
            };
            removeBtn.addEventListener('click', newRemoveBtnClickHandler);
            setattr(removeBtn, '_removeClickHandler', newRemoveBtnClickHandler);
        }
    }

    // ... clearModalFields, fillModalFromField, addSelectOption, escapeHtml ... (largely unchanged)
    clearModalFields(type) {
        const safeSetValue = (id, value = '') => { const el = document.getElementById(id); if (el) el.value = value; };
        const safeSetChecked = (id, checked = false) => { const el = document.getElementById(id); if (el) el.checked = checked; };
        const safeSetHTML = (id, html = '') => { const el = document.getElementById(id); if (el) el.innerHTML = html; };

        // For all field types, clear the label
        safeSetValue('textFieldLabel'); // Used for text, select, file, scale

        // CLEAR NEW FILE ACCEPT FIELDS
        safeSetValue('fileFieldAcceptPreset', '*'); // Reset preset dropdown
        safeSetValue('fileFieldAcceptManual', '*'); // Reset manual input

        safeSetValue('textFieldCorrectAnswer', ''); // Corrected: this is the input for the correct answer

        if (type === 'text') {
            safeSetChecked('textFieldRequired');
        } else if (type === 'select') {
            safeSetHTML('selectOptions', ''); // Clear options container
            this.addSelectOption(); // Add one empty option by default
        } else if (type === 'file') {
            safeSetValue('fileFieldAccept', '*');
            safeSetValue('fileFieldMaxSize', '2048'); // Default to 2MB
        } else if (type === 'scale') {
            safeSetValue('scaleFieldMin', '1');
            safeSetValue('scaleFieldMax', '5');
            safeSetValue('scaleFieldStep', '1');
            safeSetValue('scaleFieldDefault', '1');
            safeSetValue('scaleFieldPrefix', '');
            safeSetValue('scaleFieldSuffix', '');
        }
    }

    fillModalFromField(field, type) {
        this.clearModalFields(type);
        const safeSetValue = (id, value) => { const el = document.getElementById(id); if (el) el.value = value; };
        const safeSetChecked = (id, checked) => { const el = document.getElementById(id); if (el) el.checked = checked; };

        const labelElement = document.getElementById(type + 'FieldLabel') || document.getElementById('textFieldLabel');
        if (labelElement) labelElement.value = field.dataset.label || '';

        if (type === 'text') {
            safeSetChecked('textFieldRequired', field.dataset.required === 'true');
            // Corrected: Retrieve correct answer from correct_answers dataset
            try {
                const correctAnswers = JSON.parse(field.dataset.correctAnswers || '{}');
                safeSetValue('textFieldCorrectAnswer', correctAnswers.text || '');
            } catch (e) {
                safeSetValue('textFieldCorrectAnswer', ''); // Fallback if parsing fails
            }

        } else if (type === 'select') {
            let options = [];
            try {
                options = JSON.parse(field.dataset.options || '[]');
            } catch (e) {
                console.error("Error parsing select options:", e);
            }

            let correctValue = null;
            try {
                 const correctAnswers = JSON.parse(field.dataset.correctAnswers || '{}');
                 correctValue = correctAnswers.select; // Get the correct value from JSON
            } catch (e) {
                // Fallback for old format if parsing fails
                correctValue = field.dataset.correct || null;
            }

            const selectOptionsContainer = document.getElementById('selectOptions');
            if (selectOptionsContainer) {
                selectOptionsContainer.innerHTML = ''; // Clear existing options
                if (options.length > 0) {
                    options.forEach((optionText) => {
                        this.addSelectOption(optionText, optionText === correctValue); // Pass the text and check if it's correct
                    });
                } else { this.addSelectOption(); } // Add one empty if no options
            }
        } else if (type === 'file') {
            // Updated file types logic
            const acceptValue = field.dataset.accept || '*';
            safeSetValue('fileFieldAcceptManual', acceptValue); // Set manual input
            safeSetValue('fileFieldMaxSize', field.dataset.maxSize || '2048');

            const presetSelect = document.getElementById('fileFieldAcceptPreset');
            if (presetSelect) {
                const matchingOption = Array.from(presetSelect.options).find(opt => opt.value === acceptValue);
                presetSelect.value = matchingOption ? matchingOption.value : presetSelect.options[0].value;
            }
        } else if (type === 'scale') {
            safeSetValue('scaleFieldMin', field.dataset.min || '1');
            safeSetValue('scaleFieldMax', field.dataset.max || '5');
            safeSetValue('scaleFieldStep', field.dataset.step || '1');

            // Corrected: Retrieve default value from correct_answers dataset
            try {
                const correctAnswers = JSON.parse(field.dataset.correctAnswers || '{}');
                safeSetValue('scaleFieldDefault', correctAnswers.default_value || field.dataset.min || '1');
                safeSetValue('scaleFieldPrefix', correctAnswers.prefix || '');
                safeSetValue('scaleFieldSuffix', correctAnswers.suffix || '');
            } catch (e) {
                safeSetValue('scaleFieldDefault', field.dataset.default || '1');
                safeSetValue('scaleFieldPrefix', field.dataset.prefix || '');
                safeSetValue('scaleFieldSuffix', field.dataset.suffix || '');
            }
        }
    }


    addSelectOption(text = '', isCorrect = false) {
      const container = document.getElementById('selectOptions');
      if (!container) return;
      const count = container.children.length; // Use current number of options for value, will be updated on save
      const newOptionDiv = document.createElement('div');
      newOptionDiv.className = 'input-group mb-2';
      newOptionDiv.innerHTML = `
        <div class="input-group-text">
          <input type="radio" name="correctOptionRadio" value="${count}" ${isCorrect ? 'checked' : ''} aria-label="Отметить как правильный">
        </div>
        <input type="text" class="form-control" placeholder="Вариант ответа" value="${this.escapeHtml(text)}">
        <button class="btn btn-outline-danger remove-select-option-btn" type="button" aria-label="Удалить вариант">
          <i class="bi bi-x-lg"></i>
        </button>
      `;
      newOptionDiv.querySelector('.remove-select-option-btn').addEventListener('click', function() {
          this.closest('.input-group').remove();
      });
      container.appendChild(newOptionDiv);
    }

    escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return unsafe;
        return unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    insertHTML(html) {
        const activeRange = this._ensureValidRange();
        if (!activeRange) {
            return;
        }

        const fragment = activeRange.createContextualFragment(html);
        const firstInsertedNode = fragment.firstChild;
        let lastInsertedNode = fragment.lastChild; // This will be the wrapper element

        activeRange.deleteContents(); // Delete any existing selection
        activeRange.insertNode(fragment); // Insert the new content

        // Check if the inserted content is a contenteditable="false" block wrapper
        const isBlockWrapper = lastInsertedNode &&
                               (lastInsertedNode.classList.contains('img-resize-wrapper') ||
                                lastInsertedNode.classList.contains('response-field-wrapper'));

        let newCursorRange = document.createRange();

        if (isBlockWrapper) {
            // If it's a block wrapper, insert a new paragraph immediately after it
            const newParagraph = document.createElement('p');
            newParagraph.innerHTML = '<br>'; // Ensures it's an empty, visible paragraph

            if (lastInsertedNode.parentNode) { // Ensure the node is still in the DOM
                lastInsertedNode.parentNode.insertBefore(newParagraph, lastInsertedNode.nextSibling);
                newCursorRange.selectNodeContents(newParagraph);
                newCursorRange.collapse(true); // Place cursor at the start of the new paragraph
            } else {
                // Fallback if somehow the node got detached (unlikely, but for safety)
                newCursorRange.selectNodeContents(this.editor);
                newCursorRange.collapse(false); // End of editor
            }
        } else if (lastInsertedNode) {
            // For other inserted elements (e.g., tables, or general inline HTML)
            // Place cursor immediately after the inserted content.
            newCursorRange.setStartAfter(lastInsertedNode);
            newCursorRange.collapse(true);
        } else if (firstInsertedNode) { // Fallback if lastInsertedNode is null
            newCursorRange.setStartAfter(firstInsertedNode);
            newCursorRange.collapse(true);
        } else {
            // If nothing was inserted (empty fragment), just collapse to the original start.
            newCursorRange.setStart(activeRange.startContainer, activeRange.startOffset);
            newCursorRange.collapse(true);
        }

        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(newCursorRange);
        this.lastRange = newCursorRange.cloneRange(); // Save the new cursor position

        this.editor.focus();
        this.reinitializeDynamicElements();
        this.recordState(false, true);
    }
    // --- END: Insertion Logic ---


    makeElementDraggable(element) {
        if (!element || element.tagName === 'TABLE' || element.classList.contains('table-drag-wrapper')) return;

        const oldDragStart = getattr(element, '_itemDragStartHandler', null);
        if (oldDragStart) element.removeEventListener('dragstart', oldDragStart);
        const oldDragEnd = getattr(element, '_itemDragEndHandler', null);
        if (oldDragEnd) element.removeEventListener('dragend', oldDragEnd);

        element.draggable = !this.isPreviewMode();
        const itemDragStartHandler = (e) => {
            if (this.isPreviewMode()) { e.preventDefault(); return false; }
            e.stopPropagation();
            this.draggedElement = element;
            element.classList.add('dragging');
            if (!element.id) element.id = `draggable-item-${Date.now()}`;
            e.dataTransfer.setData('text/plain', element.id);
            e.dataTransfer.effectAllowed = 'move';
        };
        const itemDragEndHandler = (e) => {
            if (element) element.classList.remove('dragging');
            this.draggedElement = null;
        };
        element.addEventListener('dragstart', itemDragStartHandler);
        element.addEventListener('dragend', itemDragEndHandler);
        setattr(element, '_itemDragStartHandler', itemDragStartHandler);
        setattr(element, '_itemDragEndHandler', itemDragEndHandler);
    }

    enableDragAndDrop() {
        // console.log('enableDragAndDrop');
        if (this.isPreviewMode()) return;
        this.editor.querySelectorAll('.img-resize-wrapper, .response-field-wrapper').forEach(item => {
            this.makeElementDraggable(item);
        });
    }

    initializeImageHandlers() {
      // console.log('initializeImageHandlers');
      this.editor.querySelectorAll('img').forEach(img => {
        if (img.closest('[contenteditable="false"]:not(.img-resize-wrapper)')) return;

        let wrapper = img.closest('.img-resize-wrapper');
        if (!wrapper) {
            // console.log('initializeImageHandlers: Wrapping image');
            wrapper = this.wrapImage(img, false);
        }
        if (wrapper) {
            this.makeElementDraggable(wrapper);
            if (wrapper.classList.contains('selected')) {
                 this.addImageHandles(wrapper);
            }
        }
      });
    }
    // ... selectImageWrapper, removeAllImageHandles, wrapImage, addImageHandles, startResize ... (largely unchanged, but ensure they work with reinitializeDynamicElements)
    selectImageWrapper(wrapper) {
        if (!wrapper || this.isPreviewMode()) return;
        wrapper.classList.add('selected');
        this.addImageHandles(wrapper);
    }

    removeAllImageHandles() {
      this.editor.querySelectorAll('.img-resize-wrapper.selected .img-resize-handle').forEach(h => h.remove());
    }

    wrapImage(img, selectAfterWrap = true) {
      if (!img || !img.parentNode || !this.editor.contains(img) || img.closest('.img-resize-wrapper')) return null;

      const wrapper = document.createElement('div');
      wrapper.className = 'img-resize-wrapper';
      wrapper.setAttribute('contenteditable', 'false');
      wrapper.id = `img-wrapper-${Date.now()}-${Math.random().toString(36).substr(2,5)}`;

      const parentParagraph = img.closest('p');
      if (parentParagraph && parentParagraph.style.textAlign) {
          const align = parentParagraph.style.textAlign;
          if (['left', 'center', 'right'].includes(align)) {
              wrapper.dataset.align = align;
              if (align === 'center' && parentParagraph.childNodes.length === 1 && parentParagraph.firstChild === img) {
                 parentParagraph.style.textAlign = '';
              }
          }
      }

      img.parentNode.insertBefore(wrapper, img);
      wrapper.appendChild(img);

      if (selectAfterWrap) {
        this.deselectAll();
        this.selectImageWrapper(wrapper);
      }
      return wrapper;
    }

    addImageHandles(wrapper) {
      if (!wrapper.classList.contains('selected') || wrapper.querySelector('.img-resize-handle')) {
          if (wrapper.classList.contains('selected') && !wrapper.querySelector('.img-resize-handle')) {
          } else {
              return;
          }
      }
      this.removeAllImageHandles();
      ['nw', 'ne', 'sw', 'se', 'n', 's', 'w', 'e'].forEach(pos => {
        const handle = document.createElement('div');
        handle.className = `img-resize-handle ${pos}`;

        const oldMouseDown = getattr(handle, '_resizeMouseDownHandler', null);
        if(oldMouseDown) handle.removeEventListener('mousedown', oldMouseDown);

        const newMouseDownHandler = (e) => {
          if (this.isPreviewMode()) return;
          e.preventDefault(); e.stopPropagation(); this.startResize(e, wrapper, pos);
        };
        handle.addEventListener('mousedown', newMouseDownHandler);
        setattr(handle, '_resizeMouseDownHandler', newMouseDownHandler);
        wrapper.appendChild(handle);
      });
    }

    startResize(e, wrapper, handleType) {
      const img = wrapper.querySelector('img');
      if (!img || this.isPreviewMode()) return;

      const startX = e.clientX, startY = e.clientY;
      const computedStyle = window.getComputedStyle(img);
      let startWidth = parseFloat(computedStyle.width) || img.offsetWidth || 30; // Ensure initial value
      let startHeight = parseFloat(computedStyle.height) || img.offsetHeight || (startWidth / (img.naturalWidth / img.naturalHeight || 1)) || 30; // Ensure initial value

      // Calculate aspectRatio only if image has natural dimensions, otherwise default to 1 for generic resizing
      const aspectRatio = (img.naturalWidth && img.naturalHeight) ? (img.naturalWidth / img.naturalHeight) : (startWidth / startHeight || 1);

      const isCentered = wrapper.dataset.align === 'center';

      const doResize = (moveEvent) => {
        let newWidth = startWidth;
        let newHeight = startHeight;
        const deltaX = moveEvent.clientX - startX;
        const deltaY = moveEvent.clientY - startY;

        if (handleType.length === 2) { // Corner handles (nw, ne, sw, se) - maintain aspect ratio
            // Calculate potential new dimensions based on mouse movement
            let proposedWidth = startWidth + (handleType.includes('w') ? -deltaX : deltaX);
            let proposedHeight = startHeight + (handleType.includes('n') ? -deltaY : deltaY);

            // Determine which dimension changed more significantly and use it to drive proportional scaling
            if (aspectRatio > 0) {
                const scaleX = proposedWidth / startWidth;
                const scaleY = proposedHeight / startHeight;
                const scale = Math.max(scaleX, scaleY); // Use the larger scale to avoid "clipping" and ensure growth

                newWidth = startWidth * scale;
                newHeight = startHeight * scale;
            } else { // Fallback if aspect ratio is not defined or zero (shouldn't happen for valid images)
                newWidth = proposedWidth;
                newHeight = proposedHeight;
            }

            // Adjust element position for top and/or left handles to make it appear to resize from that corner
            if (handleType.includes('w')) {
                img.style.marginLeft = (startWidth - newWidth) + 'px';
            }
            if (handleType.includes('n')) {
                img.style.marginTop = (startHeight - newHeight) + 'px';
            }

        } else { // Side handles (n, s, w, e) - allow stretching/squashing
            if (handleType === 'e') { // East handle: only horizontal resize
                newWidth = startWidth + deltaX;
            } else if (handleType === 'w') { // West handle: only horizontal resize
                newWidth = startWidth - deltaX;
                img.style.marginLeft = (startWidth - newWidth) + 'px'; // Adjust left margin
            } else if (handleType === 's') { // South handle: only vertical resize
                newHeight = startHeight + deltaY;
            } else if (handleType === 'n') { // North handle: only vertical resize
                newHeight = startHeight - deltaY;
                img.style.marginTop = (startHeight - newHeight) + 'px'; // Adjust top margin
            }
        }

        // Apply new dimensions, ensuring minimum size (e.g., 30px)
        img.style.width = Math.max(30, newWidth) + 'px';
        img.style.height = Math.max(30, newHeight) + 'px';

        // Reset margins if they are not specifically controlled by current handle type
        if (!handleType.includes('w') && img.style.marginLeft) img.style.marginLeft = '';
        if (!handleType.includes('n') && img.style.marginTop) img.style.marginTop = '';


        if(isCentered) {
            wrapper.style.width = 'fit-content';
            wrapper.style.width = '-moz-fit-content';
        }
      };
      const stopResize = () => {
        document.removeEventListener('mousemove', doResize);
        document.removeEventListener('mouseup', stopResize);
        document.body.style.userSelect = '';
        this.recordState(false, true); // Record state AFTER resize
      };
      document.body.style.userSelect = 'none';
      document.addEventListener('mousemove', doResize);
      document.addEventListener('mouseup', stopResize);
    }


    initializeModals() {
      document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('show.bs.modal', () => this.lastActiveElement = document.activeElement);
        modal.addEventListener('hidden.bs.modal', () => {
          if (this.editor && typeof this.editor.focus === 'function') {
            this.editor.focus();
            if (this.lastRange) {
                try {
                    const selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(this.lastRange);
                } catch (e) { /* ignore */ }
            }
          }
        });
      });

        const insertLinkConfirmBtn = document.getElementById('insertLinkConfirmBtn');
        if (insertLinkConfirmBtn) {
            const handler = () => {
                const url = document.getElementById('linkUrlInput').value.trim();
                if (!url) {
                    this.showNotification('URL не может быть пустым.', 'error');
                    return;
                }

                // Restore selection that was before modal opening
                this._ensureValidRange();
                
                const selection = window.getSelection();
                if (!selection || selection.rangeCount === 0) return;

                const range = selection.getRangeAt(0);
                const linkText = document.getElementById('linkTextInput').value.trim();

                // Check if the selected element is an image
                const selectedElement = range.commonAncestorContainer.nodeType === Node.ELEMENT_NODE ?
                                        range.commonAncestorContainer : range.commonAncestorContainer.parentNode;
                const isImageSelected = selectedElement.closest('.img-resize-wrapper') || selectedElement.tagName === 'IMG';

                if (isImageSelected) {
                    this.showNotification('Вставка ссылки на изображение через этот интерфейс не поддерживается.', 'info');
                    // Clear fields and close modal if not desired
                    document.getElementById('linkUrlInput').value = '';
                    document.getElementById('linkTextInput').value = '';
                    bootstrap.Modal.getInstance(document.getElementById('linkInsertModal'))?.hide();
                    return;
                }


                // If nothing is selected (cursor), insert link as text
                if (range.collapsed) {
                    const textToInsert = linkText || url;
                    const linkNode = document.createElement('a');
                    linkNode.href = url;
                    linkNode.textContent = textToInsert;
                    range.insertNode(linkNode);
                    // Move cursor after inserted link
                    range.setStartAfter(linkNode);
                    range.collapse(true);
                    selection.removeAllRanges();
                    selection.addRange(range);
                } 
                // If there is a selection (text)
                else {
                    // Use document.execCommand for simple and reliable wrapping
                    // This works for text. For images, we already did the check above.
                    document.execCommand('createLink', false, url);

                    // If custom text was entered, and only text was selected,
                    // find the newly created link and change its text.
                    const selectedContent = selection.toString().trim(); // The text that was selected
                    // Find the nearest <a> element that was just created
                    // This can be tricky if the selection spans multiple nodes.
                    // It's easiest to work if simple text is selected.
                    let linkElement = range.commonAncestorContainer.nodeType === Node.ELEMENT_NODE ? range.commonAncestorContainer : range.commonAncestorContainer.parentElement;
                    while (linkElement && linkElement !== this.editor && linkElement.tagName !== 'A') {
                        linkElement = linkElement.parentElement;
                    }

                    if (linkElement && linkElement.tagName === 'A' && linkElement.href === url) {
                        // If display text was specified in the modal and it's different from selected
                        if (linkText && linkText !== selectedContent) {
                            linkElement.textContent = linkText;
                        }
                    } else {
                        // Fallback for complex selections or if createLink did not work as expected
                        // In this case, if the user entered linkText, but the link was not inserted,
                        // you can insert linkNode as in the case of range.collapsed
                        if (linkText || url) {
                            const newLinkNode = document.createElement('a');
                            newLinkNode.href = url;
                            newLinkNode.textContent = linkText || url;
                            range.deleteContents(); // Delete old selection
                            range.insertNode(newLinkNode); // Insert new link
                            range.setStartAfter(newLinkNode);
                            range.collapse(true);
                            selection.removeAllRanges();
                            selection.addRange(range);
                        }
                    }
                }

                this.recordState(false, true); // Save state for Undo/Redo
                bootstrap.Modal.getInstance(document.getElementById('linkInsertModal'))?.hide();
            };

            // Remove old handler to avoid duplication
            const oldHandler = getattr(insertLinkConfirmBtn, '_handler');
            if (oldHandler) insertLinkConfirmBtn.removeEventListener('click', oldHandler);
            
            insertLinkConfirmBtn.addEventListener('click', handler);
            setattr(insertLinkConfirmBtn, '_handler', handler);
        }

      const applyTableBtn = document.getElementById('apply-table-settings');
      if (applyTableBtn) {
        applyTableBtn.addEventListener('click', () => {
          this._ensureValidRange(); // Ensure cursor position is valid before inserting table
          this.ensureBlockInsertionPoint();

          const rows = parseInt(document.getElementById('ts-rows').value) || 2;
          const cols = parseInt(document.getElementById('ts-cols').value) || 2;
          // ... (rest of table creation logic) ...
          const border = parseInt(document.getElementById('ts-border').value) || 1;
          const styleClass = document.getElementById('ts-style').value || '';
          const colWidth = document.getElementById('ts-col-width')?.value.trim() || '';
          const wordWrap = document.getElementById('ts-word-wrap')?.checked;

          const tableId = `table-${Date.now()}`;
          let tableHtml = `<table id="${tableId}" class="ed-tb table ${styleClass}" style="border-width: ${border}px; width: 100%;"><tbody>`;
          for (let i = 0; i < rows; i++) {
            tableHtml += '<tr>';
            for (let j = 0; j < cols; j++) {
              let cellStyle = `border-width: ${border}px; min-width: 50px;`;
              if (colWidth) cellStyle += ` width: ${this.escapeHtml(colWidth)};`;
              if (wordWrap === false) cellStyle += ` white-space: nowrap;`;
              else cellStyle += ` word-break: break-word; overflow-wrap: break-word; white-space: normal;`;
              tableHtml += `<td style="${cellStyle}" contenteditable="true"><p><br></p></td>`;
            }
            tableHtml += '</tr>';
          }
          tableHtml += '</tbody></table>';

          this.insertHTML(tableHtml); // Calls recordState

          const newTableElement = this.editor.querySelector(`#${tableId}`);
          if (newTableElement) {
              const wrapper = this.wrapTableForDragging(newTableElement); // This should be caught by reinitialize, but can be explicit
              if (wrapper) {
                  this.addDragHandleToTableWrapper(wrapper, newTableElement);
              }
          }
          bootstrap.Modal.getInstance(document.getElementById('tableSettingsModal'))?.hide();
        });
      }

      // ... (addSelectOptionBtn, insertImageConfirmBtn, dropZone listeners remain the same) ...
      const addSelectOptionBtn = document.getElementById('addSelectOption');
      if (addSelectOptionBtn) addSelectOptionBtn.addEventListener('click', () => this.addSelectOption());

      const insertImageConfirmBtn = document.getElementById('insertImageConfirmBtn');
      if (insertImageConfirmBtn) {
        insertImageConfirmBtn.addEventListener('click', () => {
          const imageUrlInput = document.getElementById('imageUrlInput');
          const fileInput = document.getElementById('imageFileInput');
          const url = imageUrlInput?.value?.trim();
          const file = fileInput?.files?.[0];

          if (url) this.insertImage(url); // Calls recordState via insertHTML
          else if (file && file.type.startsWith('image/')) this.uploadImageToServer(file); // Calls recordState via insertImage
          else { this.showNotification('Выберите файл или введите URL!', 'error'); return; }

          bootstrap.Modal.getInstance(document.getElementById('imageInsertModal'))?.hide();
          if(imageUrlInput) imageUrlInput.value = '';
          if(fileInput) fileInput.value = '';
          const dropZoneText = document.getElementById('dropZoneText');
          if(dropZoneText) dropZoneText.textContent = 'Перетащите файл сюда или нажмите для выбора';
        });
      }

      const dropZone = document.getElementById('imageDropZone');
      const fileInputForDropZone = document.getElementById('imageFileInput');
      if (dropZone && fileInputForDropZone) {
        dropZone.addEventListener('click', () => fileInputForDropZone.click());
        dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('bg-info', 'text-white'); });
        dropZone.addEventListener('dragleave', e => { e.preventDefault(); dropZone.classList.remove('bg-info', 'text-white'); });
        dropZone.addEventListener('drop', e => {
          e.preventDefault(); dropZone.classList.remove('bg-info', 'text-white');
          if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            fileInputForDropZone.files = e.dataTransfer.files;
            const dropZoneText = document.getElementById('dropZoneText');
            if (dropZoneText) dropZoneText.textContent = e.dataTransfer.files[0].name;
          }
        });
         fileInputForDropZone.addEventListener('change', () => {
            const dropZoneText = document.getElementById('dropZoneText');
            if (dropZoneText) {
                dropZoneText.textContent = fileInputForDropZone.files.length > 0 ? fileInputForDropZone.files[0].name : 'Перетащите файл сюда или нажмите для выбора';
            }
        });
      }

      // Add event listener for fileFieldAcceptPreset
      document.getElementById('fileFieldAcceptPreset')?.addEventListener('change', function() {
          const manualInput = document.getElementById('fileFieldAcceptManual');
          if (manualInput) {
              manualInput.value = this.value;
          }
      });

      this.initializeFieldSaveButtons(); // Calls recordState on save
    }

    initializeFieldSaveButtons() {
      const createSaveHandler = (modalId, type, getValues) => {
        const saveBtn = document.getElementById(`save${type.charAt(0).toUpperCase() + type.slice(1)}Field`);
        if (saveBtn) {
          const oldHandler = getattr(saveBtn, '_saveFieldHandler', null);
          if(oldHandler) saveBtn.removeEventListener('click', oldHandler);

          const newHandler = () => {
            if (this.currentField && this.currentField.dataset.type === type) {
              const values = getValues();
              for (const key in values) {
                // Special handling for objects that need to be stringified
                if (typeof values[key] === 'object' && values[key] !== null) {
                    this.currentField.dataset[key] = JSON.stringify(values[key]);
                } else {
                    this.currentField.dataset[key] = values[key];
                }
              }
              const textSpan = this.currentField.querySelector('.response-text');
              if(textSpan && values.label) textSpan.textContent = this.escapeHtml(values.label);

              const fieldWrapper = this.currentField.closest('.response-field-wrapper');
              if(fieldWrapper) fieldWrapper.classList.remove('selected-for-deletion');

              bootstrap.Modal.getInstance(document.getElementById(modalId))?.hide();
              this.recordState(false, true); // Record state after field update
            }
          };
          saveBtn.addEventListener('click', newHandler);
          setattr(saveBtn, '_saveFieldHandler', newHandler);
        }
      };

      createSaveHandler('textFieldModal', 'text', () => ({
        label: document.getElementById('textFieldLabel')?.value || 'Текстовое поле',
        required: (document.getElementById('textFieldRequired')?.checked || false).toString(),
        correctAnswers: { text: document.getElementById('textFieldCorrectAnswer')?.value || '' } // Corrected: Save correct answer
      }));

      createSaveHandler('selectFieldModal', 'select', () => {
        const options = []; let correctValue = null;
        document.querySelectorAll('#selectOptions .input-group').forEach((group, index) => {
          const input = group.querySelector('input[type="text"]');
          const radio = group.querySelector('input[type="radio"]');
          if (input?.value.trim()) {
            options.push(input.value.trim());
            if (radio?.checked) correctValue = input.value.trim(); // Store the correct value, not index
          }
        });
        return {
          label: document.getElementById('selectFieldLabel')?.value || 'Поле-список',
          options: options, // Store as array directly, JSON.stringify handled by createSaveHandler
          correctAnswers: { select: correctValue } // Store as JSON for correct_answers column
        };
      });

      createSaveHandler('fileFieldModal', 'file', () => ({
        label: document.getElementById('fileFieldLabel')?.value || 'Поле-файл',
        // Use value from manual input, which is updated by preset select
        accept: document.getElementById('fileFieldAcceptManual')?.value || '*',
        maxSize: document.getElementById('fileFieldMaxSize')?.value || '2048' // in KB
      }));

      createSaveHandler('scaleFieldModal', 'scale', () => ({
          label: document.getElementById('scaleFieldLabel')?.value || 'Поле-шкала',
          min: document.getElementById('scaleFieldMin')?.value || '1',
          max: document.getElementById('scaleFieldMax')?.value || '5',
          step: document.getElementById('scaleFieldStep')?.value || '1',
          correctAnswers: { // Store prefix, suffix, and default value in correct_answers
              default_value: document.getElementById('scaleFieldDefault')?.value || '1',
              prefix: document.getElementById('scaleFieldPrefix')?.value || '',
              suffix: document.getElementById('scaleFieldSuffix')?.value || ''
          }
      }));
    }

    handleClick(e) {
      if (this.isPreviewMode()) { e.preventDefault(); return; }
      const target = e.target;

      if (target.closest('.img-resize-handle') || target.closest('.remove-btn') || target.closest('.modal') ||
          target.classList.contains('table-drag-handle') || target.closest('.table-drag-handle')) {
            const wrapper = target.closest('.table-drag-wrapper');
            if (wrapper && !wrapper.classList.contains('handle-active')) {
                 this.deselectAll(); wrapper.classList.add('handle-active');
            }
        return;
      }

      const fieldWrapper = target.closest('.response-field-wrapper');
      const imgWrapper = target.closest('.img-resize-wrapper');
      const tableCell = target.closest('td, th');
      const tableWrapperTarget = target.closest('.table-drag-wrapper');

      let newSelectionMade = false;
      if (imgWrapper && !imgWrapper.classList.contains('selected')) {
          this.deselectAll(); this.selectImageWrapper(imgWrapper); newSelectionMade = true;
      } else if (fieldWrapper && !fieldWrapper.classList.contains('selected-for-deletion')) {
          this.deselectAll();
          fieldWrapper.classList.add('selected-for-deletion');
          this.currentField = fieldWrapper.querySelector('.response-field');
          newSelectionMade = true;
      } else if (tableCell && tableCell.isContentEditable && !tableCell.classList.contains('cell-active')) {
          this.deselectAll();
          tableCell.classList.add('cell-active');
          const parentWrapper = tableCell.closest('.table-drag-wrapper');
          if (parentWrapper) parentWrapper.classList.add('handle-active');
          newSelectionMade = true;
      } else if (tableWrapperTarget && !tableWrapperTarget.classList.contains('handle-active') && !tableCell) {
          this.deselectAll();
          tableWrapperTarget.classList.add('handle-active');
          newSelectionMade = true;
      } else if (!imgWrapper && !fieldWrapper && !tableCell && !tableWrapperTarget) {
          this.deselectAll();
          newSelectionMade = true;
      }

      if (this.editor.contains(target)) {
          this.updateSelection();
      }
    }

    handleDoubleClick(e) {
        if (this.isPreviewMode()) return;
        const target = e.target;

        const field = target.closest('.response-field');
        if (field && !target.classList.contains('remove-btn') && !target.closest('.remove-btn')) {
            e.preventDefault(); e.stopPropagation();
            this.deselectAll();
            this.currentField = field;
            field.closest('.response-field-wrapper')?.classList.add('selected-for-deletion');
            this.openFieldSettings();
            return;
        }
    }

    openFieldSettings() {
      if (!this.currentField) return;
      const type = this.currentField.dataset.type;
      this.fillModalFromField(this.currentField, type);
      if (type === 'text') this.openModal('textFieldModal');
      else if (type === 'select') this.openModal('selectFieldModal');
      else if (type === 'file') this.openModal('fileFieldModal');
      else if (type === 'scale') this.openModal('scaleFieldModal'); // Open scale modal
    }

    handleKey(e) {
      if (this.isPreviewMode()) return;
      if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Home', 'End', 'PageUp', 'PageDown'].includes(e.key)) {
        setTimeout(this.updateSelection, 0);
      }
    }

    togglePreview() {
        const mainContainer = document.querySelector('.container.my-4');
        const isCurrentlyPreview = this.editor.classList.contains('preview-mode');

        this.editor.classList.toggle('preview-mode', !isCurrentlyPreview);
        const isInPreviewNow = this.editor.classList.contains('preview-mode');

        const toggleButton = document.getElementById('togglePreview');
        const saveButton = document.getElementById('save-btn');
        const toolbar = document.getElementById('editorToolbar');
        const undoBtn = document.getElementById('undo-btn');
        const redoBtn = document.getElementById('redo-btn');
        const elementsToToggle = document.querySelectorAll('.btn-tgl');

        this.editor.contentEditable = isInPreviewNow ? 'false' : 'true';

        if (toggleButton) {
             const icon = toggleButton.querySelector('i');
             const textSpan = toggleButton.querySelector('#togglePreviewText');
             if (isInPreviewNow) {
                if(icon) { icon.classList.remove('bi-eye-fill'); icon.classList.add('bi-pencil-fill'); }
                if(textSpan) textSpan.textContent = 'Редактирование';
             } else {
                if(icon) { icon.classList.remove('bi-pencil-fill'); icon.classList.add('bi-eye-fill'); }
                if(textSpan) textSpan.textContent = 'Предварительный просмотр';
             }
        }

        if (mainContainer) {
            mainContainer.classList.toggle('mb-4', !isInPreviewNow);
        }
        elementsToToggle.forEach(el => { el.style.display = isInPreviewNow ? 'none' : ''; });

        if (isInPreviewNow) {
            this.editor.querySelectorAll('td[contenteditable="true"], th[contenteditable="true"]').forEach(cell => {
                cell.setAttribute('data-was-editable', 'true'); cell.contentEditable = 'false';
            });
            this.contentBeforePreview = this.editor.innerHTML;
            this.convertFieldsToInputs();
            this.editor.querySelectorAll('.img-resize-handle, .remove-btn, .table-drag-handle').forEach(el => el.style.display = 'none');
            this.editor.querySelectorAll('.selected, .selected-for-deletion, .cell-active, .handle-active').forEach(el =>
                el.classList.remove('selected', 'selected-for-deletion', 'cell-active', 'handle-active')
            );
            if (toolbar) {
                 toolbar.querySelectorAll('.btn, .form-select, .form-control, .input-group, .dropdown, select, input[type=color]').forEach(el => {
                    if (el !== toggleButton && el !== saveButton && !el.contains(toggleButton) && !el.contains(saveButton) && el !== undoBtn && el !== redoBtn) {
                        el.style.setProperty('display', 'none', 'important');
                        if(el.disabled !== undefined) el.disabled = true;
                    } else {
                        el.style.display = ''; if(el.disabled !== undefined) el.disabled = false;
                    }
                });
            }
            if(undoBtn) undoBtn.disabled = true; if(redoBtn) redoBtn.disabled = true;
        } else {
            // 1. Восстанавливаем контент из сохраненной переменной
            if (this.contentBeforePreview !== null) {
                 this.editor.innerHTML = this.contentBeforePreview;
                 this.contentBeforePreview = null;
            }

            // 2. Повторно инициализируем все динамические элементы (хендлеры, врапперы и т.д.)
            this.reinitializeDynamicElements();

            // 3. ТЕПЕРЬ, когда DOM восстановлен, принудительно делаем ячейки редактируемыми
            this.editor.querySelectorAll('table.ed-tb td, table.ed-tb th').forEach(cell => {
                // Просто устанавливаем contenteditable в true для всех ячеек всех таблиц редактора
                cell.setAttribute('contenteditable', 'true');

                // Опционально: очищаем data-атрибут, если он еще остался
                if (cell.hasAttribute('data-was-editable')) {
                    cell.removeAttribute('data-was-editable');
                }

                // Убеждаемся, что в пустой ячейке есть тег <p> для корректной установки курсора
                if (cell.innerHTML.trim() === '') {
                    cell.innerHTML = '<p><br></p>';
                } else if (!cell.querySelector('p, li, h1, h2, h3, h4, h5, h6')) {
                    const tempContent = cell.innerHTML;
                    cell.innerHTML = `<p>${tempContent}</p>`;
                }
            });

            // 4. Восстанавливаем видимость тулбара и кнопок
            if (toolbar) {
                toolbar.querySelectorAll('.btn, .form-select, .form-control, .input-group, .dropdown, select, input[type=color]').forEach(el => {
                    el.style.display = ''; if(el.disabled !== undefined) el.disabled = false;
                });
            }

            // 5. Обновляем состояние кнопок Undo/Redo и фокус
            this.updateUndoRedoButtons();
            this.editor.focus();
            this.updateSelection();
        }
    }

    convertFieldsToInputs() {
      // In teacher's preview, fields become read-only inputs that show the field's label
      this.editor.querySelectorAll('.response-field-wrapper').forEach(wrapper => {
        const field = wrapper.querySelector('.response-field');
        if (!field) { wrapper.remove(); return; }
        const type = field.dataset.type;
        const label = field.dataset.label || `[${type} field]`;
        let inputHtml = `<div class="mb-2 form-group preview-field-render-wrapper">`;
        if (type === 'text') {
          inputHtml += `<input type="text" class="form-control preview-response-input" value="" placeholder="${this.escapeHtml(label)}" readonly>`;
        } else if (type === 'select') {
          const options = JSON.parse(field.dataset.options || '[]');
          let optionsHtml = '';
          options.forEach(opt => { optionsHtml += `<option value="${this.escapeHtml(opt)}">${this.escapeHtml(opt)}</option>`; });
          if (options.length === 0) {
              optionsHtml += `<option value="" disabled selected>Нет вариантов</option>`;
          }
          inputHtml += `<select class="form-select preview-response-select" disabled>${optionsHtml}</select>`;
        } else if (type === 'file') {
          inputHtml += `<input type="file" class="form-control preview-response-file" disabled>`;
        } else if (type === 'scale') {
          // Changed to retrieve values from correct_answers dataset
          let min = '1', max = '5', step = '1', defaultVal = '1', prefix = '', suffix = '';
          try {
              const correctAnswers = JSON.parse(field.dataset.correctAnswers || '{}');
              defaultVal = correctAnswers.default_value || field.dataset.min || '1'; // Fallback to min if default_value not set
              prefix = correctAnswers.prefix || '';
              suffix = correctAnswers.suffix || '';
          } catch (e) {
              // Fallback to direct dataset for old format
              defaultVal = field.dataset.default || '1';
              prefix = field.dataset.prefix || '';
              suffix = field.dataset.suffix || '';
          }
          min = field.dataset.min || '1'; // min/max/step are from validation_rules, not correct_answers
          max = field.dataset.max || '5';
          step = field.dataset.step || '1';

          inputHtml += `<input type="range" class="form-range preview-response-scale" min="${this.escapeHtml(min)}" max="${this.escapeHtml(max)}" step="${this.escapeHtml(step)}" value="${this.escapeHtml(defaultVal)}" disabled>`;
          inputHtml += `<span class="ms-2 text-muted">(${this.escapeHtml(prefix)}${this.escapeHtml(defaultVal)}${this.escapeHtml(suffix)})</span>`;
        }
        inputHtml += `</div>`;
        wrapper.outerHTML = inputHtml;
      });
    }

    // Renamed from saveContent to saveNotebookContent
    saveContent() {
      const url = this.editor.dataset.saveUrl; // This will now point to notebooks.saveSnapshot
      let contentToSave = '';
      const wasInPreview = this.editor.classList.contains('preview-mode');
      const sourceHTML = (wasInPreview && this.contentBeforePreview !== null) ? this.contentBeforePreview : this.editor.innerHTML;

      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = sourceHTML;
      tempDiv.querySelectorAll('.selected, .selected-for-deletion, .cell-active, .handle-active')
             .forEach(el => el.classList.remove('selected', 'selected-for-deletion', 'cell-active', 'handle-active'));
      tempDiv.querySelectorAll('.img-resize-handle, .table-drag-handle, .editor-selection-marker').forEach(h => h.remove()); // Also remove selection markers
      tempDiv.querySelectorAll('.table-drag-wrapper').forEach(wrapper => {
          const table = wrapper.querySelector('table.ed-tb');
          if (table && wrapper.parentNode) {
              wrapper.parentNode.replaceChild(table.cloneNode(true), wrapper);
          } else if (wrapper.parentNode) {
              wrapper.remove();
          }
      });

      // Extract response field data
      const responseFieldsData = [];
      let fieldOrder = 0; // Initialize order counter
      tempDiv.querySelectorAll('.response-field').forEach(fieldEl => {
          const fieldType = fieldEl.dataset.type;
          const fieldUUID = fieldEl.dataset.uuid;
          const fieldLabel = fieldEl.dataset.label;

          const fieldData = {
              uuid: fieldUUID,
              field_type: fieldType,
              label: fieldLabel,
              order: fieldOrder++
          };

          // Collect type-specific data for validation_rules and correct_answers
          if (fieldType === 'text') {
              fieldData.validation_rules = { required: fieldEl.dataset.required === 'true' };
              try {
                  const correctAnswers = JSON.parse(fieldEl.dataset.correctAnswers || '{}');
                  fieldData.correct_answers = { text: correctAnswers.text || null };
              } catch (e) {
                  fieldData.correct_answers = null;
              }
          } else if (fieldType === 'select') {
              const options = JSON.parse(fieldEl.dataset.options || '[]');
              let correctValue = null;
              try {
                  const correctAnswersObj = JSON.parse(fieldEl.dataset.correctAnswers || '{}');
                  if (correctAnswersObj.select !== undefined) {
                      correctValue = correctAnswersObj.select;
                  }
              } catch (e) { /* ignore parse error, use default */ }
              fieldData.validation_rules = null;
              fieldData.correct_answers = { select: correctValue, options: options }; // Store options here as well for backend
          } else if (fieldType === 'file') {
              fieldData.validation_rules = {
                  accept: fieldEl.dataset.accept || '*',
                  max_size: parseInt(fieldEl.dataset.maxSize || '2048') // in KB
              };
              fieldData.correct_answers = null;
          } else if (fieldType === 'scale') {
              fieldData.validation_rules = {
                  min: parseInt(fieldEl.dataset.min || '1'),
                  max: parseInt(fieldEl.dataset.max || '5'),
                  step: parseInt(fieldEl.dataset.step || '1')
              };
              let defaultVal = '1';
              let prefix = '';
              let suffix = '';
              try {
                  const correctAnswers = JSON.parse(fieldEl.dataset.correctAnswers || '{}');
                  defaultVal = correctAnswers.default_value || fieldEl.dataset.min || '1';
                  prefix = correctAnswers.prefix || '';
                  suffix = correctAnswers.suffix || '';
              } catch (e) {
                  defaultVal = fieldEl.dataset.default || fieldEl.dataset.min || '1';
                  prefix = fieldEl.dataset.prefix || '';
                  suffix = fieldEl.dataset.suffix || '';
              }
              fieldData.correct_answers = {
                  default_value: defaultVal,
                  prefix: prefix,
                  suffix: suffix
              };
          }
          responseFieldsData.push(fieldData);
      });

      // --- НАЧАЛО ЗАЩИТНОГО КОДА ---
      const seenUuids = new Set();
      const duplicateUuids = new Set();

      // Первый проход: находим дубликаты
      responseFieldsData.forEach(field => {
          if (seenUuids.has(field.uuid)) {
              duplicateUuids.add(field.uuid);
          }
          seenUuids.add(field.uuid);
      });

      // Второй проход: перегенерируем дубликаты и обновляем в DOM и в responseFieldsData
      if (duplicateUuids.size > 0) {
          console.warn('Обнаружены и исправлены дубликаты UUID перед сохранением:', Array.from(duplicateUuids));
          tempDiv.querySelectorAll('.response-field').forEach(fieldEl => {
              if (duplicateUuids.has(fieldEl.dataset.uuid)) {
                  const oldUuid = fieldEl.dataset.uuid;
                  const newUuid = crypto.randomUUID();
                  fieldEl.dataset.uuid = newUuid;
                  
                  // Также нужно найти этот UUID в массиве responseFieldsData и обновить его там
                  const fieldData = responseFieldsData.find(f => f.uuid === oldUuid);
                  if(fieldData) {
                      fieldData.uuid = newUuid;
                  }
              }
          });
      }
      // --- КОНЕЦ ЗАЩИТНОГО КОДА ---

      contentToSave = tempDiv.innerHTML;

      // Send both content and extracted fields data
      fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''},
        body: JSON.stringify({
            content_html: contentToSave,
            response_fields: responseFieldsData
        })
      })
      .then(async resp => {
        if (resp.ok) {
            this.showNotification('Сохранено!', 'success');
            // Reset undo/redo stacks to reflect the saved state as the new baseline
            this.undoStack = [{html: contentToSave, hasMarkers: false}]; // Store only the clean HTML
            this.redoStack = [];
            this.updateUndoRedoButtons();
            // Optionally, refresh snapshot list in settings tab if it's open
            if (document.getElementById('notebookEditorTabs').querySelector('button[data-bs-target="#versions-pane"]')?.classList.contains('active')) {
                document.dispatchEvent(new CustomEvent('notebookSnapshotSaved'));
            }
        } else {
          let errorText = 'Ошибка сохранения';
          try { const data = await resp.json(); if (data.message) errorText = data.message; } catch (e) { /* ignore */ }
          this.showNotification(errorText, 'error');
        }
      })
      .catch((error) => { this.showNotification('Ошибка сети при сохранении', 'error'); });
    }


    showNotification(message, type = 'info', duration = 3000) {
        // ... (this.activeConfirmationNotification logic is for showNotificationWithActions) ...
        let container = document.getElementById('notificationContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notificationContainer';
            container.className = 'notification-container';
            document.body.appendChild(container);
        }
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        container.appendChild(notification);
        void notification.offsetWidth;
        setTimeout(() => { notification.classList.add('show'); }, 10);
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => { if (notification.parentNode) notification.remove(); }, 300);
        }, duration);
    }

    showNotificationWithActions(message, actions, elementToTrack = null) {
        // If a confirmation for the same element is already shown, do nothing.
        if (this.activeConfirmationNotification && getattr(this.activeConfirmationNotification, '_trackedElement') === elementToTrack && elementToTrack !== null) {
            return;
        }
        // Close any existing confirmation notification
        if (this.activeConfirmationNotification && this.activeConfirmationNotification.parentNode) {
            this.activeConfirmationNotification.classList.remove('show');
            const oldNotification = this.activeConfirmationNotification; // Capture for timeout
            setTimeout(() => { if (oldNotification.parentNode) oldNotification.remove(); }, 300);
            this.activeConfirmationNotification = null;
        }

        let container = document.getElementById('notificationContainer');
         if (!container) {
            container = document.createElement('div'); container.id = 'notificationContainer';
            container.className = 'notification-container'; document.body.appendChild(container);
        }
        const notification = document.createElement('div');
        notification.className = 'notification info';
        if (elementToTrack) setattr(notification, '_trackedElement', elementToTrack); // Tag notification with element

        const messageP = document.createElement('p'); 
        messageP.innerHTML = message; // USE innerHTML INSTEAD OF textContent
        notification.appendChild(messageP);
        const actionsDiv = document.createElement('div'); actionsDiv.className = 'notification-actions';

        actions.forEach(actionInfo => {
            const button = document.createElement('button');
            button.className = `btn btn-sm ${actionInfo.class || 'btn-primary'}`;
            if (actionInfo.class === 'btn-danger' && actionInfo.text.toLowerCase() === 'да') {
                 button.innerHTML = `<i class="bi bi-trash3"></i> ${this.escapeHtml(actionInfo.text)}`;
            } else { button.textContent = this.escapeHtml(actionInfo.text); }

            button.onclick = () => {
                actionInfo.action(); // This should include recordState()
                if (this.activeConfirmationNotification === notification) {
                    this.activeConfirmationNotification = null;
                }
                notification.classList.remove('show');
                setTimeout(() => { if (notification.parentNode) notification.remove(); }, 300);
            };
            actionsDiv.appendChild(button);
        });
        notification.appendChild(actionsDiv); container.appendChild(notification);
        this.activeConfirmationNotification = notification; // Set as active

        void notification.offsetWidth;
        setTimeout(() => { notification.classList.add('show'); }, 10);
    }

    uploadImageToServer(file) {
      this._ensureValidRange(); // Ensure cursor position is valid before uploading image
      this.ensureBlockInsertionPoint();

      const formData = new FormData(); formData.append('image', file);
      // Placeholder URL for image upload - replace with your actual endpoint
      fetch('/blocks/upload-image', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '' },
        body: formData
      })
      .then(resp => { if (!resp.ok) throw new Error(`HTTP error! status: ${resp.status}`); return resp.json(); })
      .then(data => {
        if (data.url) {
            this.insertImage(data.url); // Calls recordState
            this.showNotification('Изображение загружено', 'success');
        }
        else this.showNotification(data.error || 'Ошибка формата ответа от сервера при загрузке изображения', 'error');
      })
      .catch(error => { this.showNotification(`Ошибка загрузки изображения: ${error.message}`, 'error'); });
    }

    insertImage(src) {
        this._ensureValidRange(); // Ensure cursor position is valid before inserting image
        this.ensureBlockInsertionPoint();

        const id = `img-wrapper-${Date.now()}`;
        const wrapperHtml = `<div id="${id}" class="img-resize-wrapper" contenteditable="false" data-img-marker-id="${id}"><img src="${this.escapeHtml(src)}" alt="Изображение"></div>`;
        this.insertHTML(wrapperHtml); // Calls recordState

        const insertedWrapper = this.editor.querySelector(`.img-resize-wrapper[data-img-marker-id="${id}"]`);
        if (insertedWrapper) {
            // makeElementDraggable and addImageHandles should be covered by reinitializeDynamicElements called in insertHTML
            this.deselectAll();
            this.selectImageWrapper(insertedWrapper); // Explicitly select after insertion
        }
    }

    handleDrop(e) {
        // console.log('handleDrop: Initiated');
        if (this.isPreviewMode() || !this.draggedElement) {
            if (this.draggedElement) this.draggedElement.classList.remove('dragging');
            this.draggedElement = null; e.preventDefault(); return;
        }
        e.preventDefault(); e.stopPropagation();

        const dragged = this.draggedElement;
        let range;
        // Attempt to get the precise drop location using caretRangeFromPoint or caretPositionFromPoint
        if (document.caretRangeFromPoint) {
            range = document.caretRangeFromPoint(e.clientX, e.clientY);
        } else if (document.caretPositionFromPoint) {
            const pos = document.caretPositionFromPoint(e.clientX, e.clientY);
            if (pos) { range = document.createRange(); range.setStart(pos.offsetNode, pos.offset); range.collapse(true); }
        }

        if (!range) { // Fallback if caretRangeFromPoint is not supported or fails
            // console.warn("handleDrop: caretRangeFromPoint failed. Using fallback.");
            range = document.createRange();
            let refNode = e.target;
            // Traverse up to find a suitable parent within the editor or the editor itself
            while(refNode && refNode.nodeType !== Node.ELEMENT_NODE && refNode.parentNode !== this.editor) { refNode = refNode.parentNode; }
            if (!refNode || !this.editor.contains(refNode)) refNode = this.editor;

            try {
                const rect = refNode.getBoundingClientRect();
                const isTextNodeOrEmpty = refNode.nodeType === Node.TEXT_NODE || (refNode.nodeType === Node.ELEMENT_NODE && !refNode.firstChild);
                if (isTextNodeOrEmpty || e.clientY < rect.top + rect.height / 2) {
                    if (refNode.parentNode && refNode !== this.editor) range.setStartBefore(refNode);
                    else { range.selectNodeContents(refNode); range.collapse(true); } // At start of editor or empty element
                } else {
                    if (refNode.parentNode && refNode !== this.editor) range.setStartAfter(refNode);
                    else { range.selectNodeContents(refNode); range.collapse(false); } // At end of editor or empty element
                }
            } catch(err) {
                 // Final fallback: place at end of editor if all else fails
                range.selectNodeContents(this.editor); range.collapse(false);
            }
        }

        let startContainer = range.startContainer;
        // Fix for "range.startContainer.closest is not a function" when startContainer is text node
        let closestTargetForDraggedCheck;
        if (startContainer.nodeType === Node.ELEMENT_NODE) {
            closestTargetForDraggedCheck = startContainer.closest(`.${dragged.classList[0]}`);
        } else if (startContainer.parentElement) { // TEXT_NODE or other non-element
            closestTargetForDraggedCheck = startContainer.parentElement.closest(`.${dragged.classList[0]}`);
        }

        // Prevent dropping element onto itself or its direct wrapper
        if (dragged.contains(startContainer) || dragged === closestTargetForDraggedCheck) {
            dragged.classList.remove('dragging'); this.draggedElement = null; return;
        }

        // Remove the dragged element from its original position
        const originalParent = dragged.parentNode;
        if (originalParent && originalParent.contains(dragged)) {
             try { originalParent.removeChild(dragged); } catch (err) { /* ignore */ }
        }

        let targetCell = null;
        if (startContainer.nodeType === Node.ELEMENT_NODE) targetCell = startContainer.closest('td, th');
        else if (startContainer.parentElement) targetCell = startContainer.parentElement.closest('td, th');

        // Prevent dropping into non-editable wrappers (images, fields)
        const nonEditableWrapperTarget = startContainer.nodeType === Node.ELEMENT_NODE ?
                                         startContainer.closest('.img-resize-wrapper, .response-field-wrapper') :
                                         (startContainer.parentElement ? startContainer.parentElement.closest('.img-resize-wrapper, .response-field-wrapper') : null);

        if (nonEditableWrapperTarget && nonEditableWrapperTarget !== dragged) {
            // console.log('handleDrop: Target is non-editable wrapper. Inserting after.');
            if (nonEditableWrapperTarget.parentNode) {
                nonEditableWrapperTarget.parentNode.insertBefore(dragged, nonEditableWrapperTarget.nextSibling);
            } else { this.editor.appendChild(dragged); } // Fallback to append if no parent
        } else if (targetCell && this.editor.contains(targetCell) && targetCell.isContentEditable && !dragged.classList.contains('table-drag-wrapper')) {
            // console.log('handleDrop: Target is editable table cell.');
            // Adjust range to be within the cell if it's not already
            let insertionPoint = range.startContainer;
            if (!targetCell.contains(insertionPoint)) {
                // If cursor is not precisely in the cell, find a suitable insertion point (e.g., first <p> or the cell itself)
                insertionPoint = targetCell.querySelector('p:not([contenteditable="false"] p)') || targetCell;
                range.selectNodeContents(insertionPoint);
                // Collapse to start for a paragraph, or end for a cell if it's not a paragraph
                range.collapse(targetCell.querySelector('p:not([contenteditable="false"] p)') ? true : false);
            }
            range.insertNode(dragged);
        } else if (dragged.classList.contains('table-drag-wrapper') && targetCell) {
            this.showNotification('Нельзя вставлять таблицу внутрь ячейки другой таблицы.', 'error');
            // If dropping a table into a cell, revert to original position or append to editor
            if(originalParent && this.editor.contains(originalParent)) originalParent.appendChild(dragged);
            else this.editor.appendChild(dragged);
        } else {
            // console.log('handleDrop: Target is main editor area.');
            try {
                 range.insertNode(dragged);
            } catch (exInsert) {
                 // console.warn("handleDrop: range.insertNode failed, fallback to append.", exInsert);
                 this.editor.appendChild(dragged); // Simple fallback
            }
        }

        // Set cursor after the dropped element
        if (dragged.parentNode) {
            const newRange = document.createRange();
            try {
                newRange.setStartAfter(dragged); newRange.collapse(true);
                const selection = window.getSelection(); selection.removeAllRanges(); selection.addRange(newRange);
                this.lastRange = newRange.cloneRange();
            } catch (err) { this.updateSelection(); }
        }

        dragged.classList.remove('dragging'); this.draggedElement = null;
        this.reinitializeDynamicElements(); // CRITICAL after drop
        this.recordState(false, true); // Record state AFTER successful drop
        // console.log('handleDrop: Finished, state recorded.');
    }


    getDragAfterElement(container, y, x, draggedItem) {
      // ... (This function is a fallback, might not be hit often with caretRangeFromPoint) ...
        const directChildrenSelector = '> .response-field-wrapper, > .img-resize-wrapper, > .table-drag-wrapper, > p, > div:not(.img-resize-handle):not(.table-drag-handle), > h1, > h2, > h3, > h4, > h5, > h6, > ul, > ol, > li, > blockquote, > pre';
        if (!container || typeof container.querySelectorAll !== 'function') return null;
        const children = Array.from(container.children).filter(child =>
            child.nodeType === Node.ELEMENT_NODE &&
            child.matches(directChildrenSelector.substring(2)) &&
            child !== draggedItem &&
            getComputedStyle(child).display !== 'none'
        );
        let closest = { offset: Number.POSITIVE_INFINITY, element: null };
        for (const child of children) {
            const box = child.getBoundingClientRect();
            if (x >= box.left && x <= box.right) {
                if (y < box.top + box.height / 2) { return child; }
            }
            const offset = box.top - y;
            if (offset >= 0 && offset < closest.offset) { closest = { offset, element: child }; }
        }
        return closest.element;
    }


    alignSelected(alignment, selectedItem) {
      // If selectedItem is not passed, try to find it
      if (!selectedItem) {
          selectedItem = this.editor.querySelector('.img-resize-wrapper.selected, .response-field-wrapper.selected-for-deletion');
      }
      if (!selectedItem) {
          this.showNotification('Сначала выделите изображение или поле.', 'info');
          return;
      }

      // Clear existing alignment classes and styles
      selectedItem.classList.remove('float-start', 'float-end', 'mx-auto', 'd-block');
      selectedItem.style.float = '';
      selectedItem.style.marginLeft = '';
      selectedItem.style.marginRight = '';
      selectedItem.style.display = ''; // Reset display to default for the wrapper type
      selectedItem.removeAttribute('data-align'); // Clear data-align attribute

      if (alignment === 'none' || alignment === 'clear') {
        // If "none" or "clear", reset to default block/inline-flex behavior
        if (selectedItem.classList.contains('img-resize-wrapper')) {
             selectedItem.style.display = 'block';
        } else if (selectedItem.classList.contains('response-field-wrapper')) {
             selectedItem.style.display = 'inline-flex';
        }
        selectedItem.style.marginLeft = ''; selectedItem.style.marginRight = '';
      } else if (alignment === 'full') { // For justifyFull, set to center-aligned block
        selectedItem.style.display = 'block';
        selectedItem.style.marginLeft = 'auto';
        selectedItem.style.marginRight = 'auto';
        selectedItem.dataset.align = 'center'; // Visually, justifyFull for an image/field acts like center
      }
      else {
        selectedItem.dataset.align = alignment; // Store alignment
      }
      this.recordState(false, true); // Record state after alignment change
    }

    closeAllModals() {
      console.log('closeAllModals: Closing all open modals.'); // DEBUG: Close all modals
      document.querySelectorAll('.modal.show').forEach(modal => {
        const instance = bootstrap.Modal.getInstance(modal);
        if (instance) {
            instance.hide();
            console.log(`closeAllModals: Hidden modal: ${modal.id}`); // DEBUG: Hidden modal
        }
      });
    }
    openModal(modalId) {
      console.log(`openModal: Attempting to open modal: ${modalId}`); // DEBUG: Attempting to open modal
      this.updateSelection();
      this.closeAllModals();
      setTimeout(() => {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            console.log(`openModal: Modal element found: ${modalId}`); // DEBUG: Modal element found
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
            if (modalInstance) {
                console.log(`openModal: Bootstrap Modal instance obtained for: ${modalId}. Showing...`); // DEBUG: Modal instance obtained
                modalInstance.show();
            } else {
                console.error(`openModal: Failed to get Bootstrap Modal instance for: ${modalId}`); // ERROR: Could not get instance
            }
        } else {
            console.error(`openModal: Modal element not found: ${modalId}`); // ERROR: Modal element not found
        }
      }, 150);
    }
    isPreviewMode() { return this.editor.classList.contains('preview-mode'); }
  } // --- END of EditorCore class ---

  function getattr(el, attrName, defaultValue = null) {
      return el && el[attrName] !== undefined ? el[attrName] : defaultValue;
  }
  function setattr(el, attrName, value) {
      if(el) el[attrName] = value;
  }

  // Ensure crypto.randomUUID() is polyfilled for older browsers if needed.
  // For modern browsers, it's usually available.
  if (typeof crypto.randomUUID === 'undefined') {
    crypto.randomUUID = function() {
      // This is a simple UUID v4 generator, not cryptographically strong but sufficient for this use case
      return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
      });
    };
  }


  window.editorInstance = new EditorCore();

  // Move JavaScript from partials here, within the same DOMContentLoaded listener.
  // This ensures editorInstance is defined when these scripts try to use it.

  // --- Start _settings_general.blade.php JS ---
  (() => {
    const form = document.getElementById('notebookGeneralSettingsForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const url = form.dataset.updateUrl;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            // Remove method override from data, it's handled by fetch options
            delete data['_method'];

            try {
                const response = await fetch(url, {
                    method: 'PUT', // Use PUT for update
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });

                const responseData = await response.json();

                if (response.ok) {
                    // Access editorInstance after it's guaranteed to be defined
                    window.editorInstance.showNotification('Общие настройки сохранены!', 'success');
                    // Update the H1 title if it changes
                    document.querySelector('h1 #notebook-title-header').textContent = responseData.title; // Обновлено
                } else {
                    window.editorInstance.showNotification(responseData.message || 'Ошибка сохранения общих настроек.', 'error');
                }
            } catch (error) {
                window.editorInstance.showNotification('Ошибка сети при сохранении общих настроек.', 'error');
                console.error('Error:', error);
            }
        });
    }
  })();
  // --- End _settings_general.blade.php JS ---

  // --- Start _settings_named_links.blade.php JS ---
  (() => {
    const namedLinksList = document.getElementById('namedLinksList');
    const createNamedLinkBtn = document.getElementById('createNamedLinkBtn');
    const namedLinkModal = new bootstrap.Modal(document.getElementById('namedLinkModal'));
    const namedLinkModalLabel = document.getElementById('namedLinkModalLabel');
    const namedLinkTokenDisplay = document.getElementById('namedLinkTokenDisplay');
    const namedLinkTokenInput = document.getElementById('namedLinkToken');
    const namedLinkIsActiveInput = document.getElementById('namedLinkIsActive');
    const copyNamedLinkBtn = document.getElementById('copyNamedLinkBtn');
    const namedLinkForm = document.getElementById('namedLinkForm');
    // Использование оригинального синтаксиса Blade для $notebook->id
    const notebookId = {{ $notebook->id }}; 

    // New controls for all links
    const massActionsBlock = document.getElementById('massActionsBlock');
    const deleteAllLinksBtn = document.getElementById('deleteAllLinksBtn');
    const toggleAllLinksBtn = document.getElementById('toggleAllLinksBtn');
    const overallProgressDisplay = document.getElementById('overallProgressDisplay');


    // Function to fetch and render named links
    async function fetchNamedLinks() {
        namedLinksList.innerHTML = '<p class="text-muted">Загрузка ссылок...</p>';
        try {
            const response = await fetch(`/notebooks/${notebookId}/named-links`);
            const links = await response.json();

            // Check notebook access status
            const notebookAccessResponse = await fetch(`/notebooks/${notebookId}/access-status`);
            const notebookAccessData = await notebookAccessResponse.json();
            const isNotebookClosed = notebookAccessData.access === 'closed';

            if (response.ok) {
                if (links.length === 0) {
                    namedLinksList.innerHTML = '<p>Пока нет созданных ссылок для этой тетради.</p>';
                    // Hide mass action block if no links exist
                    if (massActionsBlock) massActionsBlock.style.display = 'none';
                    return;
                }
                
                // Show mass action block if links exist
                if (massActionsBlock) massActionsBlock.style.display = 'block';


                let totalInstances = 0;
                let completedInstances = 0;

                let html = '<ul class="list-group">';
                links.forEach(link => {
                    // Determine actual link activity based on notebook access
                    const actualIsActive = link.is_active && !isNotebookClosed;
                    const statusText = actualIsActive ? '<span class="badge bg-success">Активна</span>' : '<span class="badge bg-secondary">Неактивна</span>';
                    const linkUrl = `${window.location.origin}/named-links/${link.token}`;
                    // Ensure student_notebook_instance_id is available before using it
                    const progressUrl = link.student_instance && link.student_instance.id ? `/student-notebook-instances/${link.student_instance.id}/progress` : '#';

                    // Counting for overall progress
                    totalInstances++;
                    if (link.student_instance && link.student_instance.is_completed) { // Assuming student_instance has an is_completed field
                        completedInstances++;
                    }


                    html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <strong>${window.editorInstance.escapeHtml(link.title || 'Без названия')}</strong> ${statusText}
                                <br>
                                <small class="text-muted">Токен: <a href="${window.editorInstance.escapeHtml(linkUrl)}" target="_blank">${window.editorInstance.escapeHtml(link.token)}</a></small>
                            </div>
                            <div class="mt-2 mt-md-0">
                                <button class="btn btn-info btn-sm view-progress-btn me-1" data-instance-id="${link.student_instance ? link.student_instance.id : ''}" ${link.student_instance ? '' : 'disabled'}>
                                    <i class="bi bi-eye"></i> Прогресс
                                </button>
                                <button class="btn btn-secondary btn-sm edit-named-link-btn me-1" data-id="${link.id}" data-title="${window.editorInstance.escapeHtml(link.title || '')}" data-is-active="${link.is_active ? '1' : '0'}" data-token="${link.token}">
                                    <i class="bi bi-pencil"></i> Изменить
                                </button>
                                <button class="btn btn-danger btn-sm delete-named-link-btn" data-id="${link.id}">
                                    <i class="bi bi-trash"></i> Удалить
                                </button>
                            </div>
                        </li>
                    `;
                });
                html += '</ul>';
                namedLinksList.innerHTML = html;
                addLinkEventListeners();

                // Update overall progress
                if (overallProgressDisplay) {
                    overallProgressDisplay.innerHTML = `Общий прогресс: ${completedInstances} из ${totalInstances} (${totalInstances > 0 ? ((completedInstances / totalInstances) * 100).toFixed(0) : 0}%)`;
                }

            } else {
                window.editorInstance.showNotification(links.message || 'Ошибка загрузки ссылок.', 'error');
            }
        } catch (error) {
            window.editorInstance.showNotification('Ошибка сети при загрузке ссылок.', 'error');
            console.error('Error fetching named links:', error);
        }
    }

    // Add event listeners for dynamically created buttons
    function addLinkEventListeners() {
        document.querySelectorAll('.edit-named-link-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const title = btn.dataset.title;
                // ИСПРАВЛЕНИЕ: Изменить строгое сравнение со строкой 'true' на нестрогое сравнение с '1'.
                const isActive = btn.dataset.isActive == '1'; 
                const token = btn.dataset.token;

                namedLinkModalLabel.textContent = 'Редактировать ссылку';
                namedLinkForm.action = `/notebooks/${notebookId}/named-links/${id}`;
                document.getElementById('namedLinkMethod').value = 'PUT';
                document.getElementById('namedLinkId').value = id;
                document.getElementById('namedLinkTitle').value = title;
                document.getElementById('namedLinkIsActive').checked = isActive; // Set checkbox activity status

                namedLinkTokenInput.value = `${window.location.origin}/named-links/${token}`;
                namedLinkTokenDisplay.style.display = 'block';

                namedLinkModal.show();
            });
        });

        document.querySelectorAll('.delete-named-link-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                window.editorInstance.showNotificationWithActions('Вы уверены, что хотите удалить эту ссылку?', [
                    { text: 'Да', class: 'btn-danger', action: async () => {
                        try {
                            const response = await fetch(`/notebooks/${notebookId}/named-links/${id}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                            });
                            if (response.ok) {
                                window.editorInstance.showNotification('Ссылка успешно удалена.', 'success');
                                fetchNamedLinks(); // Refresh list
                            } else {
                                const errorData = await response.json();
                                window.editorInstance.showNotification(errorData.message || 'Ошибка удаления ссылки.', 'error');
                            }
                        } catch (error) {
                            window.editorInstance.showNotification('Ошибка сети при удалении ссылки.', 'error');
                            console.error('Error deleting named link:', error);
                        }
                    }},
                    { text: 'Нет', class: 'btn-secondary', action: () => {} }
                ]);
            });
        });

        document.querySelectorAll('.view-progress-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const instanceId = btn.dataset.instanceId;
                if (instanceId) { // Only navigate if instanceId exists
                    window.location.href = `/student-notebook-instances/${instanceId}/progress`; // Navigate to progress page
                } else {
                    window.editorInstance.showNotification('Экземпляр тетради для этой ссылки еще не создан.', 'info');
                }
            });
        });

        // Copy button for named link token
        if (copyNamedLinkBtn) {
            copyNamedLinkBtn.addEventListener('click', () => {
                const linkText = namedLinkTokenInput.value;
                if (linkText) {
                    try {
                        // Use execCommand('copy') for better compatibility within iframes
                        const textarea = document.createElement('textarea');
                        textarea.value = linkText;
                        textarea.style.position = 'fixed'; // Prevents scrolling to bottom
                        textarea.style.opacity = '0';
                        document.body.appendChild(textarea);
                        textarea.select();
                        document.execCommand('copy');
                        textarea.remove();
                        window.editorInstance.showNotification('Ссылка скопирована в буфер обмена!', 'success');
                    } catch (err) {
                        console.error('Failed to copy text: ', err);
                        window.editorInstance.showNotification('Не удалось скопировать ссылку.', 'error');
                    }
                }
            });
        }
    }

    // Handle create new link button click
    createNamedLinkBtn.addEventListener('click', () => {
        namedLinkModalLabel.textContent = 'Создать новую ссылку';
        namedLinkForm.action = `/notebooks/${notebookId}/named-links`;
        document.getElementById('namedLinkMethod').value = 'POST';
        document.getElementById('namedLinkId').value = '';
        document.getElementById('namedLinkTitle').value = '';
        document.getElementById('namedLinkIsActive').checked = true;
        namedLinkTokenDisplay.style.display = 'none'; // Hide token for new links
        namedLinkModal.show();
    });

    // Handle form submission for create/edit
    namedLinkForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const url = namedLinkForm.action;
        const method = document.getElementById('namedLinkMethod').value;
        const formData = new FormData(namedLinkForm);
        const data = Object.fromEntries(formData.entries());

        // Checkbox values are tricky; ensure `is_active` is boolean
        data.is_active = document.getElementById('namedLinkIsActive').checked;


        // Remove _method from data, it's handled by fetch options
        delete data['_method'];

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });

            const responseData = await response.json();

            if (response.ok) {
                window.editorInstance.showNotification('Ссылка успешно сохранена.', 'success');
                namedLinkModal.hide();
                fetchNamedLinks(); // Refresh the list
            } else {
                window.editorInstance.showNotification(responseData.message || 'Ошибка сохранения ссылки.', 'error');
            }
        } catch (error) {
            window.editorInstance.showNotification('Ошибка сети при сохранении ссылки.', 'error');
            console.error('Error saving named link:', error);
        }
    });

    // New handlers for managing all links
    if (deleteAllLinksBtn) {
        deleteAllLinksBtn.addEventListener('click', () => {
            window.editorInstance.showNotificationWithActions('Вы уверены, что хотите удалить ВСЕ ссылки для этой тетради? Это действие необратимо.', [
                { text: 'Да, удалить все', class: 'btn-danger', action: async () => {
                    try {
                        const response = await fetch(`/notebooks/${notebookId}/named-links/destroy-all`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                        });
                        if (response.ok) {
                            window.editorInstance.showNotification('Все ссылки успешно удалены.', 'success');
                            fetchNamedLinks(); // Refresh list
                        } else {
                            const errorData = await response.json();
                            window.editorInstance.showNotification(errorData.message || 'Ошибка удаления всех ссылок.', 'error');
                        }
                    } catch (error) {
                        window.editorInstance.showNotification('Ошибка сети при удалении всех ссылок.', 'error');
                        console.error('Error deleting all named links:', error);
                    }
                }},
                { text: 'Отмена', class: 'btn-secondary', action: () => {} }
            ]);
        });
    }

    if (toggleAllLinksBtn) {
        toggleAllLinksBtn.addEventListener('click', () => {
            // Determine current state (if at least one is active, suggest deactivating all; otherwise - activate)
            const currentLinks = Array.from(namedLinksList.querySelectorAll('.list-group-item'));
            const anyActive = currentLinks.some(li => li.querySelector('.badge.bg-success'));
            const actionType = anyActive ? 'deactivate' : 'activate';
            const message = anyActive ? 'Вы уверены, что хотите ДЕАКТИВИРОВАТЬ все ссылки для этой тетради?' : 'Вы уверены, что хотите АКТИВИРОВАТЬ все ссылки для этой тетради?';
            const confirmText = anyActive ? 'Деактивировать все' : 'Активировать все';
            const buttonClass = anyActive ? 'btn-warning' : 'btn-success';

            window.editorInstance.showNotificationWithActions(message, [
                { text: confirmText, class: buttonClass, action: async () => {
                    try {
                        const response = await fetch(`/notebooks/${notebookId}/named-links/toggle-all`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ action: actionType })
                        });
                        if (response.ok) {
                            window.editorInstance.showNotification(`Все ссылки успешно ${actionType === 'activate' ? 'активированы' : 'деактивированы'}.`, 'success');
                            fetchNamedLinks(); // Refresh list
                        } else {
                            const errorData = await response.json();
                            window.editorInstance.showNotification(errorData.message || 'Ошибка изменения статуса ссылок.', 'error');
                        }
                    } catch (error) {
                        window.editorInstance.showNotification('Ошибка сети при изменении статуса ссылок.', 'error');
                        console.error('Error toggling all named links:', error);
                    }
                }},
                { text: 'Отмена', class: 'btn-secondary', action: () => {} }
            ]);
        });
    }


    // Initial fetch when the tab becomes active
    const settingsTab = document.getElementById('settings-tab');
    settingsTab.addEventListener('shown.bs.tab', fetchNamedLinks);

    // Also fetch on initial load if settings tab is somehow active or for first view
    if (settingsTab.classList.contains('active')) {
        fetchNamedLinks();
    }
  })();
  // --- End _settings_named_links.blade.php JS ---

  // --- Start _settings_versions.blade.php JS ---
  (() => {
    const notebookVersionsList = document.getElementById('notebookVersionsList');
    const snapshotPreviewModal = new bootstrap.Modal(document.getElementById('snapshotPreviewModal'));
    const snapshotPreviewModalLabel = document.getElementById('snapshotPreviewModalLabel');
    const snapshotPreviewContent = document.getElementById('snapshotPreviewContent');
    // Использование оригинального синтаксиса Blade для $notebook->id
    const notebookId = {{ $notebook->id }}; 

    // Function to fetch and render notebook versions (snapshots)
    async function fetchNotebookVersions() {
        notebookVersionsList.innerHTML = '<p class="text-muted">Загрузка версий...</p>';
        try {
            const response = await fetch(`/notebooks/${notebookId}/snapshots`); // New route for snapshots
            const snapshots = await response.json();

            if (response.ok) {
                if (snapshots.length === 0) {
                    notebookVersionsList.innerHTML = '<p>Нет сохраненных версий тетради.</p>';
                    return;
                }
                let html = '<ul class="list-group">';
                snapshots.forEach(snapshot => {
                    // Handle $notebook->current_snapshot_id being null. Использование оригинального синтаксиса Blade.
                    const currentSnapshotId = {{ $notebook->current_snapshot_id ?? 'null' }};
                    const isActiveText = snapshot.id === currentSnapshotId ? '<span class="badge bg-primary ms-2">Активна</span>' : '';
                    html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <strong>Версия ${snapshot.version_number}</strong>
                                <small class="text-muted ms-2">${new Date(snapshot.created_at).toLocaleString()}</small>
                                ${isActiveText}
                            </div>
                            <div class="mt-2 mt-md-0">
                                <button class="btn btn-info btn-sm preview-snapshot-btn me-1" data-id="${snapshot.id}" data-version="${snapshot.version_number}">
                                    <i class="bi bi-eye"></i> Просмотр
                                </button>
                                <button class="btn btn-warning btn-sm revert-snapshot-btn" data-id="${snapshot.id}" data-created-at="${snapshot.created_at}">
                                    <i class="bi bi-arrow-counterclockwise"></i> Откатить
                                </button>
                            </div>
                        </li>
                    `;
                });
                html += '</ul>';
                notebookVersionsList.innerHTML = html;
                addVersionEventListeners();
            } else {
                window.editorInstance.showNotification(snapshots.message || 'Ошибка загрузки версий тетради.', 'error');
            }
        } catch (error) {
            window.editorInstance.showNotification('Ошибка сети при загрузке версий тетради.', 'error');
            console.error('Error fetching notebook versions:', error);
        }
    }

    function addVersionEventListeners() {
        document.querySelectorAll('.preview-snapshot-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const snapshotId = btn.dataset.id;
                const version = btn.dataset.version;
                snapshotPreviewModalLabel.textContent = `Предпросмотр версии ${version}`;
                snapshotPreviewContent.innerHTML = '<p class="text-muted text-center">Загрузка контента...</p>';

                try {
                    // ----------- FIX: Corrected the fetch URL to include the notebook ID -----------
                    const response = await fetch(`/notebooks/${notebookId}/snapshots/${snapshotId}/content`);
                    const data = await response.json();
                    if (response.ok && data.content_html) {
                        snapshotPreviewContent.innerHTML = data.content_html;
                        // For preview, disable all interactive elements within the content
                        snapshotPreviewContent.querySelectorAll('[contenteditable], [draggable], .img-resize-handle, .table-drag-handle, .response-field .remove-btn').forEach(el => {
                            if (el.hasAttribute('contenteditable')) el.setAttribute('contenteditable', 'false');
                            if (el.hasAttribute('draggable')) el.setAttribute('draggable', 'false');
                            el.style.display = 'none'; // Hide handles/buttons
                            el.style.cursor = 'default';
                        });
                        // Convert response-fields to disabled inputs for proper preview display
                        snapshotPreviewContent.querySelectorAll('.response-field-wrapper').forEach(wrapper => {
                            const field = wrapper.querySelector('.response-field');
                            if (!field) return;
                            const type = field.dataset.type;
                            const label = field.dataset.label || `[${type} field]`;
                            let inputHtml = `<div class="mb-2 form-group preview-field-render-wrapper">`;
                            if (type === 'text') {
                              inputHtml += `<input type="text" class="form-control preview-response-input" value="" placeholder="${window.editorInstance.escapeHtml(label)}" readonly>`;
                            } else if (type === 'select') {
                              const options = JSON.parse(field.dataset.options || '[]');
                              let optionsHtml = '';
                              options.forEach(opt => { optionsHtml += `<option value="${window.editorInstance.escapeHtml(opt)}">${window.editorInstance.escapeHtml(opt)}</option>`; });
                              if (options.length === 0) {
                                  optionsHtml += `<option value="" disabled selected>Нет вариантов</option>`;
                              }
                              inputHtml += `<select class="form-select preview-response-select" disabled>${optionsHtml}</select>`;
                            } else if (type === 'file') {
                              inputHtml += `<input type="file" class="form-control preview-response-file" disabled>`;
                            } else if (type === 'scale') {
                              // Changed to retrieve values from correct_answers dataset
                              let min = '1', max = '5', step = '1', defaultVal = '1', prefix = '', suffix = '';
                              try {
                                  const correctAnswers = JSON.parse(field.dataset.correctAnswers || '{}');
                                  defaultVal = correctAnswers.default_value || field.dataset.min || '1'; // Fallback to min if default_value not set
                                  prefix = correctAnswers.prefix || '';
                                  suffix = correctAnswers.suffix || '';
                              } catch (e) {
                                  // Fallback to direct dataset for old format
                                  defaultVal = field.dataset.default || '1';
                                  prefix = field.dataset.prefix || '';
                                  suffix = field.dataset.suffix || '';
                              }
                              min = field.dataset.min || '1'; // min/max/step are from validation_rules, not correct_answers
                              max = field.dataset.max || '5';
                              step = field.dataset.step || '1';

                              inputHtml += `<input type="range" class="form-range preview-response-scale" min="${window.editorInstance.escapeHtml(min)}" max="${window.editorInstance.escapeHtml(max)}" step="${window.editorInstance.escapeHtml(step)}" value="${window.editorInstance.escapeHtml(defaultVal)}" disabled>`;
                              inputHtml += `<span class="ms-2 text-muted">(${window.editorInstance.escapeHtml(prefix)}${window.editorInstance.escapeHtml(defaultVal)}${window.editorInstance.escapeHtml(suffix)})</span>`;
                            }
                            inputHtml += `</div>`;
                            wrapper.outerHTML = inputHtml;
                        });

                    } else {
                        snapshotPreviewContent.innerHTML = `<p class="text-danger text-center">Не удалось загрузить контент версии.</p>`;
                    }
                } catch (error) {
                    snapshotPreviewContent.innerHTML = `<p class="text-danger text-center">Ошибка сети при загрузке контента.</p>`;
                    console.error('Error fetching snapshot content:', error);
                }
                snapshotPreviewModal.show();
            });
        });

        document.querySelectorAll('.revert-snapshot-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const snapshotId = btn.dataset.id;
                const snapshotCreatedAt = btn.dataset.createdAt; // Get timestamp of selected snapshot

                // Get all existing snapshots and their created_at
                const allSnapshots = Array.from(document.querySelectorAll('.revert-snapshot-btn')).map(el => ({
                    id: el.dataset.id,
                    createdAt: el.dataset.createdAt
                }));

                // Determine if there are newer snapshots
                const hasNewerSnapshots = allSnapshots.some(s => {
                    return (new Date(s.createdAt).getTime() > new Date(snapshotCreatedAt).getTime()) ||
                           (new Date(s.createdAt).getTime() === new Date(snapshotCreatedAt).getTime() && parseInt(s.id) > parseInt(snapshotId));
                });

                let message = 'Вы уверены, что хотите откатить тетрадь к этой версии? Будет создана новая версия с содержимым выбранной.';
                if (hasNewerSnapshots) {
                    message += `<br><strong class="text-danger">ВНИМАНИЕ: Все более поздние версии будут удалены безвозвратно.</strong>`;
                }

                window.editorInstance.showNotificationWithActions(message, [
                    { text: 'Да, откатить', class: 'btn-warning', action: async () => {
                        try {
                            const response = await fetch(`/notebooks/${notebookId}/revert-snapshot/${snapshotId}`, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                            });
                            const responseData = await response.json();
                            if (response.ok) {
                                window.editorInstance.showNotification('Тетрадь успешно откачена к выбранной версии.', 'success');
                                // Force page reload to load the new current snapshot into the editor
                                window.location.reload();
                            } else {
                                window.editorInstance.showNotification(responseData.message || 'Ошибка отката версии тетради.', 'error');
                            }
                        } catch (error) {
                            window.editorInstance.showNotification('Ошибка сети при откате версии тетради.', 'error');
                            console.error('Error reverting snapshot:', error);
                        }
                    }},
                    { text: 'Отмена', class: 'btn-secondary', action: () => {} }
                ]);
            });
        });

        // Listen for custom event to refresh versions list after a snapshot save
        document.addEventListener('notebookSnapshotSaved', fetchNotebookVersions);

        // Initial fetch when the tab becomes active
        const versionsTab = document.getElementById('versions-tab'); // Corrected to versions-tab
        versionsTab.addEventListener('shown.bs.tab', fetchNotebookVersions);

        // Also fetch on initial load if settings tab is somehow active or for first view
        if (versionsTab.classList.contains('active')) { // Corrected to versions-tab
            fetchNotebookVersions();
        }
  }
})();
  // --- End _settings_versions.blade.php JS ---

  // --- Start _blocks.blade.php JS (if still relevant, though Blocks are less dynamic now) ---
  (() => {
    const list = document.getElementById('blocks-list');
    if (!list) return; // Only run if blocks-list exists

    let dragSrcEl = null;

    // Function to apply CSS class on dragover
    function handleDragOver(e) {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      return false;
    }

    // dragstart event: save source element
    function handleDragStart(e) {
      dragSrcEl = this;
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('text/html', this.innerHTML);
      this.classList.add('dragging');
    }

    // drop event: swap content and remove class
    function handleDrop(e) {
      e.stopPropagation();
      if (dragSrcEl !== this) {
        // Swap in DOM
        const temp = dragSrcEl.innerHTML;
        dragSrcEl.innerHTML = this.innerHTML;
        this.innerHTML = temp;
        // After visual swap, send new order to server
        updateOrderOnServer();
      }
      return false;
    }

    // dragend event: remove class
    function handleDragEnd() {
      this.classList.remove('dragging');
    }

    // Attach handlers to each item
    function addDnDHandlers(item) {
      item.addEventListener('dragstart', handleDragStart);
      item.addEventListener('dragover', handleDragOver);
      item.addEventListener('drop', handleDrop);
      item.addEventListener('dragend', handleDragEnd);
    }

    // Initialization: find all .block-item
    Array.from(list.querySelectorAll('.block-item')).forEach(addDnDHandlers);

    // Function to collect order and send AJAX
    function updateOrderOnServer() {
      const order = Array.from(list.children).map(li => li.dataset.id);
      fetch(window.location.pathname + '/blocks/reorder', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ order })
      })
      .then(res => {
        if (!res.ok) throw new Error('Network error');
        // window.editorInstance.showNotification('Block order updated.', 'success'); // Optional notification
      })
      .catch(err => {
        console.error('Reorder failed:', err);
        // window.editorInstance.showNotification('Error changing block order.', 'error'); // Optional notification
      });
    }
  })();
  // --- End _blocks.blade.php JS ---

  // --- _modal-fields.blade.php, _modal-image.blade.php, _modal-table.blade.php have no direct JS,
  // their event listeners are handled by EditorCore.initializeModals() or related functions.
  // _toolbar.blade.php also has no direct JS, its buttons are handled by EditorCore.initializeEventListeners().
  // No need for separate IIFEs for them.
});
</script>

@endsection