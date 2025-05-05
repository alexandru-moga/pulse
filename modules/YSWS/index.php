<?php
require_once '../../core/init.php';

function getProjects() {
    global $db;
    return Project::getAll();
}

function getProjectDetails($id) {
    return Project::getById($id);
}

function applyForProject($projectId, $userId) {
    global $db;
    
    $db->insert('applications', [
        'project_id' => $projectId,
        'user_id' => $userId,
        'status' => 'pending',
        'applied_at' => date('Y-m-d H:i:s')
    ]);
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action === 'apply' && isLoggedIn() && isset($_GET['id'])) {
        applyForProject($_GET['id'], $_SESSION['user_id']);
        redirect(SITE_URL . '/ysws?success=application_submitted');
    }
}

include CUSTOM_PATH . '/templates/ysws/projects.php';