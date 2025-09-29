/**
 * Drag & Drop Website Builder JavaScript
 */

class DragDropBuilder {
    constructor() {
        this.currentSelectedComponent = null;
        this.currentComponent = null;
        this.settingsPanel = document.getElementById('settings-panel');
        this.settingsForm = document.getElementById('settings-form');
        this.settingsFields = document.getElementById('settings-fields');
        this.canvasContent = document.getElementById('canvas-content');

        this.init();
    }

    init() {
        this.setupDragAndDrop();
        this.setupSortable();
        this.setupEventListeners();
        this.setupComponentControls();
    }

    setupDragAndDrop() {
        // Make components draggable from sidebar
        const componentItems = document.querySelectorAll('.ddb-component-item');

        componentItems.forEach(item => {
            item.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', item.dataset.componentType);
                item.classList.add('dragging');
            });

            item.addEventListener('dragend', (e) => {
                item.classList.remove('dragging');
            });
        });

        // Setup drop zones
        this.setupDropZones();
    }

    setupDropZones() {
        const dropZones = document.querySelectorAll('.ddb-drop-zone');

        dropZones.forEach(zone => {
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('dragover');
            });

            zone.addEventListener('dragleave', (e) => {
                zone.classList.remove('dragover');
            });

            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('dragover');

                const componentType = e.dataTransfer.getData('text/plain');
                if (componentType) {
                    // Get position, default to null if not set
                    const position = zone.dataset.position || null;
                    this.addComponent(componentType, position);
                }
            });
        });

        // Make canvas droppable
        this.canvasContent.addEventListener('dragover', (e) => {
            e.preventDefault();
        });

        this.canvasContent.addEventListener('drop', (e) => {
            e.preventDefault();

            // Check if we're dropping on a component or in empty space
            if (!e.target.closest('.ddb-component') && !e.target.closest('.ddb-drop-zone')) {
                const componentType = e.dataTransfer.getData('text/plain');
                if (componentType) {
                    this.addComponent(componentType);
                }
            }
        });
    }

    setupSortable() {
        // Make component list sortable
        new Sortable(this.canvasContent, {
            animation: 150,
            handle: '.ddb-move-component',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            onEnd: (evt) => {
                this.reorderComponents();
            }
        });
    }

    setupEventListeners() {
        // Settings panel controls
        document.getElementById('close-settings').addEventListener('click', () => {
            this.closeSettings();
        });

        document.getElementById('cancel-settings').addEventListener('click', () => {
            this.closeSettings();
        });

        // Settings form submission
        this.settingsForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveComponentSettings();
        });

        // Toolbar buttons
        document.getElementById('preview-btn').addEventListener('click', () => {
            this.previewPage();
        });

        document.getElementById('save-btn').addEventListener('click', () => {
            this.savePage();
        });
    }

    setupComponentControls() {
        // Use event delegation for dynamic components
        this.canvasContent.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.ddb-edit-component');
            const deleteBtn = e.target.closest('.ddb-delete-component');
            const component = e.target.closest('.ddb-component');

            if (editBtn && component) {
                e.stopPropagation();
                this.editComponent(component);
                return;
            }

            if (deleteBtn && component) {
                e.stopPropagation();
                this.deleteComponent(component);
                return;
            }

            // Select component on click
            if (component) {
                this.selectComponent(component);
            } else {
                this.deselectComponent();
            }
        });
    }

    addComponent(componentType, position = null) {
        console.log('Adding component:', componentType, 'at position:', position); // Debug log

        // Convert 'end' to null on the client side to avoid any server-side issues
        if (position === 'end' || position === '' || position === undefined) {
            position = null;
            console.log('Converted position to null');
        }

        const data = new FormData();
        data.append('action', 'add_component');
        data.append('component_type', componentType);
        if (position !== null) {
            data.append('position', position);
        }

        fetch('', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: data
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    this.refreshCanvas();
                    this.showNotification('Component added successfully', 'success');
                } else {
                    this.showNotification('Error adding component: ' + (result.error || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Error adding component', 'error');
            });
    }

    editComponent(componentElement) {
        const componentId = componentElement.dataset.componentId;

        if (!componentId) {
            this.showNotification('Component ID not found', 'error');
            return;
        }

        this.selectComponent(componentElement);

        // Get component settings
        const data = new FormData();
        data.append('action', 'get_component_settings');
        data.append('component_id', componentId);

        fetch('', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: data
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    this.showSettings(componentId, result.component, result.settings);
                } else {
                    this.showNotification('Error loading component settings: ' + (result.error || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Error loading component settings', 'error');
            });
    }

    showSettings(componentId, component, settings) {
        this.currentComponentId = componentId;
        this.currentComponent = component; // Store the component data

        document.getElementById('settings-title').textContent = `Edit ${component.name}`;

        // Clear previous fields
        this.settingsFields.innerHTML = '';

        // Generate form fields
        const componentFields = component.fields || component.settings || {};
        Object.entries(componentFields).forEach(([key, field]) => {
            const value = settings[key] || field.default || '';
            const fieldHTML = this.generateFormField(key, field, value);
            this.settingsFields.innerHTML += fieldHTML;
        });

        // Setup repeater field listeners
        Object.entries(componentFields).forEach(([key, field]) => {
            if (field.type === 'repeater') {
                this.setupRepeaterFieldListeners(key);
            }
        });

        // Show settings panel
        this.settingsPanel.classList.add('active');
    }

    generateFormField(name, field, value) {
        const fieldId = `field-${name}`;
        let inputHTML = '';

        switch (field.type) {
            case 'text':
                inputHTML = `<input type="text" id="${fieldId}" name="${name}" value="${this.escapeHtml(value)}" class="ddb-form-control">`;
                break;

            case 'textarea':
                inputHTML = `<textarea id="${fieldId}" name="${name}" rows="3" class="ddb-form-control">${this.escapeHtml(value)}</textarea>`;
                break;

            case 'wysiwyg':
                inputHTML = `<textarea id="${fieldId}" name="${name}" rows="6" class="ddb-form-control">${this.escapeHtml(value)}</textarea>`;
                break;

            case 'select':
                let options = '';
                Object.entries(field.options).forEach(([optValue, optLabel]) => {
                    const selected = value === optValue ? 'selected' : '';
                    options += `<option value="${optValue}" ${selected}>${this.escapeHtml(optLabel)}</option>`;
                });
                inputHTML = `<select id="${fieldId}" name="${name}" class="ddb-form-control">${options}</select>`;
                break;

            case 'color':
                inputHTML = `<input type="color" id="${fieldId}" name="${name}" value="${value}" class="ddb-form-control">`;
                break;

            case 'image':
                let previewUrl = value;
                let displayStyle = value ? 'block' : 'none';
                let dropZoneStyle = value ? 'none' : 'block';
                
                // Handle emoji values for preview
                if (value && value.startsWith('emoji:')) {
                    const emoji = value.substring(6);
                    previewUrl = `data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><text y="50" font-size="50">${emoji}</text></svg>`;
                }
                
                inputHTML = `
                    <div class="ddb-image-field">
                        <div class="ddb-image-drop-zone" id="${fieldId}-dropzone" style="display: ${dropZoneStyle};" 
                             onclick="document.getElementById('${fieldId}-file').click()"
                             ondragover="window.builder.handleDragOver(event)"
                             ondragleave="window.builder.handleDragLeave(event)"
                             ondrop="window.builder.handleImageDrop(event, '${fieldId}')">
                            <div class="ddb-drop-zone-icon">📁</div>
                            <div><strong>Click to upload</strong> or drag and drop</div>
                            <div class="ddb-drop-zone-text">Images, URLs, or emojis • PNG, JPG, GIF up to 5MB</div>
                        </div>
                        <div class="ddb-image-preview" id="${fieldId}-preview" style="display: ${displayStyle};">
                            <img src="${this.escapeHtml(previewUrl)}" alt="Preview" style="max-width: 100px; max-height: 100px; border-radius: 4px;">
                            <button type="button" class="ddb-btn-remove" onclick="window.builder.removeImage('${fieldId}')" title="Remove image">×</button>
                        </div>
                        <div class="ddb-image-controls">
                            <input type="file" id="${fieldId}-file" accept="image/*" class="ddb-file-input" onchange="window.builder.handleImageUpload(event, '${fieldId}')" style="display: none;">
                            <input type="url" id="${fieldId}" name="${name}" value="${this.escapeHtml(value)}" placeholder="Or enter image URL" class="ddb-form-control" onchange="window.builder.updateImagePreview('${fieldId}', this.value)">
                            <div class="ddb-image-buttons">
                                <button type="button" class="ddb-btn-secondary" onclick="document.getElementById('${fieldId}-file').click()">📁 Upload</button>
                                <button type="button" class="ddb-btn-secondary" onclick="window.builder.openEmojiPicker('${fieldId}')">😀 Emoji</button>
                            </div>
                        </div>
                    </div>
                `;
                break;

            case 'repeater':
                // Handle repeater fields for statistics and similar data
                let repeaterValue = value;
                if (typeof value === 'string') {
                    try {
                        repeaterValue = JSON.parse(value);
                    } catch (e) {
                        repeaterValue = [];
                    }
                }
                if (!Array.isArray(repeaterValue)) {
                    repeaterValue = [];
                }

                inputHTML = `
                    <div class="ddb-repeater" data-field-name="${name}">
                        <div class="ddb-repeater-items" id="${fieldId}-items">
                            ${repeaterValue.map((item, index) => this.generateRepeaterItem(name, field, item, index)).join('')}
                        </div>
                        <button type="button" class="ddb-btn-secondary" onclick="window.builder.addRepeaterItem('${name}', '${fieldId}')">
                            ➕ Add ${field.label || 'Item'}
                        </button>
                        <input type="hidden" id="${fieldId}" name="${name}" value="${this.escapeHtml(JSON.stringify(repeaterValue))}">
                    </div>
                `;
                break;

            default:
                inputHTML = `<input type="text" id="${fieldId}" name="${name}" value="${this.escapeHtml(value)}" class="ddb-form-control">`;
        }

        return `
            <div class="ddb-form-group">
                <label for="${fieldId}" class="ddb-form-label">${this.escapeHtml(field.label)}</label>
                ${inputHTML}
            </div>
        `;
    }

    generateRepeaterItem(fieldName, field, item, index) {
        const fields = field.fields || {};
        let fieldsHtml = '';

        Object.entries(fields).forEach(([subFieldName, subField]) => {
            const subFieldId = `${fieldName}-${index}-${subFieldName}`;
            const subFieldValue = item[subFieldName] || subField.default || '';
            let fieldInput = '';

            switch (subField.type) {
                case 'image':
                    let previewUrl = subFieldValue;
                    let displayStyle = subFieldValue ? 'block' : 'none';
                    
                    // Handle legacy emoji values and emoji: prefix values for preview
                    if (subFieldValue) {
                        if (subFieldValue.startsWith('emoji:')) {
                            const emoji = subFieldValue.substring(6);
                            previewUrl = `data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><text y="50" font-size="50">${emoji}</text></svg>`;
                        } else if (!subFieldValue.startsWith('http') && !subFieldValue.startsWith('/')) {
                            // Legacy emoji format - convert to emoji: format for internal handling
                            const legacyEmoji = subFieldValue;
                            previewUrl = `data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><text y="50" font-size="50">${legacyEmoji}</text></svg>`;
                            // Update the value to new format
                            subFieldValue = `emoji:${legacyEmoji}`;
                        }
                    }
                    
                    fieldInput = `
                        <div class="ddb-image-field">
                            <div class="ddb-image-drop-zone" id="${subFieldId}-dropzone" style="display: ${subFieldValue ? 'none' : 'block'};" 
                                 onclick="document.getElementById('${subFieldId}-file').click()"
                                 ondragover="window.builder.handleDragOver(event)"
                                 ondragleave="window.builder.handleDragLeave(event)"
                                 ondrop="window.builder.handleImageDrop(event, '${subFieldId}')">
                                <div class="ddb-drop-zone-icon">📁</div>
                                <div><strong>Click to upload</strong></div>
                                <div class="ddb-drop-zone-text">or drag images, URLs, emojis</div>
                            </div>
                            <div class="ddb-image-preview" id="${subFieldId}-preview" style="display: ${displayStyle};">
                                <img src="${this.escapeHtml(previewUrl)}" alt="Preview" style="max-width: 60px; max-height: 60px; border-radius: 4px;">
                                <button type="button" class="ddb-btn-remove" onclick="window.builder.removeImage('${subFieldId}')" title="Remove image">×</button>
                            </div>
                            <div class="ddb-image-controls">
                                <input type="file" id="${subFieldId}-file" accept="image/*" class="ddb-file-input" onchange="window.builder.handleImageUpload(event, '${subFieldId}')" style="display: none;">
                                <input type="url" 
                                       class="ddb-form-control ddb-repeater-field" 
                                       id="${subFieldId}"
                                       data-field="${subFieldName}"
                                       data-index="${index}"
                                       data-parent="${fieldName}"
                                       value="${this.escapeHtml(subFieldValue)}"
                                       placeholder="Image URL"
                                       onchange="window.builder.updateImagePreview('${subFieldId}', this.value)">
                                <div class="ddb-image-buttons">
                                    <button type="button" class="ddb-btn-secondary ddb-btn-sm" onclick="document.getElementById('${subFieldId}-file').click()">📁</button>
                                    <button type="button" class="ddb-btn-secondary ddb-btn-sm" onclick="window.builder.openEmojiPicker('${subFieldId}')">😀</button>
                                </div>
                            </div>
                        </div>
                    `;
                    break;
                case 'color':
                    fieldInput = `
                        <input type="color" 
                               class="ddb-form-control ddb-repeater-field" 
                               data-field="${subFieldName}"
                               data-index="${index}"
                               data-parent="${fieldName}"
                               value="${this.escapeHtml(subFieldValue)}">
                    `;
                    break;
                case 'textarea':
                    fieldInput = `
                        <textarea class="ddb-form-control ddb-repeater-field" 
                                  data-field="${subFieldName}"
                                  data-index="${index}"
                                  data-parent="${fieldName}"
                                  rows="3">${this.escapeHtml(subFieldValue)}</textarea>
                    `;
                    break;
                default:
                    fieldInput = `
                        <input type="text" 
                               class="ddb-form-control ddb-repeater-field" 
                               data-field="${subFieldName}"
                               data-index="${index}"
                               data-parent="${fieldName}"
                               value="${this.escapeHtml(subFieldValue)}">
                    `;
            }

            fieldsHtml += `
                <div class="ddb-form-group">
                    <label class="ddb-form-label">${this.escapeHtml(subField.label)}</label>
                    ${fieldInput}
                </div>
            `;
        });

        return `
            <div class="ddb-repeater-item" data-index="${index}">
                <div class="ddb-repeater-header">
                    <span>Item ${index + 1}</span>
                    <button type="button" class="ddb-btn-danger" onclick="window.builder.removeRepeaterItem('${fieldName}', ${index})">
                        ➖ Remove
                    </button>
                </div>
                <div class="ddb-repeater-fields">
                    ${fieldsHtml}
                </div>
            </div>
        `;
    }

    addRepeaterItem(fieldName, fieldId) {
        const component = this.currentComponent; // Use stored component data
        if (!component) return;

        const componentFields = component.fields || component.settings || {};
        if (!componentFields[fieldName]) return;

        const field = componentFields[fieldName];
        const container = document.getElementById(`${fieldId}-items`);
        const currentItems = container.querySelectorAll('.ddb-repeater-item');
        const newIndex = currentItems.length;

        // Create new item with default values
        const newItem = {};
        Object.entries(field.fields || {}).forEach(([key, subField]) => {
            newItem[key] = subField.default || '';
        });

        const itemHtml = this.generateRepeaterItem(fieldName, field, newItem, newIndex);
        container.insertAdjacentHTML('beforeend', itemHtml);

        // Add event listeners to new fields
        this.setupRepeaterFieldListeners(fieldName);
    }

    removeRepeaterItem(fieldName, index) {
        const container = document.querySelector(`[data-field-name="${fieldName}"] .ddb-repeater-items`);
        const item = container.querySelector(`[data-index="${index}"]`);
        if (item) {
            item.remove();
            this.updateRepeaterIndices(fieldName);
        }
    }

    updateRepeaterIndices(fieldName) {
        const container = document.querySelector(`[data-field-name="${fieldName}"] .ddb-repeater-items`);
        const items = container.querySelectorAll('.ddb-repeater-item');

        items.forEach((item, newIndex) => {
            item.dataset.index = newIndex;
            item.querySelector('.ddb-repeater-header span').textContent = `Item ${newIndex + 1}`;

            const fields = item.querySelectorAll('.ddb-repeater-field');
            fields.forEach(field => {
                field.dataset.index = newIndex;
            });

            const removeBtn = item.querySelector('.ddb-btn-danger');
            removeBtn.setAttribute('onclick', `window.builder.removeRepeaterItem('${fieldName}', ${newIndex})`);
        });

        this.updateRepeaterValue(fieldName);
    }

    setupRepeaterFieldListeners(fieldName) {
        const container = document.querySelector(`[data-field-name="${fieldName}"]`);
        const fields = container.querySelectorAll('.ddb-repeater-field');

        fields.forEach(field => {
            field.addEventListener('input', () => {
                this.updateRepeaterValue(fieldName);
            });
        });
    }

    updateRepeaterValue(fieldName) {
        const container = document.querySelector(`[data-field-name="${fieldName}"]`);
        const hiddenInput = container.querySelector(`input[name="${fieldName}"]`);
        const items = container.querySelectorAll('.ddb-repeater-item');

        const data = Array.from(items).map(item => {
            const itemData = {};
            const fields = item.querySelectorAll('.ddb-repeater-field');

            fields.forEach(field => {
                const fieldName = field.dataset.field;
                itemData[fieldName] = field.value;
            });

            return itemData;
        });

        hiddenInput.value = JSON.stringify(data);
    }

    saveComponentSettings() {
        const formData = new FormData(this.settingsForm);
        const settings = {};

        // Debug: log all form entries
        console.log('Form entries:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
            settings[key] = value;
        }

        console.log('Settings to save:', settings);

        // Validate that we have at least some settings
        if (Object.keys(settings).length === 0) {
            this.showNotification('No settings to save', 'warning');
            return;
        }

        // Validate component ID
        if (!this.currentComponentId) {
            this.showNotification('No component selected', 'error');
            return;
        }

        const data = new FormData();
        data.append('action', 'update_component');
        data.append('component_id', this.currentComponentId);
        data.append('settings', JSON.stringify(settings));

        console.log('Sending AJAX request with component ID:', this.currentComponentId);

        fetch('', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: data
        })
            .then(response => response.json())
            .then(result => {
                console.log('Update response:', result);
                if (result.success) {
                    this.closeSettings();
                    this.refreshCanvas();
                    this.showNotification('Component updated successfully', 'success');
                } else {
                    this.showNotification('Error updating component: ' + (result.error || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Error updating component', 'error');
            });
    }

    deleteComponent(componentElement) {
        const componentId = componentElement.dataset.componentId;

        if (!componentId) {
            this.showNotification('Component ID not found', 'error');
            return;
        }

        if (!confirm('Are you sure you want to delete this component?')) {
            return;
        }

        const data = new FormData();
        data.append('action', 'delete_component');
        data.append('component_id', componentId);

        fetch('', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: data
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    componentElement.remove();
                    this.showNotification('Component deleted successfully', 'success');
                } else {
                    this.showNotification('Error deleting component: ' + (result.error || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Error deleting component', 'error');
            });
    }

    reorderComponents() {
        const components = Array.from(this.canvasContent.querySelectorAll('.ddb-component'));
        const componentIds = components.map(component => component.dataset.componentId);

        const data = new FormData();
        data.append('action', 'reorder_components');
        data.append('component_ids', JSON.stringify(componentIds));

        fetch('', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: data
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    this.showNotification('Components reordered', 'success');
                } else {
                    this.showNotification('Error reordering components: ' + (result.error || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Error reordering components', 'error');
            });
    }

    selectComponent(componentElement) {
        // Deselect all components
        this.deselectComponent();

        // Select this component
        componentElement.classList.add('selected');
        this.currentSelectedComponent = componentElement;
    }

    deselectComponent() {
        const selected = document.querySelectorAll('.ddb-component.selected');
        selected.forEach(component => {
            component.classList.remove('selected');
        });
        this.currentSelectedComponent = null;
    }

    closeSettings() {
        this.settingsPanel.classList.remove('active');
        this.currentComponentId = null;
    }

    refreshCanvas() {
        // Reload the page to show updated components
        location.reload();
    }

    previewPage() {
        // Open page in new tab for preview
        const pageUrl = window.location.href.replace('/dashboard/page-builder.php', '/page.php').replace('id=', 'page_id=');
        window.open(pageUrl, '_blank');
    }

    savePage() {
        this.showNotification('Page saved successfully', 'success');
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white z-50 ${type === 'success' ? 'bg-green-500' :
            type === 'error' ? 'bg-red-500' :
                type === 'warning' ? 'bg-yellow-500' :
                    'bg-blue-500'
            }`;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function (m) { return map[m]; });
    }

    // Image handling methods
    handleImageUpload(event, fieldId) {
        const file = event.target.files[0];
        if (!file) return;

        // Check file size (limit to 5MB)
        if (file.size > 5 * 1024 * 1024) {
            this.showNotification('Image file size must be less than 5MB', 'error');
            return;
        }

        // Check file type
        if (!file.type.startsWith('image/')) {
            this.showNotification('Please select a valid image file', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'upload_image');
        formData.append('image', file);

        fetch('', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const input = document.getElementById(fieldId);
                if (input) {
                    input.value = result.url;
                    this.updateImagePreview(fieldId, result.url);
                    
                    // Trigger change event for repeater fields
                    if (input.classList.contains('ddb-repeater-field')) {
                        this.updateRepeaterValue(input.dataset.parent);
                    }
                    
                    this.showNotification('Image uploaded successfully', 'success');
                }
            } else {
                this.showNotification('Error uploading image: ' + (result.error || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('Error uploading image', 'error');
        });
    }

    updateImagePreview(fieldId, imageUrl) {
        const preview = document.getElementById(fieldId + '-preview');
        const dropZone = document.getElementById(fieldId + '-dropzone');
        const img = preview ? preview.querySelector('img') : null;
        
        if (preview && img) {
            if (imageUrl && imageUrl.trim()) {
                // Handle emoji URLs specially
                if (imageUrl.startsWith('emoji:')) {
                    const emoji = imageUrl.substring(6);
                    img.src = `data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><text y="50" font-size="50">${emoji}</text></svg>`;
                } else if (imageUrl.startsWith('data:image/svg+xml')) {
                    img.src = imageUrl;
                } else {
                    img.src = imageUrl;
                }
                preview.style.display = 'block';
                if (dropZone) dropZone.style.display = 'none';
            } else {
                preview.style.display = 'none';
                if (dropZone) dropZone.style.display = 'block';
            }
        }
    }

    removeImage(fieldId) {
        const input = document.getElementById(fieldId);
        const dropZone = document.getElementById(fieldId + '-dropzone');
        const preview = document.getElementById(fieldId + '-preview');
        
        if (input) {
            input.value = '';
            this.updateImagePreview(fieldId, '');
            
            // Show drop zone and hide preview
            if (dropZone) dropZone.style.display = 'block';
            if (preview) preview.style.display = 'none';
            
            // Trigger change event for repeater fields
            if (input.classList.contains('ddb-repeater-field')) {
                this.updateRepeaterValue(input.dataset.parent);
            }
        }
    }

    // Drag and drop methods
    handleDragOver(event) {
        event.preventDefault();
        event.stopPropagation();
        event.currentTarget.classList.add('dragover');
    }

    handleDragLeave(event) {
        event.preventDefault();
        event.stopPropagation();
        event.currentTarget.classList.remove('dragover');
    }

    handleImageDrop(event, fieldId) {
        event.preventDefault();
        event.stopPropagation();
        event.currentTarget.classList.remove('dragover');

        // Check for files first
        const files = event.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            if (!file.type.startsWith('image/')) {
                this.showNotification('Please drop an image file', 'error');
                return;
            }

            // Simulate file input change event
            const fileInput = document.getElementById(fieldId + '-file');
            if (fileInput) {
                // Create a new FileList-like object
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
                
                // Trigger the upload
                this.handleImageUpload({ target: fileInput }, fieldId);
            }
            return;
        }

        // Check for URLs/text if no files
        const droppedText = event.dataTransfer.getData('text/plain');
        if (droppedText) {
            // Check if it's a valid URL
            try {
                const url = new URL(droppedText);
                
                // Check if it looks like an image URL
                const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg', '.bmp', '.ico'];
                const hasImageExtension = imageExtensions.some(ext => 
                    url.pathname.toLowerCase().includes(ext)
                );
                
                // Allow URLs that have image extensions or are from common image hosts
                const imageHosts = ['imgur.com', 'cloudinary.com', 'amazonaws.com', 'googleusercontent.com', 'unsplash.com', 'pexels.com'];
                const isImageHost = imageHosts.some(host => url.hostname.includes(host));
                
                if (hasImageExtension || isImageHost || url.protocol === 'data:') {
                    // Set the URL directly
                    const input = document.getElementById(fieldId);
                    if (input) {
                        input.value = droppedText;
                        this.updateImagePreview(fieldId, droppedText);
                        
                        // Trigger change event for repeater fields
                        if (input.classList.contains('ddb-repeater-field')) {
                            this.updateRepeaterValue(input.dataset.parent);
                        }
                        
                        this.showNotification('Image URL added successfully', 'success');
                    }
                } else {
                    this.showNotification('Please drop a valid image URL or image file', 'error');
                }
            } catch (e) {
                // Not a valid URL, check if it might be an emoji
                if (droppedText.length <= 10 && /[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/u.test(droppedText)) {
                    // It's an emoji
                    const input = document.getElementById(fieldId);
                    if (input) {
                        input.value = `emoji:${droppedText}`;
                        this.updateImagePreview(fieldId, `emoji:${droppedText}`);
                        
                        // Trigger change event for repeater fields
                        if (input.classList.contains('ddb-repeater-field')) {
                            this.updateRepeaterValue(input.dataset.parent);
                        }
                        
                        this.showNotification('Emoji added successfully', 'success');
                    }
                } else {
                    this.showNotification('Please drop a valid image URL, image file, or emoji', 'error');
                }
            }
        } else {
            this.showNotification('Please drop an image file or image URL', 'error');
        }
    }

    // Emoji picker methods
    openEmojiPicker(fieldId) {
        if (this.emojiPicker) {
            this.emojiPicker.remove();
        }

        const emojis = [
            // Stats and numbers
            '📊', '📈', '📉', '💰', '🎯', '🏆', '🎉', '�', '⭐', '🌟',
            
            // Features and technology
            '🚀', '💡', '🔥', '💎', '⚡', '🎖️', '🏅', '🔔', '📢', '👑',
            '🌐', '💻', '📱', '⚙️', '🔧', '🛠️', '📝', '📋', '💼', '�',
            
            // Business and services
            '🖥️', '📺', '�', '📸', '🔍', '�', '�', '🌍', '🎪', '🏪',
            '🏢', '�', '🏦', '�️', '�', '📚', '📖', '📑', '�', '�',
            
            // People and community
            '👥', '👤', '👨‍💼', '�‍💼', '👨‍💻', '👩‍💻', '🤝', '�', '✨', '🎭',
            
            // Security and reliability
            '�', '�️', '✅', '☑️', '✔️', '�', '�️', '�', '⚖️', '🎚️',
            
            // Communication and support
            '�', '📧', '�', '🗨️', '�', '📬', '📮', '📪', '📫', '🎤',
            
            // Success and growth
            '🌱', '🌳', '🌿', '�', '�', '�', '�', '🎊', '🥇', '�'
        ];

        const picker = document.createElement('div');
        picker.className = 'ddb-emoji-picker';
        picker.innerHTML = `
            <div class="ddb-emoji-picker-content">
                <div class="ddb-emoji-picker-header">
                    <span>Select Emoji</span>
                    <button type="button" onclick="this.parentElement.parentElement.parentElement.remove()" class="ddb-btn-close">×</button>
                </div>
                <div class="ddb-emoji-grid">
                    ${emojis.map(emoji => `
                        <button type="button" class="ddb-emoji-btn" onclick="window.builder.selectEmoji('${fieldId}', '${emoji}')">
                            ${emoji}
                        </button>
                    `).join('')}
                </div>
            </div>
        `;

        document.body.appendChild(picker);
        this.emojiPicker = picker;
    }

    selectEmoji(fieldId, emoji) {
        const input = document.getElementById(fieldId);
        if (input) {
            // For image fields, we'll treat emojis as special image URLs
            input.value = `emoji:${emoji}`;
            this.updateImagePreview(fieldId, `data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><text y="50" font-size="50">${emoji}</text></svg>`);
            
            // Trigger change event for repeater fields
            if (input.classList.contains('ddb-repeater-field')) {
                this.updateRepeaterValue(input.dataset.parent);
            }
        }

        if (this.emojiPicker) {
            this.emojiPicker.remove();
            this.emojiPicker = null;
        }
    }
}

// Initialize builder when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.builder = new DragDropBuilder();
});
