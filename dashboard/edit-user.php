<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$userId = intval($_GET['id']);

// Handle form submission
$editSuccess = $editError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $fields = [
        'first_name', 'last_name', 'email',
        'school', 'class', 'phone', 'role', 'description'
    ];
    $data = [];
    foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '');
    
    // Use birthdate_iso if available, otherwise fall back to birthdate
    $data['birthdate'] = trim($_POST['birthdate_iso'] ?? $_POST['birthdate'] ?? '');
    $data['active_member'] = isset($_POST['active_member']) ? 1 : 0;

    // Check if email already exists for another user
    $exists = $db->prepare("SELECT id FROM users WHERE email=? AND id != ?");
    $exists->execute([$data['email'], $userId]);
    if ($exists->fetch()) {
        $editError = "A user with this email already exists.";
    } else {
        $stmt = $db->prepare("UPDATE users SET
            first_name=?, last_name=?, email=?, 
            school=?, birthdate=?, class=?, phone=?, role=?, description=?, active_member=?
            WHERE id=?");
        $params = array_values($data);
        $params[] = $userId;
        $stmt->execute($params);
        $editSuccess = "User updated successfully!";
        
        // Refresh user data
        $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$userId]);
        $editUser = $stmt->fetch();
    }
}

// Fetch user data
if (!isset($editUser)) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$userId]);
    $editUser = $stmt->fetch();
}

if (!$editUser) {
    $notFound = true;
}

// Fetch Discord link if exists
$discordLink = null;
if ($editUser) {
    $stmt = $db->prepare("SELECT * FROM discord_links WHERE user_id=?");
    $stmt->execute([$userId]);
    $discordLink = $stmt->fetch();
}

