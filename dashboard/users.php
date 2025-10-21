<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$createSuccess = $createError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $fields = [
        'first_name', 'last_name', 'email', 'discord_id', 'slack_id', 'github_username',
        'school', 'hcb_member', 'birthdate', 'class', 'phone', 'role', 'description'
    ];
    $data = [];
    foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '');
    $data['active_member'] = isset($_POST['active_member']) ? 1 : 0;
    $data['hcb_member'] = isset($_POST['hcb_member']) ? 1 : 0;

    $exists = $db->prepare("SELECT 1 FROM users WHERE email=?");
    $exists->execute([$data['email']]);
    if ($exists->fetch()) {
        $createError = "A user with this email already exists.";
    } else {
        $stmt = $db->prepare("INSERT INTO users
            (first_name, last_name, email, discord_id, slack_id, github_username, school, hcb_member, birthdate, class, phone, role, description, active_member)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['first_name'], $data['last_name'], $data['email'],
            $data['discord_id'], $data['slack_id'], $data['github_username'], $data['school'],
            $data['hcb_member'], $data['birthdate'], $data['class'],
            $data['phone'], $data['role'], $data['description'], $data['active_member']
        ]);
        header("Location: users.php?created=1");
        exit();
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->prepare("DELETE FROM users WHERE id=?")->execute([$_GET['delete']]);
    header("Location: users.php?deleted=1");
    exit();
}

$editSuccess = $editError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id = intval($_POST['user_id']);
    $fields = [
        'first_name', 'last_name', 'email', 'discord_id', 'slack_id', 'github_username',
        'school', 'hcb_member', 'birthdate', 'class', 'phone', 'role', 'description'
    ];
    $data = [];
    foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '');
    $data['active_member'] = isset($_POST['active_member']) ? 1 : 0;
    $data['hcb_member'] = isset($_POST['hcb_member']) ? 1 : 0;

    $stmt = $db->prepare("UPDATE users SET
        first_name=?, last_name=?, email=?, discord_id=?, slack_id=?, github_username=?, school=?, hcb_member=?, birthdate=?, class=?, phone=?, role=?, description=?, active_member=?
        WHERE id=?");
    $params = array_values($data);
    $params[] = $id;
    $stmt->execute($params);
    $editSuccess = "User updated successfully!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_active'])) {
    $id = intval($_POST['user_id']);
    $active = isset($_POST['active_member']) ? 1 : 0;
    $db->prepare("UPDATE users SET active_member=? WHERE id=?")->execute([$active, $id]);
    exit();
}


$users = $db->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();

