<div class="card mb-4">
    <div class="card-header">
        <h4 class="mb-0">История версий тетради (Снимки)</h4>
    </div>
    <div class="card-body">
        <div id="notebookVersionsList">
            <p class="text-muted">Загрузка версий...</p>
        </div>
    </div>
</div>

{{-- Modal for Previewing Snapshot --}}
<div class="modal fade" id="snapshotPreviewModal" tabindex="-1" aria-labelledby="snapshotPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="snapshotPreviewModalLabel">Предпросмотр версии тетради</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="snapshotPreviewContent" class="border p-3" style="min-height: 300px;">
                    <!-- Snapshot content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const notebookVersionsList = document.getElementById('notebookVersionsList');
    const snapshotPreviewModal = new bootstrap.Modal(document.getElementById('snapshotPreviewModal'));
    const snapshotPreviewModalLabel = document.getElementById('snapshotPreviewModalLabel');
    const snapshotPreviewContent = document.getElementById('snapshotPreviewContent');
    const notebookId = {{ $notebook->id }}; // Get notebook ID from Blade

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
                    const isActiveText = snapshot.id === {{ $notebook->current_snapshot_id ?? 'null' }} ? '<span class="badge bg-primary ms-2">Активна</span>' : '';
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
                                <button class="btn btn-warning btn-sm revert-snapshot-btn" data-id="${snapshot.id}">
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
                editorInstance.showNotification(snapshots.message || 'Ошибка загрузки версий тетради.', 'error');
            }
        } catch (error) {
            editorInstance.showNotification('Ошибка сети при загрузке версий тетради.', 'error');
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
                    const response = await fetch(`/notebooks/${notebookId}/snapshots/${snapshotId}/content`); // Corrected route
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
                              inputHtml += `<input type="text" class="form-control preview-response-input" value="" placeholder="${editorInstance.escapeHtml(label)}" readonly>`;
                            } else if (type === 'select') {
                              const options = JSON.parse(field.dataset.options || '[]');
                              let optionsHtml = '';
                              options.forEach(opt => { optionsHtml += `<option value="${editorInstance.escapeHtml(opt)}">${editorInstance.escapeHtml(opt)}</option>`; });
                              if (options.length === 0) {
                                  optionsHtml += `<option value="" disabled selected>Нет вариантов</option>`;
                              }
                              inputHtml += `<select class="form-select preview-response-select" disabled>${optionsHtml}</select>`;
                            } else if (type === 'file') {
                              inputHtml += `<input type="file" class="form-control preview-response-file" disabled>`;
                            } else if (type === 'scale') {
                              const min = field.dataset.min || '1';
                              const max = field.dataset.max || '5';
                              const step = field.dataset.step || '1';
                              const defaultVal = field.dataset.default || '1';
                              const prefix = field.dataset.prefix || '';
                              const suffix = field.dataset.suffix || '';
                              inputHtml += `<input type="range" class="form-range preview-response-scale" min="${editorInstance.escapeHtml(min)}" max="${editorInstance.escapeHtml(max)}" step="${editorInstance.escapeHtml(step)}" value="${editorInstance.escapeHtml(defaultVal)}" disabled>`;
                              inputHtml += `<span class="ms-2 text-muted">(${editorInstance.escapeHtml(prefix)}${editorInstance.escapeHtml(defaultVal)}${editorInstance.escapeHtml(suffix)})</span>`;
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
                editorInstance.showNotificationWithActions('Вы уверены, что хотите откатить тетрадь к этой версии? Будет создана новая версия с содержимым выбранной.', [
                    { text: 'Да', class: 'btn-warning', action: async () => {
                        try {
                            const response = await fetch(`/notebooks/${notebookId}/revert-snapshot/${snapshotId}`, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                            });
                            const responseData = await response.json();
                            if (response.ok) {
                                editorInstance.showNotification('Тетрадь успешно откачена к выбранной версии.', 'success');
                                // Force page reload to load the new current snapshot into the editor
                                window.location.reload();
                            } else {
                                editorInstance.showNotification(responseData.message || 'Ошибка отката версии тетради.', 'error');
                            }
                        } catch (error) {
                            editorInstance.showNotification('Ошибка сети при откате версии тетради.', 'error');
                            console.error('Error reverting snapshot:', error);
                        }
                    }},
                    { text: 'Нет', class: 'btn-secondary', action: () => {} }
                ]);
            });
        });
    }

    // Initial fetch when the tab becomes active
    const settingsTab = document.getElementById('settings-tab');
    settingsTab.addEventListener('shown.bs.tab', fetchNotebookVersions);

    // Also fetch on initial load if settings tab is somehow active or for first view
    if (settingsTab.classList.contains('active')) {
        fetchNotebookVersions();
    }
});
</script>