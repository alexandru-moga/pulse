<?php
require_once '../../core/init.php';

if (!isAdmin()) {
    redirect(SITE_URL . '/login?error=unauthorized');
}

function getMembers() {
    global $db;
    return $db->select("SELECT * FROM users ORDER BY created_at DESC");
}

function getMemberDetails($id) {
    return User::getById($id);
}

function createMember($data) {
    return User::create(
        $data['username'],
        $data['email'],
        $data['password'],
        $data['role'] ?? 'member'
    );
}

function updateMember($id, $data) {
    $user = User::getById($id);
    if ($user) {
        $user->update($data);
    }
}

function deleteMember($id) {
    global $db;
    $db->delete('users', 'id = ?', [$id]);
}

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'create' && isset($_POST['username'], $_POST['email'], $_POST['password'])) {
        createMember($_POST);
        redirect(SITE_URL . '/admin/members?success=member_created');
    }
    
    if ($action === 'update' && isset($_POST['id'])) {
        updateMember($_POST['id'], $_POST);
        redirect(SITE_URL . '/admin/members?success=member_updated');
    }
    
    if ($action === 'delete' && isset($_POST['id'])) {
        deleteMember($_POST['id']);
        redirect(SITE_URL . '/admin/members?success=member_deleted');
    }
}

include CUSTOM_PATH . '/templates/admin/members.php';