<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser;

$pageTitle = 'Diploma Templates';
include __DIR__ . '/components/dashboard-header.php';

$success = $error = null;

// Handle template creation/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_template'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $template_type = $_POST['template_type'];
        $related_id = $_POST['related_id'] ?: null;
        $certificate_text = $_POST['certificate_text'];
        $signature_name = $_POST['signature_name'];
        $signature_title = $_POST['signature_title'];
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        
        // Handle background image upload
        $background_image = null;
        if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/diploma-templates/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['background_image']['name'], PATHINFO_EXTENSION);
            $filename = 'template_' . time() . '_' . uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['background_image']['tmp_name'], $file_path)) {
                $background_image = 'uploads/diploma-templates/' . $filename;
            }
        }
        
        try {
            $stmt = $db->prepare("
                INSERT INTO diploma_templates 
                (title, description, template_type, related_id, background_image, certificate_text, signature_name, signature_title, enabled, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title, $description, $template_type, $related_id, $background_image, 
                $certificate_text, $signature_name, $signature_title, $enabled, $currentUser['id']
            ]);
            $success = "Diploma template created successfully!";
        } catch (PDOException $e) {
            $error = "Error creating template: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_template'])) {
        $id = $_POST['template_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $template_type = $_POST['template_type'];
        $related_id = $_POST['related_id'] ?: null;
        $certificate_text = $_POST['certificate_text'];
        $signature_name = $_POST['signature_name'];
        $signature_title = $_POST['signature_title'];
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        
        // Handle background image upload
        $background_image = $_POST['current_background_image'];
        if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/diploma-templates/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['background_image']['name'], PATHINFO_EXTENSION);
            $filename = 'template_' . time() . '_' . uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['background_image']['tmp_name'], $file_path)) {
                // Delete old background if exists
                if ($background_image && file_exists(__DIR__ . '/../' . $background_image)) {
                    unlink(__DIR__ . '/../' . $background_image);
                }
                $background_image = 'uploads/diploma-templates/' . $filename;
            }
        }
        
        try {
            $stmt = $db->prepare("
                UPDATE diploma_templates 
                SET title = ?, description = ?, template_type = ?, related_id = ?, background_image = ?, 
                    certificate_text = ?, signature_name = ?, signature_title = ?, enabled = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $title, $description, $template_type, $related_id, $background_image,
                $certificate_text, $signature_name, $signature_title, $enabled, $id
            ]);
            $success = "Diploma template updated successfully!";
        } catch (PDOException $e) {
            $error = "Error updating template: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_template'])) {
        $id = $_POST['template_id'];
        
        try {
            // Get background image path
            $stmt = $db->prepare("SELECT background_image FROM diploma_templates WHERE id = ?");
            $stmt->execute([$id]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Delete template
            $stmt = $db->prepare("DELETE FROM diploma_templates WHERE id = ?");
            $stmt->execute([$id]);
            
            // Delete background image file
            if ($template && $template['background_image']) {
                $file_path = __DIR__ . '/../' . $template['background_image'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            $success = "Diploma template deleted successfully!";
        } catch (PDOException $e) {
            $error = "Error deleting template: " . $e->getMessage();
        }
    }
}

// Get all templates
$templates = $db->query("
    SELECT dt.*, 
           CASE 
               WHEN dt.template_type = 'project' THEN p.title
               WHEN dt.template_type = 'event' THEN e.title
               ELSE NULL
           END as related_title,
           u.first_name, u.last_name
    FROM diploma_templates dt
    LEFT JOIN projects p ON dt.template_type = 'project' AND dt.related_id = p.id
    LEFT JOIN events e ON dt.template_type = 'event' AND dt.related_id = e.id
    LEFT JOIN users u ON dt.created_by = u.id
    ORDER BY dt.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Get projects and events for dropdowns
$projects = $db->query("SELECT id, title FROM projects ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);
$events = $db->query("SELECT id, title FROM events ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Diploma Templates</h2>
                <p class="text-gray-600 mt-1">Manage diploma templates for projects and events</p>
            </div>
            <button onclick="showCreateModal()" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Template
            </button>
        </div>
    </div>

    <!-- Notifications -->
    <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700"><?= htmlspecialchars($success) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Templates List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Related</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($templates as $template): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($template['title']) ?></div>
                            <?php if ($template['description']): ?>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($template['description']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?= $template['template_type'] === 'project' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' ?>">
                                <?= ucfirst($template['template_type']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $template['related_title'] ? htmlspecialchars($template['related_title']) : 'All' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?= $template['enabled'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                <?= $template['enabled'] ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= htmlspecialchars($template['first_name'] . ' ' . $template['last_name']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick='editTemplate(<?= json_encode($template) ?>)' 
                                class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                <input type="hidden" name="template_id" value="<?= $template['id'] ?>">
                                <button type="submit" name="delete_template" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                
                <?php if (empty($templates)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No diploma templates yet</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="templateModal" class="hidden fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" id="template_id" name="template_id">
                <input type="hidden" id="current_background_image" name="current_background_image">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modalTitle">Create Diploma Template</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="title" id="title" required 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="2"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Template Type</label>
                                <select name="template_type" id="template_type" required onchange="updateRelatedOptions()"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="project">Project</option>
                                    <option value="event">Event</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Related (Optional)</label>
                                <select name="related_id" id="related_id"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">All</option>
                                    <!-- Options populated by JS -->
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Background Image (Optional)</label>
                            <input type="file" name="background_image" id="background_image" accept="image/*"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="text-xs text-gray-500 mt-1">Upload a background image for the diploma</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Certificate Text</label>
                            <textarea name="certificate_text" id="certificate_text" rows="3" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="For participating in..."></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Signature Name</label>
                                <input type="text" name="signature_name" id="signature_name"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Signature Title</label>
                                <input type="text" name="signature_title" id="signature_title"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="enabled" id="enabled" checked
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="enabled" class="ml-2 block text-sm text-gray-900">Enabled</label>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" id="submitBtn" name="create_template"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Create Template
                    </button>
                    <button type="button" onclick="hideModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const projects = <?= json_encode($projects) ?>;
const events = <?= json_encode($events) ?>;

function showCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create Diploma Template';
    document.getElementById('submitBtn').name = 'create_template';
    document.getElementById('submitBtn').textContent = 'Create Template';
    document.getElementById('template_id').value = '';
    document.getElementById('title').value = '';
    document.getElementById('description').value = '';
    document.getElementById('template_type').value = 'project';
    document.getElementById('certificate_text').value = '';
    document.getElementById('signature_name').value = '';
    document.getElementById('signature_title').value = '';
    document.getElementById('enabled').checked = true;
    document.getElementById('current_background_image').value = '';
    updateRelatedOptions();
    document.getElementById('templateModal').classList.remove('hidden');
}

function editTemplate(template) {
    document.getElementById('modalTitle').textContent = 'Edit Diploma Template';
    document.getElementById('submitBtn').name = 'update_template';
    document.getElementById('submitBtn').textContent = 'Update Template';
    document.getElementById('template_id').value = template.id;
    document.getElementById('title').value = template.title;
    document.getElementById('description').value = template.description || '';
    document.getElementById('template_type').value = template.template_type;
    document.getElementById('certificate_text').value = template.certificate_text || '';
    document.getElementById('signature_name').value = template.signature_name || '';
    document.getElementById('signature_title').value = template.signature_title || '';
    document.getElementById('enabled').checked = template.enabled == 1;
    document.getElementById('current_background_image').value = template.background_image || '';
    updateRelatedOptions();
    document.getElementById('related_id').value = template.related_id || '';
    document.getElementById('templateModal').classList.remove('hidden');
}

function hideModal() {
    document.getElementById('templateModal').classList.add('hidden');
}

function updateRelatedOptions() {
    const type = document.getElementById('template_type').value;
    const relatedSelect = document.getElementById('related_id');
    const items = type === 'project' ? projects : events;
    
    relatedSelect.innerHTML = '<option value="">All</option>';
    items.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = item.title;
        relatedSelect.appendChild(option);
    });
}

// Initialize on load
updateRelatedOptions();
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