$pageTitle = "Edit User";
include __DIR__ . '/components/dashboard-header.php';
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Edit User</h2>
                <p class="text-gray-600 mt-1">
                    <?php if ($editUser): ?>
                        Update information for <?= htmlspecialchars($editUser['first_name'] . ' ' . $editUser['last_name']) ?>
                    <?php else: ?>
                        User not found
                    <?php endif; ?>
                </p>
            </div>
            <a href="<?= $settings['site_url'] ?>/dashboard/users.php" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Users
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($editSuccess): ?>
        <div class="bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700"><?= htmlspecialchars($editSuccess) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($editError): ?>
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?= htmlspecialchars($editError) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($notFound) && $notFound): ?>
        <!-- User Not Found -->
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700">User not found. The user may have been deleted.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Discord Status Card -->
        <?php if ($discordLink): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.317 4.37a19.791 19.791 0 00-4.885-1.515.074.074 0 00-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 00-5.487 0 12.64 12.64 0 00-.617-1.25.077.077 0 00-.079-.037A19.736 19.736 0 003.677 4.37a.07.07 0 00-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 00.031.057 19.9 19.9 0 005.993 3.03.078.078 0 00.084-.028 14.09 14.09 0 001.226-1.994.076.076 0 00-.041-.106 13.107 13.107 0 01-1.872-.892.077.077 0 01-.008-.128 10.2 10.2 0 00.372-.292.074.074 0 01.077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 01.078.01c.12.098.246.198.373.292a.077.077 0 01-.006.127 12.299 12.299 0 01-1.873.892.077.077 0 00-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 00.084.028 19.839 19.839 0 006.002-3.03.077.077 0 00.032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 00-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                    </svg>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-800">Discord Account Linked</p>
                        <p class="text-sm text-green-600">
                            Username: <?= htmlspecialchars($discordLink['discord_username']) ?> 
                            (ID: <?= htmlspecialchars($discordLink['discord_id']) ?>)
                        </p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-yellow-800">No Discord Account Linked</p>
                        <p class="text-sm text-yellow-600">User has not connected their Discord account yet.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Edit User Form -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">User Information</h3>
            </div>
            <div class="p-6">
                <form method="post" class="space-y-6">
                    <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
                    
                    <!-- Personal Information -->
                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-4">Personal Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                                <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($editUser['first_name']) ?>" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                                <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($editUser['last_name']) ?>" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                                <input type="email" name="email" id="email" value="<?= htmlspecialchars($editUser['email']) ?>" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <div class="relative">
                                    <button id="dropdown-phone-button" type="button" class="flex-shrink-0 absolute z-10 inline-flex items-center py-2.5 px-4 text-sm font-medium text-center text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-50 focus:ring-2 focus:outline-none focus:ring-blue-500">
                                        <span id="selected-flag" class="text-lg mr-1">🇺🇸</span>
                                        <span id="selected-country-code" class="text-xs font-medium">+1</span>
                                    </button>
                                    <div id="dropdown-phone" class="absolute top-full left-0 z-20 mt-2 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-lg w-72 max-h-60 overflow-y-auto border border-gray-200">
                                        <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdown-phone-button">
                                            <li>
                                                <button type="button" class="inline-flex w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50" data-country-code="+1" data-flag="🇺🇸">
                                                    <span class="inline-flex items-center w-full">
                                                        <span class="text-xl mr-3">🇺🇸</span>
                                                        <span class="flex-1 text-left font-medium">United States</span>
                                                        <span class="text-gray-500 text-xs">+1</span>
                                                    </span>
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="inline-flex w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50" data-country-code="+44" data-flag="🇬🇧">
                                                    <span class="inline-flex items-center w-full">
                                                        <span class="text-xl mr-3">🇬🇧</span>
                                                        <span class="flex-1 text-left font-medium">United Kingdom</span>
                                                        <span class="text-gray-500 text-xs">+44</span>
                                                    </span>
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="inline-flex w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50" data-country-code="+40" data-flag="🇷🇴">
                                                    <span class="inline-flex items-center w-full">
                                                        <span class="text-xl mr-3">🇷🇴</span>
                                                        <span class="flex-1 text-left font-medium">Romania</span>
                                                        <span class="text-gray-500 text-xs">+40</span>
                                                    </span>
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="inline-flex w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50" data-country-code="+49" data-flag="🇩🇪">
                                                    <span class="inline-flex items-center w-full">
                                                        <span class="text-xl mr-3">🇩🇪</span>
                                                        <span class="flex-1 text-left font-medium">Germany</span>
                                                        <span class="text-gray-500 text-xs">+49</span>
                                                    </span>
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="inline-flex w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50" data-country-code="+33" data-flag="🇫🇷">
                                                    <span class="inline-flex items-center w-full">
                                                        <span class="text-xl mr-3">🇫🇷</span>
                                                        <span class="flex-1 text-left font-medium">France</span>
                                                        <span class="text-gray-500 text-xs">+33</span>
                                                    </span>
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="inline-flex w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50" data-country-code="+39" data-flag="🇮🇹">
                                                    <span class="inline-flex items-center w-full">
                                                        <span class="text-xl mr-3">🇮🇹</span>
                                                        <span class="flex-1 text-left font-medium">Italy</span>
                                                        <span class="text-gray-500 text-xs">+39</span>
                                                    </span>
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="inline-flex w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50" data-country-code="+34" data-flag="🇪🇸">
                                                    <span class="inline-flex items-center w-full">
                                                        <span class="text-xl mr-3">🇪🇸</span>
                                                        <span class="flex-1 text-left font-medium">Spain</span>
                                                        <span class="text-gray-500 text-xs">+34</span>
                                                    </span>
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="inline-flex w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50" data-country-code="+61" data-flag="🇦🇺">
                                                    <span class="inline-flex items-center w-full">
                                                        <span class="text-xl mr-3">🇦🇺</span>
                                                        <span class="flex-1 text-left font-medium">Australia</span>
                                                        <span class="text-gray-500 text-xs">+61</span>
                                                    </span>
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="inline-flex w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50" data-country-code="+91" data-flag="🇮🇳">
                                                    <span class="inline-flex items-center w-full">
                                                        <span class="text-xl mr-3">🇮🇳</span>
                                                        <span class="flex-1 text-left font-medium">India</span>
                                                        <span class="text-gray-500 text-xs">+91</span>
                                                    </span>
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    <input type="hidden" name="country_code" id="country_code" value="+1">
                                    <input type="tel" name="phone" id="phone-input" value="<?= htmlspecialchars($editUser['phone'] ?? '') ?>" placeholder="123-456-7890" class="block p-2.5 w-full pl-20 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                                </div>
                            </div>
                            <div>
                                <label for="birthdate" class="block text-sm font-medium text-gray-700 mb-2">Birth Date</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <input type="text" name="birthdate" id="birthdate" value="<?= htmlspecialchars($editUser['birthdate'] ?? '') ?>" placeholder="Select date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" readonly>
                                    <input type="hidden" name="birthdate_iso" id="birthdate_iso" value="<?= htmlspecialchars($editUser['birthdate'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- School Information -->
                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-4">School Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="school" class="block text-sm font-medium text-gray-700">School</label>
                                <input type="text" name="school" id="school" value="<?= htmlspecialchars($editUser['school'] ?? '') ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label for="class" class="block text-sm font-medium text-gray-700">Class</label>
                                <input type="text" name="class" id="class" value="<?= htmlspecialchars($editUser['class'] ?? '') ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                            </div>
                        </div>
                    </div>

                    <!-- Role & Status -->
                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-4">Role & Status</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                        
                        <div class="mt-4 flex items-center">
                            <input type="checkbox" name="active_member" value="1" id="active_member" <?= $editUser['active_member'] ? 'checked' : '' ?>
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="active_member" class="ml-2 block text-sm text-gray-900">Active Member</label>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="4"
                                  class="mt-1 block w-full border-2 border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary focus:border-2"
                                  placeholder="Additional information about the user..."><?= htmlspecialchars($editUser['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <a href="<?= $settings['site_url'] ?>/dashboard/users.php" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancel
                        </a>
                        <button type="submit" name="edit_user"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Status Toggle -->
        <div class="bg-white rounded-lg shadow border-2 border-yellow-200">
            <div class="px-6 py-4 bg-yellow-50 border-b border-yellow-200">
                <h3 class="text-lg font-medium text-yellow-900">Account Status</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">
                            <?= $editUser['active_member'] ? 'Disable Account' : 'Enable Account' ?>
                        </h4>
                        <p class="text-sm text-gray-600 mt-1">
                            <?php if ($editUser['active_member']): ?>
                                Disabling this account will prevent the user from accessing most features. They will have limited access to view their profile and projects.
                            <?php else: ?>
                                Enable this account to restore full access to all features and functionality.
                            <?php endif; ?>
                        </p>
                    </div>
                    <button type="button" onclick="confirmToggleStatus(<?= $editUser['id'] ?>, <?= $editUser['active_member'] ? 'false' : 'true' ?>)"
                            class="inline-flex items-center px-4 py-2 border <?= $editUser['active_member'] ? 'border-red-300 text-red-700 hover:bg-red-50' : 'border-green-300 text-green-700 hover:bg-green-50' ?> shadow-sm text-sm font-medium rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-offset-2 <?= $editUser['active_member'] ? 'focus:ring-red-500' : 'focus:ring-green-500' ?>">
                        <?php if ($editUser['active_member']): ?>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                            Disable Account
                        <?php else: ?>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Enable Account
                        <?php endif; ?>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmToggleStatus(userId, willEnable) {
    const action = willEnable ? 'enable' : 'disable';
    const message = willEnable 
        ? 'Are you sure you want to enable this user account? The user will regain full access.'
        : 'Are you sure you want to disable this user account? The user will have limited access.';
    
    if (confirm(message)) {
        window.location.href = '<?= $settings['site_url'] ?>/dashboard/users.php?toggle_status=' + userId;
    }
}

// Initialize components
document.addEventListener('DOMContentLoaded', function() {
    // Countries using DD.MM.YYYY format
    const ddmmyyyyCountries = ['+40', '+49', '+33', '+39', '+34', '+48', '+31', '+32', '+43', '+41', '+351', '+30', '+45', '+46', '+47', '+358'];
    
    // Get user's date format preference based on country code
    function getDateFormat() {
        const countryCode = document.getElementById('country_code')?.value || '+1';
        return ddmmyyyyCountries.includes(countryCode) ? 'DD.MM.YYYY' : 'YYYY-MM-DD';
    }
    
    // Format date according to locale
    function formatDateForDisplay(isoDate) {
        if (!isoDate) return '';
        const parts = isoDate.split('-');
        if (parts.length !== 3) return isoDate;
        
        const format = getDateFormat();
        if (format === 'DD.MM.YYYY') {
            return `${parts[2]}.${parts[1]}.${parts[0]}`;
        }
        return isoDate;
    }
    
    // Parse display date to ISO format
    function parseDateToISO(displayDate) {
        if (!displayDate) return '';
        
        const format = getDateFormat();
        if (format === 'DD.MM.YYYY') {
            const parts = displayDate.split('.');
            if (parts.length === 3) {
                return `${parts[2]}-${parts[1]}-${parts[0]}`;
            }
        }
        return displayDate;
    }
    
    // Phone Country Code Dropdown
    const dropdownButton = document.getElementById('dropdown-phone-button');
    const dropdownMenu = document.getElementById('dropdown-phone');
    const selectedCodeSpan = document.getElementById('selected-country-code');
    const hiddenCountryCodeInput = document.getElementById('country_code');
    const birthdateInput = document.getElementById('birthdate');
    const birthdateIsoInput = document.getElementById('birthdate_iso');
    
    // Initialize date display format
    if (birthdateIsoInput && birthdateInput && birthdateIsoInput.value) {
        birthdateInput.value = formatDateForDisplay(birthdateIsoInput.value);
    }
    
    if (dropdownButton && dropdownMenu) {
        // Toggle dropdown
        dropdownButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
        });
        
        // Handle country selection
        const countryButtons = dropdownMenu.querySelectorAll('button[data-country-code]');
        countryButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const countryCode = this.getAttribute('data-country-code');
                const flag = this.getAttribute('data-flag');
                selectedCodeSpan.textContent = countryCode;
                hiddenCountryCodeInput.value = countryCode;
                dropdownButton.querySelector('#selected-flag').textContent = flag;
                dropdownMenu.classList.add('hidden');
                
                // Update date format when country changes
                if (birthdateIsoInput && birthdateInput && birthdateIsoInput.value) {
                    birthdateInput.value = formatDateForDisplay(birthdateIsoInput.value);
                    const format = getDateFormat();
                    birthdateInput.placeholder = format === 'DD.MM.YYYY' ? 'DD.MM.YYYY' : 'YYYY-MM-DD';
                }
            });
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });
    }
    
    // Custom Date Picker
    if (birthdateInput) {
        let currentYear = new Date().getFullYear();
        let currentMonth = new Date().getMonth();
        let picker = null;
        
        // Parse existing date if present (from ISO format)
        if (birthdateIsoInput.value) {
            const parts = birthdateIsoInput.value.split('-');
            if (parts.length === 3) {
                currentYear = parseInt(parts[0]);
                currentMonth = parseInt(parts[1]) - 1;
            }
        }
        
        // Create picker element
        function createPicker() {
            if (picker) return;
            
            picker = document.createElement('div');
            picker.id = 'customDatePicker';
            picker.className = 'absolute z-50 mt-2 bg-white border border-gray-200 rounded-lg shadow-xl p-4 hidden';
            picker.style.width = '340px';
            birthdateInput.parentNode.appendChild(picker);
        }
        
        function renderCalendar() {
            if (!picker) createPicker();
            
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                              'July', 'August', 'September', 'October', 'November', 'December'];
            
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            
            // Create year options
            let yearOptions = '';
            for (let y = 1950; y <= new Date().getFullYear() + 10; y++) {
                yearOptions += `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y}</option>`;
            }
            
            picker.innerHTML = `
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200">
                    <button type="button" class="text-gray-600 hover:bg-gray-100 hover:text-gray-900 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="prevMonth">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div class="flex items-center gap-2">
                        <select id="monthSelect" class="text-sm font-semibold text-gray-900 bg-white border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none cursor-pointer">
                            ${monthNames.map((name, idx) => `<option value="${idx}" ${idx === currentMonth ? 'selected' : ''}>${name}</option>`).join('')}
                        </select>
                        <select id="yearSelect" class="text-sm font-semibold text-gray-900 bg-white border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none cursor-pointer">
                            ${yearOptions}
                        </select>
                    </div>
                    <button type="button" class="text-gray-600 hover:bg-gray-100 hover:text-gray-900 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="nextMonth">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-7 mb-2">
                    <span class="flex items-center justify-center h-8 text-xs font-semibold text-gray-500">Su</span>
                    <span class="flex items-center justify-center h-8 text-xs font-semibold text-gray-500">Mo</span>
                    <span class="flex items-center justify-center h-8 text-xs font-semibold text-gray-500">Tu</span>
                    <span class="flex items-center justify-center h-8 text-xs font-semibold text-gray-500">We</span>
                    <span class="flex items-center justify-center h-8 text-xs font-semibold text-gray-500">Th</span>
                    <span class="flex items-center justify-center h-8 text-xs font-semibold text-gray-500">Fr</span>
                    <span class="flex items-center justify-center h-8 text-xs font-semibold text-gray-500">Sa</span>
                </div>
                <div class="grid grid-cols-7 gap-1" id="calendarDays"></div>
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <button type="button" id="todayBtn" class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                        Today
                    </button>
                </div>
            `;
            
            const calendarDays = picker.querySelector('#calendarDays');
            
            // Empty cells before first day
            for (let i = 0; i < firstDay; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.className = 'h-9';
                calendarDays.appendChild(emptyCell);
            }
            
            // Days of month
            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const isSelected = birthdateIsoInput.value === dateStr;
                const isToday = dateStr === new Date().toISOString().split('T')[0];
                
                const dayButton = document.createElement('button');
                dayButton.type = 'button';
                dayButton.className = `h-9 text-sm rounded-lg font-medium transition-colors ${
                    isSelected 
                        ? 'bg-blue-600 text-white hover:bg-blue-700' 
                        : isToday
                            ? 'bg-blue-100 text-blue-600 hover:bg-blue-200'
                            : 'text-gray-700 hover:bg-gray-100'
                }`;
                dayButton.textContent = day;
                dayButton.addEventListener('click', function() {
                    birthdateIsoInput.value = dateStr;
                    birthdateInput.value = formatDateForDisplay(dateStr);
                    picker.classList.add('hidden');
                });
                
                calendarDays.appendChild(dayButton);
            }
            
            // Attach month navigation handlers
            picker.querySelector('#prevMonth').addEventListener('click', function() {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                renderCalendar();
            });
            
            picker.querySelector('#nextMonth').addEventListener('click', function() {
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                renderCalendar();
            });
            
            // Month and year select handlers
            picker.querySelector('#monthSelect').addEventListener('change', function() {
                currentMonth = parseInt(this.value);
                renderCalendar();
            });
            
            picker.querySelector('#yearSelect').addEventListener('change', function() {
                currentYear = parseInt(this.value);
                renderCalendar();
            });
            
            // Today button handler
            picker.querySelector('#todayBtn').addEventListener('click', function() {
                const today = new Date();
                currentYear = today.getFullYear();
                currentMonth = today.getMonth();
                const todayStr = today.toISOString().split('T')[0];
                birthdateIsoInput.value = todayStr;
                birthdateInput.value = formatDateForDisplay(todayStr);
                picker.classList.add('hidden');
            });
        }
        
        // Show/hide picker
        birthdateInput.addEventListener('click', function(e) {
            e.stopPropagation();
            if (!picker) {
                createPicker();
                renderCalendar();
            }
            picker.classList.toggle('hidden');
        });
        
        // Close picker when clicking outside
        document.addEventListener('click', function(e) {
            if (picker && !picker.contains(e.target) && e.target !== birthdateInput) {
                picker.classList.add('hidden');
            }
        });
    }
});
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
