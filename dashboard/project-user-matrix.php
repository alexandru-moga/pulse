<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = 'Project User Matrix';
include __DIR__ . '/components/dashboard-header.php';

$users = $db->query("SELECT id, first_name, last_name FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$projects = $db->query("SELECT id, title FROM projects ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

$assignments = [];
$stmt = $db->query("SELECT user_id, project_id, status, pizza_grant FROM project_assignments");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $assignments[$row['user_id']][$row['project_id']] = [
        'status' => $row['status'],
        'pizza_grant' => $row['pizza_grant']
    ];
}

$statusOptions = [
    'accepted_pizza' => 'Accepted+Pizza',
    'accepted' => 'Accepted',
    'waiting' => 'Waiting',
    'rejected' => 'Rejected',
    'not_sent' => 'Not Sent',
    'not_sent' => 'Not Sent'
];

$statusClasses = [
    'accepted_pizza' => 'bg-purple-100 text-purple-800',
    'accepted' => 'bg-green-100 text-green-800',
    'waiting' => 'bg-yellow-100 text-yellow-800',
    'rejected' => 'bg-red-100 text-red-800',
    'not_sent' => 'bg-gray-100 text-gray-800',
    'not_sent' => 'bg-gray-100 text-gray-600'
];
?>

<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Project User Matrix</h2>
                <p class="text-gray-600 mt-1">View assignment status for all users across all projects</p>
            </div>
            <a href="<?= $settings['site_url'] ?>/dashboard/projects-management.php" 
               class="text-primary hover:text-red-600 text-sm font-medium">
                ← Back to Projects
            </a>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Assignment Matrix</h3>
                    <p class="text-sm text-gray-500 mt-1">Scroll horizontally to view all projects</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="toggleAllUsers()" class="bg-blue-100 text-blue-800 px-3 py-1 rounded-md text-sm font-medium hover:bg-blue-200">
                        Toggle Users
                    </button>
                    <button onclick="toggleAllProjects()" class="bg-green-100 text-green-800 px-3 py-1 rounded-md text-sm font-medium hover:bg-green-200">
                        Toggle Projects
                    </button>
                    <button onclick="toggleFreezeHeaders()" id="freezeBtn" class="bg-purple-100 text-purple-800 px-3 py-1 rounded-md text-sm font-medium hover:bg-purple-200">
                        Freeze Headers
                    </button>
                </div>
            </div>
        </div>
        
        <div class="overflow-hidden" id="matrixContainer">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="matrixTable">
                    <thead class="bg-gray-50" id="matrixHeader">
                        <tr>
                            <th class="sticky left-0 bg-gray-50 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200 z-10">
                                <div class="flex items-center">
                                    <span>User</span>
                                    <button onclick="selectAllUsers()" class="ml-2 text-xs text-blue-600 hover:text-blue-800">Select All</button>
                                </div>
                            </th>
                            <?php foreach ($projects as $index => $project): ?>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-32 project-header" data-project-id="<?= $project['id'] ?>">
                                    <div class="flex flex-col items-center">
                                        <input type="checkbox" class="project-checkbox mb-2" checked onchange="toggleProject(<?= $project['id'] ?>)">
                                        <div class="truncate" title="<?= htmlspecialchars($project['title']) ?>">
                                            <?= htmlspecialchars(mb_strimwidth($project['title'], 0, 15, '...')) ?>
                                        </div>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50 user-row" data-user-id="<?= $user['id'] ?>">
                            <td class="sticky left-0 bg-white px-6 py-4 whitespace-nowrap border-r border-gray-200 z-10">
                                <div class="flex items-center">
                                    <input type="checkbox" class="user-checkbox mr-3" checked onchange="toggleUser(<?= $user['id'] ?>)">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                    </div>
                                </div>
                            </td>
                            <?php foreach ($projects as $project):
                                $cell = $assignments[$user['id']][$project['id']] ?? ['status' => 'not_sent', 'pizza_grant' => 'none'];
                                $virtualStatus = ($cell['status'] === 'accepted' && $cell['pizza_grant'] === 'received') ? 'accepted_pizza' : $cell['status'];
                                $badgeClass = $statusClasses[$virtualStatus] ?? 'bg-gray-100 text-gray-600';
                                $badgeText = $statusOptions[$virtualStatus] ?? ucfirst($virtualStatus);
                            ?>
                                <td class="px-4 py-4 text-center project-cell" data-project-id="<?= $project['id'] ?>">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer <?= $badgeClass ?>"
                                          tabindex="0"
                                          data-user="<?= $user['id'] ?>"
                                          data-project="<?= $project['id'] ?>"
                                          data-status="<?= $virtualStatus ?>"
                                          onclick="showStatusMenu(this)"
                                          title="Click to change status">
                                    <?= htmlspecialchars($badgeText) ?>
                                    </span>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div id="statusModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Change Status</h3>
                <div class="space-y-2" id="statusOptions">
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button onclick="closeStatusModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    const statusOptions = {
        'accepted_pizza': { label: 'Accepted + Pizza', class: 'bg-purple-100 text-purple-800' },
        'accepted': { label: 'Accepted', class: 'bg-green-100 text-green-800' },
        'waiting': { label: 'Waiting', class: 'bg-yellow-100 text-yellow-800' },
        'rejected': { label: 'Rejected', class: 'bg-red-100 text-red-800' },
        'not_sent': { label: 'Not Sent', class: 'bg-gray-100 text-gray-800' },
        'not_sent': { label: 'Not Sent', class: 'bg-gray-100 text-gray-600' }
    };

    let currentUserId, currentProjectId, currentBadge;

    function showStatusMenu(badge) {
        currentUserId = badge.dataset.user;
        currentProjectId = badge.dataset.project;
        currentBadge = badge;
        
        const modal = document.getElementById('statusModal');
        const optionsContainer = document.getElementById('statusOptions');
        
        optionsContainer.innerHTML = '';
        
        Object.entries(statusOptions).forEach(([value, option]) => {
            const button = document.createElement('button');
            button.className = `w-full text-left px-3 py-2 rounded-md ${option.class} hover:opacity-80 transition-opacity`;
            button.textContent = option.label;
            button.onclick = () => updateStatus(value);
            optionsContainer.appendChild(button);
        });
        
        modal.classList.remove('hidden');
    }

    function closeStatusModal() {
        document.getElementById('statusModal').classList.add('hidden');
    }

    function updateStatus(newStatus) {
        const status = newStatus === 'accepted_pizza' ? 'accepted' : newStatus;
        const pizzaGrant = newStatus === 'accepted_pizza' ? 'received' : 'none';
        
        const formData = new FormData();
        formData.append('user_id', currentUserId);
        formData.append('project_id', currentProjectId);
        formData.append('status', status);
        formData.append('pizza_grant', pizzaGrant);
        
        fetch('<?= $settings['site_url'] ?>/dashboard/update-assignment-status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const option = statusOptions[newStatus];
                currentBadge.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer ${option.class}`;
                currentBadge.textContent = option.label;
                currentBadge.dataset.status = newStatus;
                closeStatusModal();
            } else {
                alert('Failed to update status: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update status. Please try again.');
        });
    }

    document.getElementById('statusModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeStatusModal();
        }
    });

    function toggleUser(userId) {
        const userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
        const checkbox = document.querySelector(`input.user-checkbox[onchange*="${userId}"]`);
        
        if (checkbox.checked) {
            userRow.style.display = '';
        } else {
            userRow.style.display = 'none';
        }
    }

    function toggleProject(projectId) {
        const projectCells = document.querySelectorAll(`td[data-project-id="${projectId}"], th[data-project-id="${projectId}"]`);
        const checkbox = document.querySelector(`input.project-checkbox[onchange*="${projectId}"]`);
        
        projectCells.forEach(cell => {
            if (checkbox.checked) {
                cell.style.display = '';
            } else {
                cell.style.display = 'none';
            }
        });
    }

    function toggleAllUsers() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = !allChecked;
            const userId = checkbox.getAttribute('onchange').match(/\d+/)[0];
            toggleUser(userId);
        });
    }

    function toggleAllProjects() {
        const checkboxes = document.querySelectorAll('.project-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = !allChecked;
            const projectId = checkbox.getAttribute('onchange').match(/\d+/)[0];
            toggleProject(projectId);
        });
    }

    function selectAllUsers() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
            const userId = checkbox.getAttribute('onchange').match(/\d+/)[0];
            toggleUser(userId);
        });
    }

    let headersFrozen = false;
    function toggleFreezeHeaders() {
        const container = document.getElementById('matrixContainer');
        const header = document.getElementById('matrixHeader');
        const btn = document.getElementById('freezeBtn');
        
        if (!headersFrozen) {
            container.style.maxHeight = '600px';
            container.style.overflowY = 'auto';
            header.style.position = 'sticky';
            header.style.top = '0';
            header.style.zIndex = '20';
            btn.textContent = 'Unfreeze Headers';
            btn.className = 'bg-red-100 text-red-800 px-3 py-1 rounded-md text-sm font-medium hover:bg-red-200';
            headersFrozen = true;
        } else {
            container.style.maxHeight = 'none';
            container.style.overflowY = 'visible';
            header.style.position = 'static';
            header.style.top = 'auto';
            header.style.zIndex = 'auto';
            btn.textContent = 'Freeze Headers';
            btn.className = 'bg-purple-100 text-purple-800 px-3 py-1 rounded-md text-sm font-medium hover:bg-purple-200';
            headersFrozen = false;
        }
    }
    </script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>