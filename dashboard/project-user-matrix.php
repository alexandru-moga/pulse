<?php
require_once __DIR__ . '/../core/init.php';
include '../components/layout/header.php';

$users = $db->query("SELECT id, first_name, last_name FROM users ORDER BY last_name, first_name")->fetchAll(PDO::FETCH_ASSOC);
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
    'not_participating' => 'Not Participating',
    'not_sent' => 'Not Sent'
];

$statusClasses = [
    'accepted_pizza' => 'status-accepted-pizza',
    'accepted' => 'status-accepted',
    'waiting' => 'status-waiting',
    'rejected' => 'status-rejected',
    'not_participating' => 'status-not-participating',
    'not_sent' => 'status-not-sent'
];
?>
<link rel="stylesheet" href="/css/project-user-matrix.css">
<div class="project-matrix-section">
    <h2>Project Assignment Matrix</h2>
    <div class="table-responsive">
        <table class="dashboard-table project-matrix-table">
            <thead>
                <tr>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <?php foreach ($projects as $project): ?>
                        <th><?= htmlspecialchars($project['title']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
<?php foreach ($users as $user): ?>
    <tr>
        <td><?= htmlspecialchars($user['last_name']) ?></td>
        <td><?= htmlspecialchars($user['first_name']) ?></td>
        <?php foreach ($projects as $project):
            $cell = $assignments[$user['id']][$project['id']] ?? ['status' => 'not_sent', 'pizza_grant' => 'none'];
            $virtualStatus = ($cell['status'] === 'accepted' && $cell['pizza_grant'] === 'received') ? 'accepted_pizza' : $cell['status'];
            $badgeClass = $statusClasses[$virtualStatus] ?? 'status-not-sent';
            $badgeText = $statusOptions[$virtualStatus] ?? ucfirst($virtualStatus);
        ?>
            <td>
                <span class="status-badge <?= $badgeClass ?>"
                      tabindex="0"
                      data-user="<?= $user['id'] ?>"
                      data-project="<?= $project['id'] ?>"
                      data-status="<?= $virtualStatus ?>"
                      onclick="showStatusMenu(this)">
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

<script>
const statusOptions = [
  {value: 'accepted_pizza', label: 'Accepted+Pizza', class: 'status-accepted-pizza'},
  {value: 'accepted', label: 'Accepted', class: 'status-accepted'},
  {value: 'waiting', label: 'Waiting', class: 'status-waiting'},
  {value: 'rejected', label: 'Rejected', class: 'status-rejected'},
  {value: 'not_participating', label: 'Not Participating', class: 'status-not-participating'},
  {value: 'not_sent', label: 'Not Sent', class: 'status-not-sent'}
];
const statusClasses = {
  'accepted_pizza': 'status-accepted-pizza',
  'accepted': 'status-accepted',
  'waiting': 'status-waiting',
  'rejected': 'status-rejected',
  'not_participating': 'status-not-participating',
  'not_sent': 'status-not-sent'
};

function showStatusMenu(badge) {
  document.querySelectorAll('.status-menu').forEach(m => m.remove());
  const rect = badge.getBoundingClientRect();
  const menu = document.createElement('div');
  menu.className = 'status-menu';
  menu.style.position = 'fixed';
  menu.style.left = (rect.left) + 'px';
  menu.style.top = (rect.bottom + 4) + 'px';
  menu.style.zIndex = 10000;
  menu.style.background = 'var(--sheet, #404040)';
  menu.style.border = '1px solid #222';
  menu.style.borderRadius = '8px';
  menu.style.boxShadow = '0 2px 12px rgba(0,0,0,0.16)';
  menu.style.padding = '0.4em 0.6em';
  menu.style.minWidth = '130px';

  statusOptions.forEach(opt => {
    const item = document.createElement('div');
    item.className = 'status-badge ' + opt.class;
    item.innerText = opt.label;
    item.style.margin = '0.2em 0';
    item.style.cursor = 'pointer';
    item.onclick = () => {
      updateStatus(
        badge.dataset.user,
        badge.dataset.project,
        opt.value,
        badge
      );
      menu.remove();
    };
    menu.appendChild(item);
  });
  document.body.appendChild(menu);
  setTimeout(() => {
    document.addEventListener('mousedown', function handler(e) {
      if (!menu.contains(e.target)) {
        menu.remove();
        document.removeEventListener('mousedown', handler);
      }
    });
  }, 10);
}

function updateStatus(userId, projectId, newStatus, badge) {
  let status = newStatus === 'accepted_pizza' ? 'accepted' : newStatus;
  let pizza_grant = newStatus === 'accepted_pizza' ? 'received' : 'none';
  fetch('update-assignment-status.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `user_id=${userId}&project_id=${projectId}&status=${status}&pizza_grant=${pizza_grant}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      let badgeText = statusOptions.find(o => o.value === newStatus)?.label || newStatus;
      let badgeClass = 'status-badge ' + (statusClasses[newStatus] || 'status-not-sent');
      badge.className = badgeClass;
      badge.dataset.status = newStatus;
      badge.innerText = badgeText;
    } else {
      alert('Failed to update status.');
    }
  });
}
</script>

<?php include '../components/layout/footer.php'; ?>
<?php include '../components/effects/grid.php'; ?>
<?php include '../components/effects/mouse.php'; ?>