$pageTitle = "Manage Users";
include __DIR__ . '/components/dashboard-header.php';
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Users Management</h2>
                <p class="text-gray-600 mt-1">Manage all registered users and their Discord connections</p>
            </div>
            <div class="text-sm text-gray-500">
                Total Users: <?= count($users) ?> | Discord Linked: <?= $db->query("SELECT COUNT(DISTINCT user_id) FROM discord_links")->fetchColumn() ?>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <?php if ($createSuccess || isset($_GET['created'])): ?>
        <div class="bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700">
                        <?= $createSuccess ?? 'User created successfully!' ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($createError): ?>
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?= htmlspecialchars($createError) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700">User deleted successfully!</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($editSuccess): ?>
        <div class="bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700"><?= htmlspecialchars($editSuccess) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($resetSuccess): ?>
        <div class="bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700"><?= htmlspecialchars($resetSuccess) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($resetError): ?>
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?= htmlspecialchars($resetError) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['add'])): ?>
        <!-- Add User Form -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Add New User</h3>
                <a href="<?= $settings['site_url'] ?>/dashboard/users.php" 
                   class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    ← Back to Users
                </a>
            </div>
            <div class="p-6">
                <form method="post" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                            <input type="text" name="first_name" id="first_name" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input type="text" name="last_name" id="last_name" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <div class="mt-1 relative">
                                <input type="password" name="password" id="password" required minlength="8"
                                       class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                                <button type="button" 
                                        id="toggleCreatePassword" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <svg id="createEyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <svg id="createEyeSlashIcon" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464a10.025 10.025 0 00-5.21 2.506m5.624.872l4.242 4.242M9.878 9.878l4.242 4.242m-4.242-4.242L8.464 8.464m7.07 7.07l-7.07-7.07m7.07 7.07l1.414 1.414a10.025 10.025 0 005.21-2.506m-5.624-.872L9.878 9.878"></path>
                                    </svg>
                                </button>
                            </div>
                            <!-- Password Strength Indicator -->
                            <div id="createPasswordStrength" class="mt-1 h-1 bg-gray-200 rounded-full overflow-hidden">
                                <div id="createPasswordStrengthBar" class="h-full bg-red-500 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>
                        
                        <div id="createConfirmPasswordSection">
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <div class="mt-1 relative">
                                <input type="password" name="confirm_password" id="confirm_password" required minlength="8"
                                       class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                                <button type="button" 
                                        id="toggleCreateConfirm" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <svg id="createConfirmEyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <svg id="createConfirmEyeSlashIcon" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464a10.025 10.025 0 00-5.21 2.506m5.624.872l4.242 4.242M9.878 9.878l4.242 4.242m-4.242-4.242L8.464 8.464m7.07 7.07l-7.07-7.07m7.07 7.07l1.414 1.414a10.025 10.025 0 005.21-2.506m-5.624-.872L9.878 9.878"></path>
                                    </svg>
                                </button>
                            </div>
                            <!-- Confirm Password Strength Indicator -->
                            <div id="createConfirmStrength" class="mt-1 h-1 bg-gray-200 rounded-full overflow-hidden">
                                <div id="createConfirmStrengthBar" class="h-full bg-red-500 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <!-- Password Requirements -->
                            <div id="password-requirements-create" class="text-xs text-gray-500 mt-1">
                                <p class="mb-2">Password must contain:</p>
                                <ul class="space-y-1">
                                    <li class="requirement-item flex items-center" data-check="minLength">
                                        <span class="requirement-dot w-2 h-2 rounded-full mr-2 bg-gray-300"></span>
                                        <span>At least 8 characters</span>
                                    </li>
                                    <li class="requirement-item flex items-center" data-check="hasUppercase">
                                        <span class="requirement-dot w-2 h-2 rounded-full mr-2 bg-gray-300"></span>
                                        <span>At least 1 uppercase letter</span>
                                    </li>
                                    <li class="requirement-item flex items-center" data-check="hasLowercase">
                                        <span class="requirement-dot w-2 h-2 rounded-full mr-2 bg-gray-300"></span>
                                        <span>At least 1 lowercase letter</span>
                                    </li>
                                    <li class="requirement-item flex items-center" data-check="hasNumber">
                                        <span class="requirement-dot w-2 h-2 rounded-full mr-2 bg-gray-300"></span>
                                        <span>At least 1 number</span>
                                    </li>
                                    <li class="requirement-item flex items-center" data-check="hasSpecialChar">
                                        <span class="requirement-dot w-2 h-2 rounded-full mr-2 bg-gray-300"></span>
                                        <span>At least 1 special character</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div>
                            <label for="discord_id" class="block text-sm font-medium text-gray-700">Discord ID</label>
                            <input type="text" name="discord_id" id="discord_id"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="github_username" class="block text-sm font-medium text-gray-700">GitHub Username</label>
                            <input type="text" name="github_username" id="github_username"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="school" class="block text-sm font-medium text-gray-700">School</label>
                            <input type="text" name="school" id="school"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="birthdate" class="block text-sm font-medium text-gray-700">Birth Date</label>
                            <input type="date" name="birthdate" id="birthdate"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="class" class="block text-sm font-medium text-gray-700">Class</label>
                            <input type="text" name="class" id="class"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" name="phone" id="phone"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                            <select name="role" id="role"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                                <option value="Member">Member</option>
                                <option value="Co-leader">Co-leader</option>
                                <option value="Leader">Leader</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center">
                            <input type="checkbox" name="hcb_member" value="1" id="hcb_member"
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="hcb_member" class="ml-2 block text-sm text-gray-900">HCB Member</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="active_member" value="1" id="active_member" checked
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="active_member" class="ml-2 block text-sm text-gray-900">Active Member</label>
                        </div>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" name="create_user"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <?php elseif (isset($_GET['edit']) && is_numeric($_GET['edit'])):
        $editId = intval($_GET['edit']);
        $editUser = null;
        foreach ($users as $u) {
            if ($u['id'] == $editId) {
                $editUser = $u;
                break;
            }
        }
        if ($editUser): ?>
            <!-- Edit User Form -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Edit User</h3>
                    <a href="<?= $settings['site_url'] ?>/dashboard/users.php" 
                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        ← Back to Users
                    </a>
                </div>
                <div class="p-6">
                    <form method="post" class="space-y-6">
                        <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($editUser['first_name']) ?>" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($editUser['last_name']) ?>" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" id="email" value="<?= htmlspecialchars($editUser['email']) ?>" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                <p class="text-xs text-gray-500 mb-1">Leave blank to keep current password</p>
                                <div class="mt-1 relative">
                                    <input type="password" name="password" id="edit-password" minlength="8"
                                           class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                                    <button type="button" 
                                            id="toggleEditPassword" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                        <svg id="editEyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <svg id="editEyeSlashIcon" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464a10.025 10.025 0 00-5.21 2.506m5.624.872l4.242 4.242M9.878 9.878l4.242 4.242m-4.242-4.242L8.464 8.464m7.07 7.07l-7.07-7.07m7.07 7.07l1.414 1.414a10.025 10.025 0 005.21-2.506m-5.624-.872L9.878 9.878"></path>
                                    </svg>
                                </button>
                            </div>
                            <!-- Password Strength Indicator -->
                            <div id="editPasswordStrength" class="mt-1 h-1 bg-gray-200 rounded-full overflow-hidden">
                                <div id="editPasswordStrengthBar" class="h-full bg-red-500 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                            
                            <div id="editConfirmPasswordSection">
                                <label for="edit_confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <div class="mt-1 relative">
                                    <input type="password" name="confirm_password" id="edit_confirm_password" minlength="8"
                                           class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                                    <button type="button" 
                                            id="toggleEditConfirm" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                        <svg id="editConfirmEyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <svg id="editConfirmEyeSlashIcon" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464a10.025 10.025 0 00-5.21 2.506m5.624.872l4.242 4.242M9.878 9.878l4.242 4.242m-4.242-4.242L8.464 8.464m7.07 7.07l-7.07-7.07m7.07 7.07l1.414 1.414a10.025 10.025 0 005.21-2.506m-5.624-.872L9.878 9.878"></path>
                                    </svg>
                                </button>
                            </div>
                            <!-- Confirm Password Strength Indicator -->
                            <div id="editConfirmStrength" class="mt-1 h-1 bg-gray-200 rounded-full overflow-hidden">
                                <div id="editConfirmStrengthBar" class="h-full bg-red-500 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <!-- Password Requirements -->
                            <div id="password-requirements-edit" class="text-xs text-gray-500 mt-1">
                                <p class="mb-2">Password must contain:</p>
                                <ul class="space-y-1">
                                    <li class="requirement-item flex items-center" data-check="minLength">
                                        <span class="requirement-dot w-2 h-2 rounded-full mr-2 bg-gray-300"></span>
                                        <span>At least 8 characters</span>
                                    </li>
                                    <li class="requirement-item flex items-center" data-check="hasUppercase">
                                        <span class="requirement-dot w-2 h-2 rounded-full mr-2 bg-gray-300"></span>
                                        <span>At least 1 uppercase letter</span>
                                    </li>
                                    <li class="requirement-item flex items-center" data-check="hasLowercase">
                                        <span class="requirement-dot w-2 h-2 rounded-full mr-2 bg-gray-300"></span>
                                        <span>At least 1 lowercase letter</span>
                                    </li>
                                    <li class="requirement-item flex items-center" data-check="hasNumber">
                                        <span class="requirement-dot w-2 h-2 rounded-full mr-2 bg-gray-300"></span>
                                        <span>At least 1 number</span>
                                    </li>
                                    <li class="requirement-item flex items-center" data-check="hasSpecialChar">
                                        <span class="requirement-dot w-2 h-2 rounded-full mr-2 bg-gray-300"></span>
                                        <span>At least 1 special character</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div>
                            <label for="discord_id" class="block text-sm font-medium text-gray-700">Discord ID</label>
                            <input type="text" name="discord_id" id="discord_id" value="<?= htmlspecialchars($editUser['discord_id'] ?? '') ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="github_username" class="block text-sm font-medium text-gray-700">GitHub Username</label>
                            <input type="text" name="github_username" id="github_username" value="<?= htmlspecialchars($editUser['github_username'] ?? '') ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="school" class="block text-sm font-medium text-gray-700">School</label>
                            <input type="text" name="school" id="school" value="<?= htmlspecialchars($editUser['school'] ?? '') ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="birthdate" class="block text-sm font-medium text-gray-700">Birth Date</label>
                            <input type="date" name="birthdate" id="birthdate" value="<?= htmlspecialchars($editUser['birthdate'] ?? '') ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="class" class="block text-sm font-medium text-gray-700">Class</label>
                            <input type="text" name="class" id="class" value="<?= htmlspecialchars($editUser['class'] ?? '') ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($editUser['phone'] ?? '') ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                            <select name="role" id="role"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                                <option value="Member" <?= $editUser['role'] == 'Member' ? 'selected' : '' ?>>Member</option>
                                <option value="Co-leader" <?= $editUser['role'] == 'Co-leader' ? 'selected' : '' ?>>Co-leader</option>
                                <option value="Leader" <?= $editUser['role'] == 'Leader' ? 'selected' : '' ?>>Leader</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center">
                            <input type="checkbox" name="hcb_member" value="1" id="hcb_member" <?= $editUser['hcb_member'] ? 'checked' : '' ?>
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="hcb_member" class="ml-2 block text-sm text-gray-900">HCB Member</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="active_member" value="1" id="active_member" <?= $editUser['active_member'] ? 'checked' : '' ?>
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="active_member" class="ml-2 block text-sm text-gray-900">Active Member</label>
                        </div>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"><?= htmlspecialchars($editUser['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" name="edit_user"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">User not found.</p>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <a href="<?= $settings['site_url'] ?>/dashboard/users.php" 
                   class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    ← Back to Users
                </a>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">All Users</h3>
                <a href="<?= $settings['site_url'] ?>/dashboard/users.php?add=1" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Add User
                </a>
            </div>
            
            <?php if (empty($users)): ?>
                <div class="p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No users</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new user.</p>
                    <div class="mt-6">
                        <a href="<?= $settings['site_url'] ?>/dashboard/users.php?add=1" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Add User
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discord Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($users as $u): 
                                // Check Discord link status
                                $stmt = $db->prepare("SELECT discord_username FROM discord_links WHERE user_id = ?");
                                $stmt->execute([$u['id']]);
                                $discordLink = $stmt->fetch();
                            ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700">
                                                        <?= strtoupper(substr($u['first_name'], 0, 1) . substr($u['last_name'], 0, 1)) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>
                                                </div>
                                                <?php if ($discordLink): ?>
                                                    <div class="text-sm text-gray-500">
                                                        Discord: <?= htmlspecialchars($discordLink['discord_username']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <a href="mailto:<?= htmlspecialchars($u['email']) ?>" class="text-primary hover:text-red-600 break-all">
                                            <?= htmlspecialchars($u['email']) ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $roleColor = '';
                                        switch($u['role']) {
                                            case 'Leader': $roleColor = 'bg-purple-100 text-purple-800'; break;
                                            case 'Co-leader': $roleColor = 'bg-blue-100 text-blue-800'; break;
                                            default: $roleColor = 'bg-gray-100 text-gray-800'; break;
                                        }
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $roleColor ?>">
                                            <?= htmlspecialchars($u['role'] ?: 'Member') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($discordLink): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Linked
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                                Not Linked
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="<?= $settings['site_url'] ?>/dashboard/edit-user.php?id=<?= $u['id'] ?>" 
                                           class="text-primary hover:text-red-600">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script src="<?= $settings['site_url'] ?>/js/password-validation.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Setup validation for create user form
    setupPasswordValidation({
        passwordFieldId: 'password',
        confirmFieldId: 'confirm_password',
        confirmSectionId: 'createConfirmPasswordSection',
        requirementsSelector: '#password-requirements-create',
        strengthBarId: 'createPasswordStrengthBar',
        confirmStrengthBarId: 'createConfirmStrengthBar',
        togglePasswordId: 'toggleCreatePassword',
        toggleConfirmId: 'toggleCreateConfirm',
        eyeIconId: 'createEyeIcon',
        eyeSlashIconId: 'createEyeSlashIcon',
        confirmEyeIconId: 'createConfirmEyeIcon',
        confirmEyeSlashIconId: 'createConfirmEyeSlashIcon'
    });
    
    // Setup validation for edit user form
    setupPasswordValidation({
        passwordFieldId: 'edit-password',
        confirmFieldId: 'edit_confirm_password',
        confirmSectionId: 'editConfirmPasswordSection',
        requirementsSelector: '#password-requirements-edit',
        strengthBarId: 'editPasswordStrengthBar',
        confirmStrengthBarId: 'editConfirmStrengthBar',
        togglePasswordId: 'toggleEditPassword',
        toggleConfirmId: 'toggleEditConfirm',
        eyeIconId: 'editEyeIcon',
        eyeSlashIconId: 'editEyeSlashIcon',
        confirmEyeIconId: 'editConfirmEyeIcon',
        confirmEyeSlashIconId: 'editConfirmEyeSlashIcon'
    });
});

function toggleActive(checkbox, userId) {
    var formData = new FormData();
    formData.append('toggle_active', 1);
    formData.append('user_id', userId);
    if (checkbox.checked) formData.append('active_member', 1);
    fetch(window.location.href, { method: 'POST', body: formData });
}
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>