@extends('welcome')

@section('title', 'Редактирование тетради: ' . $notebook->title)

@section('content')

<div class="container my-4">
<h1 class="text-center my-3">
  <span class="fw-light">Редактирование тетради:</span> {{ $notebook->title }}
</h1>
  @include('notebooks.partials._toolbar')
  @include('notebooks.partials._modal-table')
  @include('notebooks.partials._modal-fields')
  @include('notebooks.partials._modal-image')

  <div id="editor"
       contenteditable="true"
       data-save-url="{{ route('notebooks.save', $notebook) }}"
       class="border p-3"
       style="min-height:600px;">
       <p>Добро пожаловать в <strong>редактор</strong>!</p>
        <p>Это <em>начальное</em> содержимое. Вы можете <del>изменить</del> его.</p>
        <ul><li>Пункт 1</li><li>Пункт 2</li></ul>
        <p style="text-align: center;">
            <img src="https://a.d-cd.net/af498as-960.jpg" alt="Пример Картинки">
        </p>
        <p>Еще немного текста для примера.</p>
      {!! $notebook->content !!}
  </div>
</div>

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

      if (!this.isPreviewMode()) {
        this.recordState(true, false);
      }
      this.updateUndoRedoButtons();
      this.editor.focus();
      this.updateSelection();
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
                    this.updateSelection(); // Save current selection before prompt
                    const url = window.prompt('Введите URL:');
                    if (url) this.execCommand('createLink', url);
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
      if (saveBtn) saveBtn.addEventListener('click', () => this.saveContent());

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
                    this.recordState(false,true); // Record state after actual removal
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
                    this.recordState(false,true); // Record state after actual removal
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

      const fieldId = `field-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
      let iconClass = '', defaultLabel = '';

      if (type === 'text') { iconClass = 'bi bi-pencil'; defaultLabel = 'Текстовое поле'; }
      else if (type === 'select') { iconClass = 'bi bi-list'; defaultLabel = 'Поле-список'; }
      else if (type === 'file') { iconClass = 'bi bi-paperclip'; defaultLabel = 'Поле-файл'; }

      const fieldHTML = `
          <span class="response-field"
                data-type="${type}"
                data-id="${fieldId}"
                data-label="${this.escapeHtml(defaultLabel)}"
                contenteditable="false"
                draggable="false">
            <i class="${iconClass}"></i> <span class="response-text">${this.escapeHtml(defaultLabel)}</span>
            <span class="remove-btn" title="Удалить поле">×</span>
          </span>`;

      const wrapperHtml = `<div class="response-field-wrapper" contenteditable="false" data-field-marker-id="${fieldId}">${fieldHTML}</div>`;

      this.insertHTML(wrapperHtml); // Handles actual insertion and recordState
      const newWrapper = this.editor.querySelector(`.response-field-wrapper[data-field-marker-id="${fieldId}"]`);
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
        // console.log('initializeFieldEventsForElement for wrapper with field ID:', fieldWrapper.querySelector('.response-field')?.dataset.id);
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

        if (type === 'text') {
            safeSetValue('textFieldLabel'); safeSetValue('textFieldAnswer'); safeSetChecked('textFieldRequired');
        } else if (type === 'select') {
            safeSetValue('selectFieldLabel'); safeSetHTML('selectOptions'); this.addSelectOption();
        } else if (type === 'file') {
            safeSetValue('fileFieldLabel'); safeSetValue('fileFieldAccept', '*'); safeSetValue('fileFieldMaxSize', '2048');
        }
    }

    fillModalFromField(field, type) {
        this.clearModalFields(type);
        const safeSetValue = (id, value) => { const el = document.getElementById(id); if (el) el.value = value; };
        const safeSetChecked = (id, checked) => { const el = document.getElementById(id); if (el) el.checked = checked; };

        if (type === 'text') {
            safeSetValue('textFieldLabel', field.dataset.label || 'Текстовое поле');
            safeSetValue('textFieldAnswer', field.dataset.answer || '');
            safeSetChecked('textFieldRequired', field.dataset.required === 'true');
        } else if (type === 'select') {
            safeSetValue('selectFieldLabel', field.dataset.label || 'Поле-список');
            const options = JSON.parse(field.dataset.options || '[]');
            const correctIndex = parseInt(field.dataset.correct || '-1');
            const selectOptionsContainer = document.getElementById('selectOptions');
            if (selectOptionsContainer) {
                selectOptionsContainer.innerHTML = '';
                if (options.length > 0) {
                    options.forEach((optionText, index) => this.addSelectOption(optionText, index === correctIndex));
                } else { this.addSelectOption(); }
            }
        } else if (type === 'file') {
            safeSetValue('fileFieldLabel', field.dataset.label || 'Поле-файл');
            safeSetValue('fileFieldAccept', field.dataset.accept || '*');
            safeSetValue('fileFieldMaxSize', field.dataset.maxSize || '2048');
        }
    }

    addSelectOption(text = '', isCorrect = false) {
      const container = document.getElementById('selectOptions');
      if (!container) return;
      const count = container.children.length;
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
                this.currentField.dataset[key] = values[key];
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
      createSaveHandler('textFieldModal', 'text', () => ({ /* ... */
        label: document.getElementById('textFieldLabel')?.value || 'Текстовое поле',
        answer: document.getElementById('textFieldAnswer')?.value || '',
        required: (document.getElementById('textFieldRequired')?.checked || false).toString()
      }));
      createSaveHandler('selectFieldModal', 'select', () => { /* ... */
        const options = []; let correctIndex = -1;
        document.querySelectorAll('#selectOptions .input-group').forEach((group, index) => {
          const input = group.querySelector('input[type="text"]');
          const radio = group.querySelector('input[type="radio"]');
          if (input?.value.trim()) {
            options.push(input.value.trim());
            if (radio?.checked) correctIndex = index;
          }
        });
        return {
          label: document.getElementById('selectFieldLabel')?.value || 'Поле-список',
          options: JSON.stringify(options),
          correct: correctIndex.toString()
        };
      });
      createSaveHandler('fileFieldModal', 'file', () => ({ /* ... */
        label: document.getElementById('fileFieldLabel')?.value || 'Поле-файл',
        accept: document.getElementById('fileFieldAccept')?.value || '*',
        maxSize: document.getElementById('fileFieldMaxSize')?.value || '2048'
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
            this.editor.querySelectorAll('td[data-was-editable="true"]', 'th[data-was-editable="true"]').forEach(cell => {
                cell.contentEditable = 'true'; cell.removeAttribute('data-was-editable');
            });
            if (this.contentBeforePreview !== null) {
                 this.editor.innerHTML = this.contentBeforePreview; this.contentBeforePreview = null;
            }
            this.reinitializeDynamicElements(); // CRITICAL: re-initialize after restoring HTML
            if (toolbar) {
                toolbar.querySelectorAll('.btn, .form-select, .form-control, .input-group, .dropdown, select, input[type=color]').forEach(el => {
                    el.style.display = ''; if(el.disabled !== undefined) el.disabled = false;
                });
            }
            this.updateUndoRedoButtons();
            this.editor.focus();
            this.updateSelection();
        }
    }

    convertFieldsToInputs() {
      this.editor.querySelectorAll('.response-field-wrapper').forEach(wrapper => {
        const field = wrapper.querySelector('.response-field');
        if (!field) { wrapper.remove(); return; }
        const type = field.dataset.type;
        let inputHtml = `<div class="mb-2 form-group preview-field-render-wrapper">`;
        if (type === 'text') {
          const answer = field.dataset.answer || '';
          inputHtml += `<input type="text" class="form-control preview-response-input" value="${this.escapeHtml(answer)}" readonly placeholder="Ответ пользователя">`;
        } else if (type === 'select') {
          const options = JSON.parse(field.dataset.options || '[]');
          let optionsHtml = '';
          if (options.length === 0) {
              optionsHtml += `<option value="" disabled selected>Нет вариантов</option>`;
          } else {
              options.forEach(opt => { optionsHtml += `<option value="${this.escapeHtml(opt)}">${this.escapeHtml(opt)}</option>`; });
          }
          inputHtml += `<select class="form-select preview-response-select">${optionsHtml}</select>`;
        } else if (type === 'file') {
          inputHtml += `<input type="file" class="form-control preview-response-file">`;
        }
        inputHtml += `</div>`;
        wrapper.outerHTML = inputHtml;
      });
    }

    saveContent() {
      const url = this.editor.dataset.saveUrl;
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
      contentToSave = tempDiv.innerHTML;

      fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''},
        body: JSON.stringify({content: contentToSave})
      })
      .then(async resp => {
        if (resp.ok) {
            this.showNotification('Сохранено!', 'success');
            // Reset undo/redo stacks to reflect the saved state as the new baseline
            this.undoStack = [{html: contentToSave, hasMarkers: false}]; // Store only the clean HTML
            this.redoStack = [];
            this.updateUndoRedoButtons();
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

        const messageP = document.createElement('p'); messageP.textContent = message;
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
      document.querySelectorAll('.modal.show').forEach(modal => {
        const instance = bootstrap.Modal.getInstance(modal); if (instance) instance.hide();
      });
    }
    openModal(modalId) {
      this.updateSelection();
      this.closeAllModals();
      setTimeout(() => {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
            modalInstance.show();
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

  window.editorInstance = new EditorCore();
});

</script>

@endsection
