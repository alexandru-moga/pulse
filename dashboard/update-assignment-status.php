<?php
require_once __DIR__ . '/../core/init.php';

$user_id = (int)$_POST['user_id'];
$project_id = (int)$_POST['project_id'];
$status = $_POST['status'];
$pizza_grant = $_POST['pizza_grant'] ?? 'none';

$stmt = $db->prepare("SELECT id FROM project_assignments WHERE user_id = ? AND project_id = ?");
$stmt->execute([$user_id, $project_id]);
$exists = $stmt->fetchColumn();

if ($exists) {
    $stmt = $db->prepare("UPDATE project_assignments SET status = ?, pizza_grant = ? WHERE user_id = ? AND project_id = ?");
    $success = $stmt->execute([$status, $pizza_grant, $user_id, $project_id]);
} else {
    $stmt = $db->prepare("INSERT INTO project_assignments (user_id, project_id, status, pizza_grant) VALUES (?, ?, ?, ?)");
    $success = $stmt->execute([$user_id, $project_id, $status, $pizza_grant]);
}

echo json_encode(['success' => $success]);
