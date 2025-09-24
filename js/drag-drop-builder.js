/**
 * Drag & Drop Website Builder JavaScript
 */

class DragDropBuilder {
    constructor() {
        this.currentSelectedComponent = null;
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

        document.getElementById('settings-title').textContent = `Edit ${component.name}`;

        // Clear previous fields
        this.settingsFields.innerHTML = '';

        // Generate form fields
        Object.entries(component.settings).forEach(([key, field]) => {
            const value = settings[key] || field.default || '';
            const fieldHTML = this.generateFormField(key, field, value);
            this.settingsFields.innerHTML += fieldHTML;
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
                inputHTML = `<input type="url" id="${fieldId}" name="${name}" value="${this.escapeHtml(value)}" placeholder="Enter image URL" class="ddb-form-control">`;
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
}

// Initialize builder when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.builder = new DragDropBuilder();
});
