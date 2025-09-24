/**
 * Simplified Drag & Drop Builder for Page Settings
 */

class PageSettingsBuilder {
    constructor() {
        this.init();
    }

    init() {
        this.setupDragAndDrop();
        this.setupClickHandlers();
    }

    setupDragAndDrop() {
        const componentItems = document.querySelectorAll('.component-item');
        const dropZones = document.querySelectorAll('.drop-zone');

        // Make components draggable
        componentItems.forEach(item => {
            item.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', item.dataset.componentType);
                item.classList.add('dragging');
            });

            item.addEventListener('dragend', () => {
                item.classList.remove('dragging');
            });
        });

        // Setup drop zones
        dropZones.forEach(zone => {
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('dragover');
            });

            zone.addEventListener('dragleave', () => {
                zone.classList.remove('dragover');
            });

            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('dragover');

                const componentType = e.dataTransfer.getData('text/plain');
                const position = zone.dataset.position;

                if (componentType) {
                    this.addComponent(componentType, position);
                }
            });
        });
    }

    setupClickHandlers() {
        const componentItems = document.querySelectorAll('.component-item');

        componentItems.forEach(item => {
            item.addEventListener('click', () => {
                const componentType = item.dataset.componentType;
                this.addComponent(componentType);
            });
        });
    }

    addComponent(componentType, position = null) {
        const pageCanvas = document.getElementById('pageCanvas');
        const pageId = pageCanvas ? pageCanvas.dataset.pageId : null;

        if (!pageId) {
            this.showNotification('Page ID not found', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'add_component');
        formData.append('component_type', componentType);
        if (position !== null) {
            formData.append('position', position);
        }

        const baseUrl = window.location.origin;
        fetch(`${baseUrl}/dashboard/page-builder.php?id=${pageId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotification('Component added successfully!', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    this.showNotification('Error: ' + (data.error || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Network error occurred', 'error');
            });
    }

    showNotification(message, type = 'info') {
        // Remove existing notifications
        const existing = document.querySelectorAll('.page-builder-notification');
        existing.forEach(n => n.remove());

        const notification = document.createElement('div');
        notification.className = `page-builder-notification fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 transform translate-x-0 transition-all duration-300 shadow-lg ${type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                    type === 'warning' ? 'bg-yellow-500' :
                        'bg-blue-500'
            }`;
        notification.innerHTML = `
            <div class="flex items-center space-x-2">
                <span>${type === 'success' ? '✅' : type === 'error' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️'}</span>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 4 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 4000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('pageCanvas')) {
        window.pageSettingsBuilder = new PageSettingsBuilder();
    }
});
